<?php

namespace App\Http\Controllers;

use App\Services\AuditQueryService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditController extends Controller
{
    public function __construct(private AuditQueryService $audit) {}

    public function index(Request $request): View
    {
        // ponytail: keyset not required at this scale; offset paginate is fine
        $activities = $this->audit->forFilters($request)->paginate(20)->withQueryString();
        $actions = $this->audit->distinctActions();

        return view('monitoring.audit.index', compact('activities', 'actions'));
    }

    public function export(Request $request): StreamedResponse
    {
        // ponytail: stream CSV, no package. Escape fields per RFC 4180.
        $rows = $this->audit->forFilters($request)->latest()->get();
        $filename = 'audit-log-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['time', 'action', 'causer', 'subject_type', 'subject_id', 'ip', 'user_agent']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->created_at->format('Y-m-d H:i:s'),
                    $r->description,
                    $r->causer->username ?? ($r->properties['identifier'] ?? ''),
                    $r->subject_type ? class_basename($r->subject_type) : '',
                    $r->subject_id ?? '',
                    $r->properties['ip'] ?? '',
                    $r->properties['user_agent'] ?? '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
