<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BotProcessController extends Controller
{
    public function start(Request $request): JsonResponse
    {
        $status = DB::table('bot_status')->where('id', 1)->first();

        $request->validate([
            'port' => 'nullable|integer|min:1024|max:65535',
        ]);

        $port = $request->input('port', $status->port ?? 3001);

        if ($this->isDockerMode()) {
            if ($this->isDockerContainerRunning()) {
                return response()->json(['error' => 'Bot is already running'], 409);
            }

            $result = $this->dockerApiCall('POST', '/containers/medify-bot/start');

            if ($result === false) {
                return response()->json([
                    'error' => 'Failed to start Docker container (HTTP ' . ($this->lastDockerHttpCode ?? 'N/A') . ')'
                ], 500);
            }

            DB::table('bot_status')->where('id', 1)->update([
                'is_running' => true,
                'is_logged_in' => false,
                'port' => $port,
                'pid' => null,
                'qr_code' => null,
                'last_activity' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'message' => 'Bot started successfully',
                'port' => $port,
            ]);
        }

        if ($status && $status->is_running && $status->pid) {
            if ($this->isProcessRunning($status->pid)) {
                return response()->json(['error' => 'Bot is already running'], 409);
            }
        }

        $nodePath = $this->findNodePath();
        if (!$nodePath) {
            return response()->json(['error' => 'Node.js not found'], 500);
        }

        $botDir = base_path('whatsapp-bot');

        DB::table('bot_status')->where('id', 1)->update([
            'is_running' => true,
            'is_logged_in' => false,
            'port' => $port,
            'pid' => null,
            'qr_code' => null,
            'last_activity' => now(),
            'updated_at' => now(),
        ]);

        if (PHP_OS_FAMILY === 'Windows') {
            $pid = $this->startBotWindows($nodePath, $botDir, $port);
        } else {
            $pid = $this->startBotUnix($nodePath, $botDir, $port);
        }

        if ($pid) {
            DB::table('bot_status')->where('id', 1)->update(['pid' => $pid]);
        }

        return response()->json([
            'message' => 'Bot started successfully',
            'pid' => $pid,
            'port' => $port,
        ]);
    }

    private function startBotWindows(string $nodePath, string $botDir, int $port): ?int
    {
        $psPath = $this->findPowerShellPath();

        if ($psPath) {
            $psCmd = "`\$p = Start-Process -FilePath '$nodePath' -ArgumentList 'src/index.js', '$port' -WorkingDirectory '$botDir' -NoNewWindow -PassThru -RedirectStandardOutput '$botDir\\bot.log' -RedirectStandardError '$botDir\\bot-err.log'; Write-Output `\$p.Id";
            $output = [];
            $cmd = "\"$psPath\" -NoProfile -Command \"$psCmd\" 2>&1";
            exec($cmd, $output, $exitCode);

            if ($exitCode === 0 && !empty($output) && is_numeric(trim($output[0]))) {
                return (int) trim($output[0]);
            }

            Log::warning('PowerShell start failed, falling back to CMD', [
                'exitCode' => $exitCode,
                'output' => implode("\n", $output),
            ]);
        }

        return $this->startBotWindowsCmd($nodePath, $botDir, $port);
    }

    private function startBotWindowsCmd(string $nodePath, string $botDir, int $port): ?int
    {
        $helperScript = "$botDir\\start-bot.js";
        $cmd = "\"$nodePath\" \"$helperScript\" $port";
        $output = [];
        exec($cmd, $output, $exitCode);

        Log::error('Node helper exec result', [
            'exitCode' => $exitCode,
            'output' => $output,
            'nodePath' => $nodePath,
            'helperScript' => $helperScript,
            'cmd' => $cmd,
        ]);

        if ($exitCode !== 0) {
            return null;
        }

        $pid = isset($output[0]) ? (int) trim($output[0]) : null;

        if ($pid && $pid > 0) {
            return $pid;
        }

        $found = $this->findBotPid($port);
        Log::error('findBotPid fallback result', ['pid' => $found]);
        return $found;
    }

    private function startBotUnix(string $nodePath, string $botDir, int $port): ?int
    {
        $cmd = "nohup $nodePath " . escapeshellarg($botDir . '/src/index.js') . " $port > $botDir/bot.log 2>&1 & echo \$!";
        $output = [];
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            DB::table('bot_status')->where('id', 1)->update(['is_running' => false]);
            Log::error('Unix start failed', ['exitCode' => $exitCode, 'cmd' => $cmd]);
            return null;
        }

        return isset($output[0]) ? (int) trim($output[0]) : null;
    }

    private function findPowerShellPath(): ?string
    {
        $paths = [
            getenv('SystemRoot') . '\\System32\\WindowsPowerShell\\v1.0\\powershell.exe',
            'C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\powershell.exe',
            'C:\\Windows\\SysWOW64\\WindowsPowerShell\\v1.0\\powershell.exe',
        ];

        foreach ($paths as $p) {
            if (file_exists($p)) {
                return $p;
            }
        }

        $output = [];
        exec('where powershell 2>NUL', $output, $code);
        if ($code === 0 && !empty($output)) {
            return $output[0];
        }

        return null;
    }

    public function stop(): JsonResponse
    {
        if ($this->isDockerMode()) {
            $result = $this->dockerApiCall('POST', '/containers/medify-bot/stop');

            if ($result === false) {
                $running = $this->isDockerContainerRunning();
                if (!$running) {
                    DB::table('bot_status')->where('id', 1)->update([
                        'is_running' => false,
                        'is_logged_in' => false,
                        'pid' => null,
                        'qr_code' => null,
                        'updated_at' => now(),
                    ]);
                    return response()->json(['message' => 'Bot stopped (container was already stopped)']);
                }
                return response()->json([
                    'error' => 'Failed to stop Docker container (HTTP ' . ($this->lastDockerHttpCode ?? 'N/A') . ')'
                ], 500);
            }

            DB::table('bot_status')->where('id', 1)->update([
                'is_running' => false,
                'is_logged_in' => false,
                'pid' => null,
                'qr_code' => null,
                'updated_at' => now(),
            ]);

            return response()->json(['message' => 'Bot stopped successfully']);
        }

        $status = DB::table('bot_status')->where('id', 1)->first();

        if (!$status || !$status->pid) {
            $this->killBotProcesses();
            DB::table('bot_status')->where('id', 1)->update([
                'is_running' => false,
                'is_logged_in' => false,
                'pid' => null,
                'qr_code' => null,
                'updated_at' => now(),
            ]);
            return response()->json(['message' => 'Bot stopped']);
        }

        if ($this->isProcessRunning($status->pid)) {
            if (PHP_OS_FAMILY === 'Windows') {
                exec("taskkill /F /PID {$status->pid} 2>NUL");
            } else {
                exec("kill -9 {$status->pid} 2>/dev/null");
            }
        }

        $this->killBotProcesses();

        DB::table('bot_status')->where('id', 1)->update([
            'is_running' => false,
            'is_logged_in' => false,
            'pid' => null,
            'qr_code' => null,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Bot stopped successfully']);
    }

    public function restart(Request $request): JsonResponse
    {
        if ($this->isDockerMode()) {
            $result = $this->dockerApiCall('POST', '/containers/medify-bot/restart');

            if ($result === false) {
                return response()->json([
                    'error' => 'Failed to restart Docker container (HTTP ' . ($this->lastDockerHttpCode ?? 'N/A') . ')'
                ], 500);
            }

            DB::table('bot_status')->where('id', 1)->update([
                'is_running' => true,
                'is_logged_in' => false,
                'pid' => null,
                'qr_code' => null,
                'updated_at' => now(),
            ]);

            return response()->json(['message' => 'Bot restarted successfully']);
        }

        $this->stop();
        sleep(1);
        $status = DB::table('bot_status')->where('id', 1)->first();
        $port = $request->input('port', $status->port ?? 3001);

        return $this->start(new Request(['port' => $port]));
    }

    private function findNodePath(): ?string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = [];
            exec("where node 2>NUL", $output, $code);
            if ($code === 0 && !empty($output)) {
                return $output[0];
            }
            $commonPaths = [
                'C:\\Program Files\\nodejs\\node.exe',
                'C:\\Program Files (x86)\\nodejs\\node.exe',
                getenv('LOCALAPPDATA') . '\\fnm\\node-versions\\latest\\installation\\node.exe',
            ];
            foreach ($commonPaths as $p) {
                if (file_exists($p)) {
                    return $p;
                }
            }
            return 'node';
        } else {
            $output = [];
            exec("which node 2>/dev/null", $output, $code);
            return $code === 0 ? $output[0] : 'node';
        }
    }

    private function isProcessRunning(int $pid): bool
    {
        if ($this->isDockerMode()) {
            return $this->isDockerContainerRunning();
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $output = [];
            exec("tasklist /FI \"PID eq {$pid}\" 2>NUL", $output, $code);
            return $code === 0 && count($output) > 3;
        } else {
            exec("kill -0 {$pid} 2>/dev/null", $output, $code);
            return $code === 0;
        }
    }

    private function findBotPid(int $port): ?int
    {
        if ($this->isDockerMode()) {
            return null;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $psPath = $this->findPowerShellPath();
            if ($psPath) {
                $psCmd = "Get-CimInstance Win32_Process -Filter \"Name='node.exe'\" | Where-Object { `\$_.CommandLine -like '*src/index.js*' -and `\$_.CommandLine -like '*$port*' } | Select-Object -ExpandProperty ProcessId";
                $output = [];
                $cmd = "\"$psPath\" -NoProfile -Command \"$psCmd\" 2>&1";
                exec($cmd, $output, $code);
                if ($code === 0 && !empty($output) && is_numeric(trim(end($output)))) {
                    return (int) trim(end($output));
                }
            }

            // Fallback: netstat to find PID listening on the bot port
            $output = [];
            exec("netstat -ano 2>NUL", $output, $code);
            if ($code === 0) {
                foreach ($output as $line) {
                    if (preg_match('/:' . $port . '\s.*LISTENING\s+(\d+)$/i', $line, $m)) {
                        return (int) $m[1];
                    }
                }
            }

            return null;
        } else {
            $output = [];
            exec("pgrep -f 'src/index.js.*{$port}'", $output, $code);
            if ($code === 0 && !empty($output)) {
                return (int) end($output);
            }
            return null;
        }
    }

    private function isDockerMode(): bool
    {
        $socket = '/var/run/docker.sock';
        return file_exists($socket) && is_readable($socket) && is_writable($socket);
    }

    private ?int $lastDockerHttpCode = null;

    private function dockerApiCall(string $method, string $endpoint): array|bool
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "http://localhost{$endpoint}",
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_UNIX_SOCKET_PATH => '/var/run/docker.sock',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $this->lastDockerHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($this->lastDockerHttpCode >= 200 && $this->lastDockerHttpCode < 300) {
            $decoded = json_decode($response, true);
            return $decoded !== null ? $decoded : true;
        }

        Log::error('Docker API call failed', [
            'method' => $method,
            'endpoint' => $endpoint,
            'httpCode' => $this->lastDockerHttpCode,
            'response' => $response,
        ]);

        return false;
    }

    private function isDockerContainerRunning(): bool
    {
        $info = $this->dockerApiCall('GET', '/containers/medify-bot/json');
        return $info && ($info['State']['Running'] ?? false);
    }

    private function killBotProcesses(): void
    {
        if ($this->isDockerMode()) {
            return;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $psPath = $this->findPowerShellPath();
            if ($psPath) {
                $psCmd = "Get-CimInstance Win32_Process -Filter \"Name='node.exe'\" | Where-Object { `\$_.CommandLine -like '*src/index.js*' } | ForEach-Object { Stop-Process -Id `\$_.ProcessId -Force }";
                exec("\"$psPath\" -NoProfile -Command \"$psCmd\" 2>NUL", $output, $code);
            }
            exec("taskkill /F /IM node.exe /T 2>NUL");
        } else {
            exec("pkill -f 'src/index.js' 2>/dev/null");
        }
    }
}
