<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BotSettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $status = DB::table('bot_status')->where('id', 1)->first();

        return response()->json([
            'rs_name' => $status->rs_name ?? 'Medify Hospital',
            'admin_wa_number' => $status->admin_wa_number ?? '',
            'is_ai_ready' => !empty($status->gemini_api_key),
            'medify_api_url' => $status->medify_api_url ?? '',
            'medify_api_email' => $status->medify_api_email ?? '',
            'medify_api_password' => $status->medify_api_password ? '••••••••' : '',
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $status = DB::table('bot_status')->where('id', 1)->first();

        $validated = $request->validate([
            'rs_name' => 'nullable|string|max:255',
            'admin_wa_number' => 'nullable|string|max:100',
            'gemini_api_key' => 'nullable|string|max:255',
            'medify_api_url' => 'nullable|string|max:255',
            'medify_api_email' => 'nullable|string|max:255',
            'medify_api_password' => 'nullable|string|max:255',
        ]);

        $data = [];
        $keyUpdated = false;

        if ($request->has('rs_name')) {
            $data['rs_name'] = $validated['rs_name'] ?? 'Medify Hospital';
        }
        if ($request->has('admin_wa_number')) {
            $data['admin_wa_number'] = $validated['admin_wa_number'] ?? '';
        }
        if ($request->has('gemini_api_key')) {
            $key = $validated['gemini_api_key'];
            $data['gemini_api_key'] = !empty(trim($key)) ? $key : null;
            $keyUpdated = true;
        }
        if ($request->has('medify_api_url')) {
            $data['medify_api_url'] = $validated['medify_api_url'] ?? '';
        }
        if ($request->has('medify_api_email')) {
            $data['medify_api_email'] = $validated['medify_api_email'] ?? '';
        }
        if ($request->has('medify_api_password')) {
            $data['medify_api_password'] = $validated['medify_api_password'] ?? '';
        }

        if (!empty($data)) {
            DB::table('bot_status')->updateOrInsert(['id' => 1], $data);
        }

        if ($keyUpdated && !empty($data['gemini_api_key'])) {
            try {
                $this->validateAndNotifyGeminiKey($data['gemini_api_key'], $data['admin_wa_number'] ?? ($status->admin_wa_number ?? ''));
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        }

        return response()->json(['message' => 'Settings saved']);
    }

    private function validateAndNotifyGeminiKey($key, $adminWa)
    {
        // Real validation check via API call
        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $key;
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'contents' => [['parts' => [['text' => 'ping']]]]
        ]));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $isValid = ($httpCode === 200);

        if (!$isValid) {
            throw new \Exception("Kunci API Gemini tidak valid.");
        }

        if ($adminWa) {
            $message = "✅ Kunci API Gemini baru berhasil diperbarui dan aktif.";
            if (strpos($adminWa, '@') === false) $adminWa .= '@lid';
            DB::table('bot_commands')->insert([
                'command' => 'send_message',
                'payload' => json_encode(['target' => $adminWa, 'message' => $message]),
                'status' => 'pending',
                'created_at' => now(),
            ]);
        }
    }
}

