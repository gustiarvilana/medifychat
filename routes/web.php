<?php

use App\Http\Controllers\BotProcessController;
use App\Http\Controllers\BotSettingsController;
use App\Http\Controllers\BotStatusController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/debug/qr', function () {
    $status = DB::table('bot_status')->where('id', 1)->first();
    return response()->json([
        'has_status' => $status ? true : false,
        'is_running' => $status->is_running ?? null,
        'is_logged_in' => $status->is_logged_in ?? null,
        'qr_type' => $status ? gettype($status->qr_code) : 'no_status',
        'qr_len' => $status ? strlen($status->qr_code ?? '') : 0,
        'qr_preview' => $status ? substr($status->qr_code ?? '', 0, 60) : null,
        'has_png_header' => $status && strpos($status->qr_code ?? '', 'data:image/png') === 0 ? true : false,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/settings', function () {
        return view('settings');
    })->name('settings');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Bot status API
    Route::get('/bot/status', [BotStatusController::class, 'index']);
    Route::get('/bot/logs', [\App\Http\Controllers\AlertLogController::class, 'data'])->name('bot.logs');
    Route::post('/bot/logout', [BotStatusController::class, 'logout']);
    Route::post('/bot/restart-cmd', [BotStatusController::class, 'restart']);

    // Bot process management
    Route::post('/bot/start', [BotProcessController::class, 'start']);
    Route::post('/bot/stop', [BotProcessController::class, 'stop']);
    Route::post('/bot/restart', [BotProcessController::class, 'restart']);

    // Bot settings
    Route::get('/bot/settings', [BotSettingsController::class, 'index']);
    Route::post('/bot/settings', [BotSettingsController::class, 'update']);

    // Context management — page view
    Route::get('/settings/context', function () {
        return view('context');
    })->name('context.index');

    // Context management — API
    Route::prefix('api/context')->group(function () {
        Route::get('/', [\App\Http\Controllers\ContextController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\ContextController::class, 'store']);
        Route::get('{id}', [\App\Http\Controllers\ContextController::class, 'show']);
        Route::patch('{id}', [\App\Http\Controllers\ContextController::class, 'update']);
        Route::delete('{id}', [\App\Http\Controllers\ContextController::class, 'destroy']);
        Route::patch('{id}/toggle', [\App\Http\Controllers\ContextController::class, 'toggle']);
        Route::post('{id}/retry', [\App\Http\Controllers\ContextController::class, 'retry']);
    });
});

require __DIR__.'/auth.php';

Route::get('/opclean', function () { if (function_exists('opcache_reset')) { opcache_reset(); } return 'cleared'; });

Route::get('/check-env', function () { return response()->json(['exec_enabled' => function_exists('exec'), 'disable_functions' => ini_get('disable_functions'), 'os' => PHP_OS_FAMILY, 'php_version' => PHP_VERSION, 'user' => getenv('USERNAME') ?: getenv('USER') ?: 'unknown', 'node' => trim(shell_exec('where node 2>NUL') ?: 'not found')]); });

Route::get('/check-node', function () { $output = []; exec('where node 2>NUL', $output, $code); $paths = ['C:\\\\Program Files\\\\nodejs\\\\node.exe', 'C:\\\\Program Files (x86)\\\\nodejs\\\\node.exe']; $exists = []; foreach ($paths as $p) { $exists[$p] = file_exists($p); } return response()->json(['where_code' => $code, 'where_output' => $output, 'file_exists_program_files' => $exists, 'laragon_node' => file_exists('C:\\\\laragon\\\\bin\\\\nodejs\\\\node-v18\\\\node.exe')]); });

Route::get('/debug-start', function () { $c = new App\Http\Controllers\BotProcessController(); return $c->start(new Illuminate\Http\Request(['port' => 3992])); });
