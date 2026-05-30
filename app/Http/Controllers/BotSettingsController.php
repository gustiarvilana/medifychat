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
            'medify_api_url' => $status->medify_api_url ?? '',
            'medify_api_email' => $status->medify_api_email ?? '',
            'medify_api_password' => $status->medify_api_password ? '••••••••' : '',
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'admin_wa_number' => 'nullable|string|max:100',
            'gemini_api_key' => 'nullable|string|max:255',
            'medify_api_url' => 'nullable|string|max:255',
            'medify_api_email' => 'nullable|string|max:255',
            'medify_api_password' => 'nullable|string|max:255',
        ]);

        $data = [];
        if ($request->has('admin_wa_number')) {
            $data['admin_wa_number'] = $validated['admin_wa_number'] ?? '';
        }
        if ($request->has('gemini_api_key')) {
            $data['gemini_api_key'] = $validated['gemini_api_key'] ?? '';
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
            DB::table('bot_status')->where('id', 1)->update($data);
        }

        return response()->json(['message' => 'Settings saved']);
    }
}
