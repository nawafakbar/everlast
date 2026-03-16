<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashFlow;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class CashFlowController extends Controller
{
    public function index(Request $request)
    {
        // Default nampilin bulan dan tahun saat ini
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        // Tarik data berdasarkan filter bulan
        $cashFlows = CashFlow::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends(['month' => $month, 'year' => $year]);

        // Hitung total buat Dashboard Card
        $totalIncome = $cashFlows->where('type', 'income')->sum('amount');
        $totalExpense = $cashFlows->where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        return view('admin.cash_flows.index', compact('cashFlows', 'totalIncome', 'totalExpense', 'netBalance', 'month', 'year'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
        ]);

        CashFlow::create($validated);

        return back()->with('success', 'Catatan arus kas berhasil ditambahkan!');
    }

    public function destroy(CashFlow $cashFlow)
    {
        $cashFlow->delete();
        return back()->with('success', 'Catatan arus kas berhasil dihapus!');
    }

    public function exportPdf(Request $request)
    {
        // Tangkap filter bulan dan tahun dari URL
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        // Tarik data persis seperti fungsi index, tapi get() semua tanpa paging
        $cashFlows = CashFlow::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'asc') // Urutkan dari tanggal termuda biar rapi di laporan
            ->get();

        // Hitung total
        $totalIncome = $cashFlows->where('type', 'income')->sum('amount');
        $totalExpense = $cashFlows->where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        // Render ke file Blade khusus PDF
        $pdf = Pdf::loadView('admin.cash_flows.pdf', compact('cashFlows', 'totalIncome', 'totalExpense', 'netBalance', 'month', 'year'));
        
        // Atur ukuran kertas ke A4 (opsional)
        $pdf->setPaper('A4', 'portrait');

        $monthName = date('F', mktime(0, 0, 0, $month, 10));
        
        // Langsung otomatis ter-download
        return $pdf->download("Laporan_Kas_Everlast_{$monthName}_{$year}.pdf");
    }
}