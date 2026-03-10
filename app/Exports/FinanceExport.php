<?php

namespace App\Exports;

use App\Models\Payment;
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
        // Tarik data persis seperti di controller (PLUS KONDISI SUCCESS)
        $payments = Payment::with('booking.user')
            ->where('status', 'success') // <--- INI TAMBAHANNYA BRO
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->orderBy('created_at')
            ->get();

        $totalRevenue = $payments->sum('amount');
        $totalDP = $payments->where('payment_type', 'dp')->sum('amount');
        $totalFullPayment = $totalRevenue - $totalDP;

        return view('admin.finance.export-template', [
            'payments' => $payments,
            'totalRevenue' => $totalRevenue,
            'totalDP' => $totalDP,
            'totalFullPayment' => $totalFullPayment,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
    }
}