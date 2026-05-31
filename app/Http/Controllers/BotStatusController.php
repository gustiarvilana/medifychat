<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BotStatusController extends Controller
{
    public function index(): JsonResponse
    {
        $status = DB::table('bot_status')->where('id', 1)->first();
        
        $recentCommands = DB::table('bot_commands')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentActivity = DB::table('user_sessions')
            ->orderBy('last_activity', 'desc')
            ->limit(5)
            ->get();

        // Check if API key exists
        $hasApiKey = !empty($status->gemini_api_key);
        
        // Treat as exhausted if database says so or if no key is configured
        $quotaExhausted = ($status->quota_exhausted ?? false) || !$hasApiKey;

        return response()->json([
            'is_running' => $status->is_running ?? false,
            'is_logged_in' => $status->is_logged_in ?? false,
            'last_activity' => $status->last_activity,
            'port' => $status->port ?? null,
            'qr_code' => $status->qr_code ?? null,
            'pid' => $status->pid ?? null,
            'quota_exhausted' => $quotaExhausted,
            'last_error' => $status->last_error ?? null,
            'last_error_at' => $status->last_error_at ?? null,
            'recent_commands' => $recentCommands,
            'recent_activity' => $recentActivity,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        DB::table('bot_commands')->insert([
            'command' => 'logout',
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Logout command sent']);
    }

    public function restart(Request $request): JsonResponse
    {
        DB::table('bot_commands')->insert([
            'command' => 'restart',
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Restart command sent']);
    }
}
