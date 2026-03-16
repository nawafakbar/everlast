<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashFlow;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
            ->get();

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
}