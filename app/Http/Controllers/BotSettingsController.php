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
            'rs_name' => $status->rs_name ?? '',
            'admin_wa_number' => $status->admin_wa_number ?? '',
            'gemini_api_key' => $status->gemini_api_key ? '••••••••' : '',
            'medify_api_url' => $status->medify_api_url ?? '',
            'medify_api_email' => $status->medify_api_email ?? '',
            'medify_api_password' => $status->medify_api_password ? '••••••••' : '',
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rs_name' => 'nullable|string|max:255',
            'admin_wa_number' => 'nullable|string|max:100',
            'gemini_api_key' => 'nullable|string|max:255',
            'medify_api_url' => 'nullable|string|max:255',
            'medify_api_email' => 'nullable|string|max:255',
            'medify_api_password' => 'nullable|string|max:255',
        ]);

        // Test Gemini API Key if provided
        if ($request->has('gemini_api_key') && !empty($validated['gemini_api_key'])) {
            $testResult = $this->testGeminiKey($validated['gemini_api_key']);
            if (!$testResult['success']) {
                return response()->json(['error' => 'API Key tidak valid: ' . $testResult['message']], 422);
            }
        }

        $data = [];
        if ($request->has('rs_name')) {
            $data['rs_name'] = $validated['rs_name'] ?? '';
        }
        if ($request->has('admin_wa_number')) {
            $data['admin_wa_number'] = $validated['admin_wa_number'] ?? '';
        }
        if ($request->has('gemini_api_key')) {
            // Only update if not the masked value
            if ($validated['gemini_api_key'] !== '••••••••') {
                $data['gemini_api_key'] = $validated['gemini_api_key'];
            }
        }
        if ($request->has('medify_api_url')) {
            $data['medify_api_url'] = $validated['medify_api_url'] ?? '';
        }
        if ($request->has('medify_api_email')) {
            $data['medify_api_email'] = $validated['medify_api_email'] ?? '';
        }
        if ($request->has('medify_api_password')) {
            // Only update if not the masked value
            if ($validated['medify_api_password'] !== '••••••••') {
                $data['medify_api_password'] = $validated['medify_api_password'];
            }
        }

        if (!empty($data)) {
            DB::table('bot_status')->where('id', 1)->update($data);

            // Notify admin when API key changes
            if (isset($data['gemini_api_key'])) {
                DB::table('bot_commands')->insert([
                    'command' => 'notify',
                    'params' => json_encode(['message' => '🔑 *API Key Gemini* telah diperbarui']),
                    'status' => 'pending',
                ]);
            }
        }

        return response()->json(['message' => 'Settings saved']);
    }

    private function testGeminiKey(string $apiKey): array
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'contents' => [['parts' => [['text' => 'ping']]]]
        ]));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return ['success' => true];
        }

        return ['success' => false, 'message' => 'HTTP ' . $httpCode];
    }
}
