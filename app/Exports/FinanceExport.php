<?php

namespace App\Exports;

use App\Models\Payment;
use App\Models\CashFlow; // 👈 TAMBAHKAN INI
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FinanceExport implements FromView, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        // === INCOME DATA ===
        $payments = Payment::with('booking.user')
            ->where('status', 'success')
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->orderBy('created_at')
            ->get();

        $totalRevenue = $payments->sum('amount');
        $totalDP = $payments->where('payment_type', 'dp')->sum('amount');
        $totalFullPayment = $totalRevenue - $totalDP;

        // === EXPENSES DATA 👇 BARU ===
        $expenses = CashFlow::where('type', 'expense')
            ->whereDate('date', '>=', $this->startDate)
            ->whereDate('date', '<=', $this->endDate)
            ->orderBy('date')
            ->get();

        $totalExpenses = $expenses->sum('amount');

        // === NET PROFIT ===
        $netProfit = $totalRevenue - $totalExpenses;

        return view('admin.finance.export-template', [
            'payments' => $payments,
            'expenses' => $expenses,           // 👈 TAMBAHKAN
            'totalRevenue' => $totalRevenue,
            'totalDP' => $totalDP,
            'totalFullPayment' => $totalFullPayment,
            'totalExpenses' => $totalExpenses, // 👈 TAMBAHKAN
            'netProfit' => $netProfit,         // 👈 TAMBAHKAN
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
    }
}