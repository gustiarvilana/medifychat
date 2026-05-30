<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AlertLogController extends Controller
{
    public function data(Request $request)
    {
        // Asumsikan log error disimpan di file log atau tabel khusus.
        // Jika belum ada tabel log, kita akan mensimulasikannya.
        // Untuk demo, kita ambil dari logs yang bisa diakses via file log Laravel.
        // Dalam produksi, disarankan menggunakan tabel khusus untuk log error.
        
        $logs = [];
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            $content = file_get_contents($logPath);
            // Parsing sederhana log untuk contoh
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
}
