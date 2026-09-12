<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class WalletReportController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::query()->with(['to', 'from'])
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('admin/Reports/Wallet/Index', [
            'transactions' => $transactions,
            'filters' => $request->only('search'),
            'message' => session('message'),
        ]);
    }

    public function downloadReport(Request $request)
    {
        $transactions = Transaction::query()->with(['to', 'from'])
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->get();

        $pdf = Pdf::loadView('Reports.TransactionReport', [
            'title'   => 'Transection Peport Document',
            'content' => $transactions,
        ]);
        return $pdf->download('Transaction_Report_' . now()->format('Y-m-d') . '.pdf');
    }
}
