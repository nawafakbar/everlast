<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinancialReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['booking.user', 'booking.package'])->where('status', 'success');

        $preset = $request->input('preset');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Logika Tombol Shortcut (Preset)
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

        // Kalau nggak ada filter sama sekali, default ke bulan ini
        if (!$startDate || !$endDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
        }

        // Terapkan Filter Tanggal
        $query->whereDate('created_at', '>=', $startDate)
              ->whereDate('created_at', '<=', $endDate);

        // Kloning Query untuk ngitung Total (biar nggak bentrok sama Pagination)
        $totalsQuery = clone $query;
        $totalRevenue = $totalsQuery->sum('amount');
        
        $dpQuery = clone $query;
        $totalDP = $dpQuery->where('payment_type', 'dp')->sum('amount');
        
        $fullPaymentQuery = clone $query;
        $totalFullPayment = $fullPaymentQuery->where('payment_type', 'pelunasan')->sum('amount');

        // Tarik Data dengan Pagination (10 data per halaman) & Simpan parameter URL-nya
        $payments = $query->latest()->paginate(10)->appends([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'preset' => $preset
        ]);

        return view('admin.finance.index', compact('payments', 'totalRevenue', 'totalDP', 'totalFullPayment', 'startDate', 'endDate', 'preset'));
    }
}