<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExportTransactionsRequest;
use App\Services\TransactionExportService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionExportController extends Controller
{
    public function __invoke(ExportTransactionsRequest $request, TransactionExportService $export): StreamedResponse
    {
        $data = $request->validated();
        $kind = $data['jenis'] ?? 'properti';
        $status = $data['status'] ?? null;
        $from = $data['from'] ?? null;
        $to = $data['to'] ?? null;
        $delimiter = ($data['delimiter'] ?? 'semicolon') === 'comma' ? ',' : ';';

        Log::info('transactions.export', ['admin_id' => $request->user()->id, 'kind' => $kind, 'status' => $status, 'from' => $from, 'to' => $to]);

        return response()->streamDownload(function () use ($export, $kind, $status, $from, $to, $delimiter) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8

            fputcsv($out, $export->headers($kind), $delimiter, '"', '\\');

            foreach ($export->rows($kind, $status, $from, $to) as $row) {
                fputcsv($out, $row, $delimiter, '"', '\\');
            }

            fclose($out);
        }, $export->filename($kind, $from, $to), [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store',
        ]);
    }
}
