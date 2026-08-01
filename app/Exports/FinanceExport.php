<?php

namespace App\Exports;

use App\Models\Payment;
use App\Models\CashFlow;
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
        // Income Data
        $payments = Payment::with('booking.user')
            ->where('status', 'success')
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->orderBy('created_at')
            ->get();

        $totalRevenue = $payments->sum('amount');
        $totalDP = $payments->where('payment_type', 'dp')->sum('amount');
        $totalFullPayment = $totalRevenue - $totalDP;

        // expenses data baru
        $expenses = CashFlow::where('type', 'expense')
            ->whereDate('date', '>=', $this->startDate)
            ->whereDate('date', '<=', $this->endDate)
            ->orderBy('date')
            ->get();

        $totalExpenses = $expenses->sum('amount');

        // net profit calculation
        $netProfit = $totalRevenue - $totalExpenses;

        return view('admin.finance.export-template', [
            'payments' => $payments,
            'expenses' => $expenses,           
            'totalRevenue' => $totalRevenue,
            'totalDP' => $totalDP,
            'totalFullPayment' => $totalFullPayment,
            'totalExpenses' => $totalExpenses, 
            'netProfit' => $netProfit,         
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
    }
}