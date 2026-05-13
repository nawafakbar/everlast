<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\CashFlow; // 👈 TAMBAHKAN INI
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinanceExport;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinancialReportController extends Controller
{
    private function resolveDates(Request $request)
    {
        $preset = $request->input('preset');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($preset) {
            switch ($preset) {
                case 'today':
                    $startDate = Carbon::today()->toDateString();
                    $endDate = Carbon::today()->toDateString();
                    break;
                case 'this_week':
                    $startDate = Carbon::now()->startOfWeek()->toDateString();
                    $endDate = Carbon::now()->endOfWeek()->toDateString();
                    break;
                case 'this_month':
                    $startDate = Carbon::now()->startOfMonth()->toDateString();
                    $endDate = Carbon::now()->endOfMonth()->toDateString();
                    break;
                case 'this_year':
                    $startDate = Carbon::now()->startOfYear()->toDateString();
                    $endDate = Carbon::now()->endOfYear()->toDateString();
                    break;
            }
        }

        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
        }

        return [$startDate, $endDate, $preset];
    }

    public function index(Request $request)
    {
        [$startDate, $endDate, $preset] = $this->resolveDates($request);

        // === INCOME (dari Payment) ===
        $query = Payment::with(['booking.user', 'booking.package'])->where('status', 'success');
        $query->whereDate('created_at', '>=', $startDate)
              ->whereDate('created_at', '<=', $endDate);

        $totalsQuery = clone $query;
        $totalRevenue = $totalsQuery->sum('amount');
        
        $dpQuery = clone $query;
        $totalDP = $dpQuery->where('payment_type', 'dp')->sum('amount');
        
        $fullPaymentQuery = clone $query;
        $totalFullPayment = $fullPaymentQuery->where('payment_type', 'pelunasan')->sum('amount');

        $payments = $query->latest()->paginate(10)->appends([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'preset' => $preset
        ]);

        // === EXPENSES (dari CashFlow) 👇 TAMBAHAN BARU ===
        $totalExpenses = CashFlow::where('type', 'expense')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->sum('amount');

        // === NET PROFIT/LOSS ===
        $netProfit = $totalRevenue - $totalExpenses;

        // === CHART DATA ===
        $chartQuery = Payment::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'success')
            ->orderBy('created_at')
            ->get();

        $diffInDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));

        $chartLabels = [];
        $chartData = [];

        if ($diffInDays > 62) {
            $grouped = $chartQuery->groupBy(function($item) { 
                return $item->created_at->format('M Y'); 
            });
            
            $period = \Carbon\CarbonPeriod::create(Carbon::parse($startDate)->startOfMonth(), '1 month', Carbon::parse($endDate)->startOfMonth());
            
            foreach ($period as $date) {
                $fmt = $date->format('M Y');
                $chartLabels[] = $fmt;
                $chartData[] = $grouped->has($fmt) ? $grouped[$fmt]->sum('amount') : 0;
            }
        } else {
            $grouped = $chartQuery->groupBy(function($item) { 
                return $item->created_at->format('d M'); 
            });
            
            $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
            
            foreach ($period as $date) {
                $fmt = $date->format('d M');
                $chartLabels[] = $fmt;
                $chartData[] = $grouped->has($fmt) ? $grouped[$fmt]->sum('amount') : 0;
            }
        }

        return view('admin.finance.index', compact(
            'payments', 
            'totalRevenue', 
            'totalDP', 
            'totalFullPayment', 
            'totalExpenses',    // 👈 TAMBAHKAN
            'netProfit',        // 👈 TAMBAHKAN
            'startDate', 
            'endDate',
            'chartLabels',
            'chartData'
        ));
    }

    public function exportPdf(Request $request)
    {
        [$startDate, $endDate] = $this->resolveDates($request);

        $payments = Payment::with('booking.user')
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at')
            ->get();

        $totalRevenue = $payments->sum('amount');
        $totalDP = $payments->where('payment_type', 'dp')->sum('amount');
        $totalFullPayment = $totalRevenue - $totalDP;

        // 👇 TAMBAHKAN EXPENSES
        $totalExpenses = CashFlow::where('type', 'expense')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->sum('amount');

        $netProfit = $totalRevenue - $totalExpenses;

        $pdf = Pdf::loadView('admin.finance.export-template', compact(
            'payments', 
            'totalRevenue', 
            'totalDP', 
            'totalFullPayment', 
            'totalExpenses',    // 👈 TAMBAHKAN
            'netProfit',        // 👈 TAMBAHKAN
            'startDate', 
            'endDate'
        ));

        $fileName = 'Everlast_Finance_' . Carbon::parse($startDate)->format('M_Y') . '.pdf';
        
        return $pdf->download($fileName);
    }

    public function exportExcel(Request $request)
    {
        [$startDate, $endDate] = $this->resolveDates($request);

        $fileName = 'Everlast_Finance_' . Carbon::parse($startDate)->format('M_Y') . '.xlsx';
        
        return Excel::download(new FinanceExport($startDate, $endDate), $fileName);
    }
}