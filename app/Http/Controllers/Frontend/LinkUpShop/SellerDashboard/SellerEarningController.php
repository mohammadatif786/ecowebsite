<?php

namespace App\Http\Controllers\Frontend\LinkUpShop\SellerDashboard;

use App\Http\Controllers\Controller;
use App\Services\SellerEarningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SellerEarningController extends Controller
{
    public function __construct(
        private SellerEarningService $service
    ) {}

    /**
     * Earnings page — running total, per-period, per-product.
     */
    public function earnings(Request $request)
    {
        $period      = $request->get('period', '30d');
        $customStart = $request->get('start');
        $customEnd   = $request->get('end');

        $data = $this->service->getEarningsData(Auth::user(), $period, $customStart, $customEnd);

        return Inertia::render('User/LinkUpShop/SellerDashboard/Earnings', $data);
    }

    /**
     * Get wallet balance as JSON for AJAX requests
     */
    public function getWalletBalance()
    {
        $user = Auth::user();
        $walletBalance = \O21\LaravelWallet\Models\Balance::where('payable_id', $user->id)
            ->where('payable_type', get_class($user))
            ->where('currency', 'USD')
            ->first();

        $balanceValue = $walletBalance ? $walletBalance->value->get() : 0;

        return response()->json([
            'walletBalance' => $balanceValue,
        ]);
    }

    /**
     * Get Sales Report data
     */
    public function getSalesReport(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $salesReport = $this->service->getSalesReport(Auth::id(), $startDate, $endDate);

        return response()->json([
            'sales_report' => $salesReport,
        ]);
    }

    /**
     * Get Cash-Out Report data
     */
    public function getCashOutReport()
    {
        $cashOutReport = $this->service->getCashOutReport(Auth::id());

        return response()->json([
            'cash_out_report' => $cashOutReport,
        ]);
    }

    /**
     * Get Wallet Funding Report data
     */
    public function getWalletFundingReport()
    {
        $walletFundingReport = $this->service->getWalletFundingReport(Auth::id());

        return response()->json([
            'wallet_funding_report' => $walletFundingReport,
        ]);
    }

    /**
     * Generate invoice for a specific order (returns HTML for PDF conversion)
     */
    public function generateInvoice($orderId)
    {
        $invoiceData = $this->service->getInvoiceData(Auth::id(), $orderId);

        if (!$invoiceData) {
            return response()->json([
                'error' => 'Order not found or does not belong to you',
            ], 404);
        }

        // Generate HTML invoice
        $html = view('invoices.marketplace_order', [
            'invoice' => $invoiceData,
        ])->render();

        // Return HTML file for download (could be converted to PDF with DomPDF if needed)
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="invoice-' . $invoiceData['order_number'] . '.html"');
    }
}
