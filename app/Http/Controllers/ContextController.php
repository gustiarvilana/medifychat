<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContextController extends Controller
{
    private const ALLOWED_TYPES = ['docx', 'pdf', 'txt', 'xlsx', 'json'];
    private const MAX_FILE_SIZE = 50 * 1024 * 1024;

    public function index(): JsonResponse
    {
        $contexts = DB::table('bot_context')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'title' => $c->title,
                'type' => $c->type,
                'category' => $c->category,
                'tags' => $c->tags,
                'status' => $c->status,
                'progress' => (int) $c->progress,
                'active' => (bool) $c->active,
                'error_message' => $c->error_message,
                'file_size' => $c->file_size,
                'created_at' => $c->created_at,
                'updated_at' => $c->updated_at,
            ]);

        return response()->json($contexts);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('bot_context')->find($id);

        if (!$row) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json([
            'id' => $row->id,
            'title' => $row->title,
            'type' => $row->type,
            'category' => $row->category,
            'tags' => $row->tags,
            'status' => $row->status,
            'progress' => (int) $row->progress,
            'active' => (bool) $row->active,
            'error_message' => $row->error_message,
            'content' => $row->status === 'completed' ? $row->content : null,
            'file_size' => $row->file_size,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, self::ALLOWED_TYPES, true)) {
            return response()->json([
                'error' => "Tipe file .{$ext} tidak didukung. Gunakan: " . implode(', ', self::ALLOWED_TYPES),
            ], 422);
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return response()->json(['error' => 'File maksimal 50MB.'], 422);
        }

        $id = DB::table('bot_context')->insertGetId([
            'title' => $file->getClientOriginalName(),
            'type' => $ext,
            'file_path' => null,
            'file_size' => $file->getSize(),
            'content' => null,
            'category' => $request->input('category'),
            'tags' => $request->input('tags'),
            'status' => 'pending',
            'progress' => 0,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (!$id) {
            \Log::error('Failed to insert bot_context record.');
            return response()->json(['error' => 'Gagal membuat record konteks.'], 500);
        }

        $dest = "context/{$id}";
        $fileName = $file->getClientOriginalName();
        
        \Log::info("Attempting to move file. Destination Dir: '{$dest}', File Name: '{$fileName}'");
        
        // Ensure directory exists
        $storagePath = storage_path('app/' . $dest);
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
        
        $targetPath = $storagePath . DIRECTORY_SEPARATOR . $fileName;
        
        // Attempt to move the file
        try {
            if ($file->move($storagePath, $fileName)) {
                $filePath = $dest . '/' . $fileName;
            } else {
                throw new \Exception("Move failed.");
            }
        } catch (\Exception $e) {
            \Log::error("Failed to move file for context ID: {$id}. Error: " . $e->getMessage());
            return response()->json(['error' => 'Gagal menyimpan file.'], 500);
        }
        
        \Log::info("File moved successfully. Path: '{$filePath}'");

        DB::table('bot_context')->where('id', $id)->update([
            'file_path' => $filePath,
            'updated_at' => now(),
        ]);

        $this->dispatchProcess($id);

        return response()->json([
            'message' => 'File uploaded. Processing started.',
            'id' => $id,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('bot_context')->find($id);
        if (!$row) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $data = [];
        if ($request->has('category')) {
            $data['category'] = $request->input('category');
        }
        if ($request->has('tags')) {
            $data['tags'] = $request->input('tags');
        }
        $data['updated_at'] = now();

        DB::table('bot_context')->where('id', $id)->update($data);

        return response()->json(['message' => 'Updated']);
    }

    public function destroy(int $id): JsonResponse
    {
        $row = DB::table('bot_context')->find($id);
        if (!$row) {
            return response()->json(['error' => 'Not found'], 404);
        }

        if ($row->file_path) {
            $full = storage_path("app/{$row->file_path}");
            if (file_exists($full)) {
                unlink($full);
            }
            $dir = dirname($full);
            if (is_dir($dir)) {
                $files = glob("{$dir}/*");
                if ($files === false || count($files) === 0) {
                    @rmdir($dir);
                }
            }
        }

        DB::table('bot_context')->where('id', $id)->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function toggle(int $id): JsonResponse
    {
        $row = DB::table('bot_context')->find($id);
        if (!$row) {
            return response()->json(['error' => 'Not found'], 404);
        }

        DB::table('bot_context')->where('id', $id)->update([
            'active' => !(bool) $row->active,
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Toggled',
            'active' => !(bool) $row->active,
        ]);
    }

    public function retry(int $id): JsonResponse
    {
        $row = DB::table('bot_context')->find($id);
        if (!$row) {
            return response()->json(['error' => 'Not found'], 404);
        }

        if ($row->status === 'processing') {
            return response()->json(['error' => 'Already processing'], 409);
        }

        $filePath = storage_path("app/{$row->file_path}");
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File tidak ditemukan di storage. Upload ulang.'], 404);
        }

        DB::table('bot_context')->where('id', $id)->update([
            'status' => 'pending',
            'progress' => 0,
            'error_message' => null,
            'content' => null,
            'updated_at' => now(),
        ]);

        $this->dispatchProcess($id);

        return response()->json(['message' => 'Retrying...']);
    }

    private function dispatchProcess(int $id): void
    {
        $php = PHP_BINARY ?: 'php';
        $artisan = base_path('artisan');

        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen("start /B {$php} {$artisan} context:process {$id} > NUL 2>&1", 'r'));
        } else {
            exec("{$php} {$artisan} context:process {$id} > /dev/null 2>&1 &");
        }
    }
}
