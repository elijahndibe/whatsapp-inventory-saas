<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LogController extends Controller
{
    private const MAX_BYTES = 200_000; // read at most the last ~200KB, log files can get huge

    public function index(): View
    {
        $path = storage_path('logs/laravel.log');
        $content = '';

        if (is_file($path)) {
            $size = filesize($path);
            $handle = fopen($path, 'r');
            $offset = max(0, $size - self::MAX_BYTES);
            fseek($handle, $offset);
            $content = fread($handle, self::MAX_BYTES);
            fclose($handle);

            if ($offset > 0) {
                $content = "... (truncated — showing the tail of the log) ...\n".$content;
            }
        }

        return view('admin.logs.index', ['content' => $content ?: 'No log entries yet.']);
    }
}
