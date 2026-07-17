<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\CashFlow;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CashFlowController extends Controller
{
    /**
     * Kategori yang boleh dipakai freelancer (dibatasi, nggak selengkap admin)
     */
    private array $allowedCategories = [
        'operational' => 'Operasional & Bensin',
        'other'       => 'Lainnya',
    ];

    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $cashFlows = CashFlow::where('reference_id', auth()->id())
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends(['month' => $month, 'year' => $year]);

        $totalExpense = CashFlow::where('reference_id', auth()->id())
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('type', 'expense')
            ->sum('amount');

        return view('freelancer.cash_flows.index', [
            'cashFlows' => $cashFlows,
            'totalExpense' => $totalExpense,
            'month' => $month,
            'year' => $year,
            'categories' => $this->allowedCategories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'category' => ['required', 'in:' . implode(',', array_keys($this->allowedCategories))],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        CashFlow::create([
            'reference_id' => auth()->id(),   // simpan user id kru yang input
            'type' => 'expense',              // kru cuma boleh nyatet pengeluaran
            'date' => $validated['date'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
        ]);

        return back()->with('success', 'Pengeluaran berhasil dicatat!');
    }

    public function destroy(CashFlow $cashFlow)
    {
        // Pastikan cuma boleh hapus punya sendiri
        abort_if($cashFlow->reference_id !== auth()->id(), 403);

        $cashFlow->delete();

        return back()->with('success', 'Catatan berhasil dihapus!');
    }
}