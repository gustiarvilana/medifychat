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
            'admin_wa_number' => $status->admin_wa_number ?? '',
            'gemini_api_key' => $status->gemini_api_key ? '••••••••' : '',
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'admin_wa_number' => 'nullable|string|max:100',
            'gemini_api_key' => 'nullable|string|max:255',
        ]);

        $data = [];
        if ($request->has('admin_wa_number')) {
            $data['admin_wa_number'] = $validated['admin_wa_number'] ?? '';
        }
        if ($request->has('gemini_api_key')) {
            $data['gemini_api_key'] = $validated['gemini_api_key'] ?? '';
        }

        if (!empty($data)) {
            DB::table('bot_status')->where('id', 1)->update($data);
        }

        return response()->json(['message' => 'Settings saved']);
    }
}
