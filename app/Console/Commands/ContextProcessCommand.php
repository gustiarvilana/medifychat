<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory as PhpWordIO;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIO;

class ContextProcessCommand extends Command
{
    protected $signature = 'context:process {id}';
    protected $description = 'Process uploaded document and extract content as Markdown';

    public function handle(): int
    {
        $id = $this->argument('id');
        $row = DB::table('bot_context')->find($id);

        if (!$row) {
            $this->error("Context #{$id} not found.");
            return Command::FAILURE;
        }

        if ($row->status === 'processing') {
            $this->warn("Context #{$id} is already being processed.");
            return Command::FAILURE;
        }

        DB::table('bot_context')->where('id', $id)->update([
            'status' => 'processing',
            'progress' => 0,
            'error_message' => null,
            'updated_at' => now(),
        ]);

        try {
            $path = storage_path("app/{$row->file_path}");
            if (!file_exists($path)) {
                throw new \RuntimeException("File tidak ditemukan di storage: {$row->file_path}");
            }

            switch ($row->type) {
                case 'txt':
                    $content = $this->processTxt($path);
                    break;
                case 'json':
                    $content = $this->processJson($path);
                    break;
                case 'docx':
                    $content = $this->processDocx($path);
                    break;
                case 'pdf':
                    $content = $this->processPdf($path);
                    break;
                case 'xlsx':
                    $content = $this->processXlsx($path);
                    break;
                default:
                    throw new \RuntimeException("Tipe file '{$row->type}' tidak didukung.");
            }

            DB::table('bot_context')->where('id', $id)->update([
                'content' => $content,
                'status' => 'completed',
                'progress' => 100,
                'updated_at' => now(),
            ]);

            $this->info("Context #{$id} processed successfully.");
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $msg = $this->friendlyError($e, $row->type);

            DB::table('bot_context')->where('id', $id)->update([
                'status' => 'failed',
                'progress' => 0,
                'error_message' => $msg,
                'updated_at' => now(),
            ]);

            $this->error("Context #{$id} failed: {$msg}");
            return Command::FAILURE;
        }
    }

    private function progress(int $id, int $pct): void
    {
        DB::table('bot_context')->where('id', $id)->update([
            'progress' => min($pct, 100),
            'updated_at' => now(),
        ]);
    }

    private function processTxt(string $path): string
    {
        $text = file_get_contents($path);
        return "```\n{$text}\n```";
    }

    private function processJson(string $path): string
    {
        $raw = file_get_contents($path);
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('File JSON tidak valid: ' . json_last_error_msg());
        }

        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            return $this->arrayToMarkdownTable($data);
        }

        return "```json\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n```";
    }

    private function processDocx(string $path): string
    {
        $this->progress((int) $this->argument('id'), 10);

        $phpWord = PhpWordIO::load($path);

        $this->progress((int) $this->argument('id'), 50);

        $lines = [];
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $lines[] = $element->getText();
                } elseif (method_exists($element, 'getElements')) {
                    foreach ($element->getElements() as $child) {
                        if (method_exists($child, 'getText')) {
                            $lines[] = $child->getText();
                        }
                    }
                }
            }
        }

        $this->progress((int) $this->argument('id'), 90);

        return implode("\n\n", array_filter($lines));
    }

    private function processPdf(string $path): string
    {
        $this->progress((int) $this->argument('id'), 10);

        $parser = new PdfParser();
        $pdf = $parser->parseFile($path);
        $pages = $pdf->getPages();
        $total = count($pages);

        if ($total === 0) {
            throw new \RuntimeException('File PDF kosong atau tidak bisa dibaca.');
        }

        $chunks = [];
        foreach ($pages as $i => $page) {
            $text = trim($page->getText());
            if ($text !== '') {
                $chunks[] = $text;
            }
            $pct = 10 + (int) ((40 / max($total, 1)) * ($i + 1));
            $this->progress((int) $this->argument('id'), $pct);
        }

        $this->progress((int) $this->argument('id'), 90);

        return implode("\n\n---\n\n", $chunks);
    }

    private function processXlsx(string $path): string
    {
        $this->progress((int) $this->argument('id'), 10);

        $spreadsheet = SpreadsheetIO::load($path);
        $sheets = $spreadsheet->getSheetNames();
        $total = count($sheets);
        $output = [];

        foreach ($sheets as $i => $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $data = $sheet->toArray();

            if (empty($data) || empty(array_filter($data))) {
                continue;
            }

            $output[] = "## {$sheetName}\n";
            $output[] = $this->arrayToMarkdownTable($data);

            $pct = 10 + (int) ((40 / max($total, 1)) * ($i + 1));
            $this->progress((int) $this->argument('id'), $pct);
        }

        $this->progress((int) $this->argument('id'), 90);

        return implode("\n\n", $output);
    }

    private function arrayToMarkdownTable(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $header = $data[0] ?? [];
        $header = array_map(fn($v) => is_string($v) ? $v : (string) $v, $header);
        $rows = array_slice($data, 1);

        $sep = '|' . implode('|', array_fill(0, count($header), '---')) . '|';
        $head = '| ' . implode(' | ', $header) . ' |';
        $body = '';

        foreach ($rows as $row) {
            $row = array_pad((array) $row, count($header), '');
            $cells = array_map(fn($v) => is_string($v) ? $v : (string) $v, $row);
            $body .= '| ' . implode(' | ', $cells) . " |\n";
        }

        return "{$head}\n{$sep}\n{$body}";
    }

    private function friendlyError(\Throwable $e, string $type): string
    {
        $msg = $e->getMessage();

        if (str_contains($msg, 'zip')) {
            return 'File ' . strtoupper($type) . ' tidak bisa dibaca. Pastikan file tidak rusak.';
        }
        if (str_contains($msg, 'password') || str_contains($msg, 'encrypted')) {
            return 'File ' . strtoupper($type) . ' dilindungi password. Harap unggah tanpa password.';
        }
        if (str_contains($msg, 'OLE')) {
            return 'Format .doc lawan belum didukung. Simpan sebagai .docx.';
        }
        if (str_contains($msg, 'Allocation')) {
            return 'File terlalu besar untuk diproses. Batas unggahan 50MB.';
        }

        return $msg;
    }
}
