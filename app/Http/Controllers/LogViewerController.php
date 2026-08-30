<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Rap2hpoutre\LaravelLogViewer\LaravelLogViewer;

class LogViewerController extends Controller
{
    public function index(Request $request)
    {
        $log = new LaravelLogViewer;

        if ($request->filled('file')) {
            $log->setFile(basename($request->file));
        }

        $logs = $log->all();
        $level = $request->get('level');

        if ($level) {
            $logs = array_filter($logs, fn ($e) => ($e['level'] ?? '') === $level);
        }

        $levels = ['error', 'warning', 'info', 'debug', 'notice', 'critical', 'alert', 'emergency'];

        return view('monitoring.logs.index', [
            'logs' => $logs,
            'files' => $log->getFiles(true),
            'current' => $log->getFileName(),
            'levels' => $levels,
            'activeLevel' => $level,
        ]);
    }
}
