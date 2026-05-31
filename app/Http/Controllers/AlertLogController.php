<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AlertLogController extends Controller
{
    public function data(Request $request)
    {
        $logs = [];
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            $content = file_get_contents($logPath);
            preg_match_all('/\[(.*)\] local.(ERROR|WARNING): (.*)/', $content, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $logs[] = [
                    'created_at' => $match[1],
                    'level' => $match[2],
                    'message' => $match[3]
                ];
            }
        }

        return DataTables::of(collect(array_reverse($logs)))->make(true);
    }

    public function stream(Request $request)
    {
        $lines = (int) $request->get('lines', 50);
        $botDir = base_path('whatsapp-bot');

        $files = [
            'bot.log' => $botDir . '/bot.log',
            'bot-err.log' => $botDir . '/bot-err.log',
        ];

        $result = [];
        foreach ($files as $key => $path) {
            if (file_exists($path)) {
                $content = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $recent = array_slice($content, -$lines);
                foreach ($recent as $line) {
                    $result[] = [
                        'file' => $key,
                        'text' => $line,
                    ];
                }
            }
        }

        // Sort by file order (bot.log first, then bot-err.log), keep newest last
        return response()->json($result);
    }
}
