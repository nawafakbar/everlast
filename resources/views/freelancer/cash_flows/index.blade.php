@extends('layouts.freelancer')

@section('content')
<div class="mb-2 border-b border-gray-200 pb-2 mt-2">
    <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Catat Pengeluaran</h2>
    <p class="text-gray-500 text-xs mt-1">Catat pengeluaran operasional kamu selama kerja.</p>
</div>

@if(session('success'))
    <div class="bg-green-50 text-green-700 p-3 mb-6 rounded-sm text-xs font-bold border border-green-200">
        {{ session('success') }}
    </div>
@endif

<form action="{{ route('freelancer.cash_flows.index') }}" method="GET" class="flex gap-2 mb-7">
    <select name="month" class="px-3 py-2 bg-white border border-gray-200 rounded-sm text-xs focus:outline-none focus:ring-1 focus:ring-black">
        @for($i = 1; $i <= 12; $i++)
            <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
        @endfor
    </select>
    <select name="year" class="px-3 py-2 bg-white border border-gray-200 rounded-sm text-xs focus:outline-none focus:ring-1 focus:ring-black">
        @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
    </select>
    <button type="submit" class="bg-black text-white px-4 py-2 text-xs font-medium uppercase tracking-wider rounded-sm hover:bg-gray-800">Filter</button>
</form>

<div class="bg-white p-6 border border-gray-200 rounded-sm shadow-sm mb-8 max-w-xs">
    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Pengeluaran Bulan Ini</p>
    <h3 class="text-2xl font-bold text-red-600">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h3>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-1">
        <div class="bg-white border border-gray-200 rounded-sm p-6">
            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-3 mb-4">Tambah Pengeluaran</h3>

            <form action="{{ route('freelancer.cash_flows.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Tanggal</label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-200 rounded-sm text-sm focus:outline-none focus:ring-1 focus:ring-black">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Kategori</label>
                        <select name="category" required class="w-full px-3 py-2 border border-gray-200 rounded-sm text-sm focus:outline-none focus:ring-1 focus:ring-black">
                            @foreach($categories as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Nominal (Rp)</label>
                        <input type="number" name="amount" required min="0" class="w-full px-3 py-2 border border-gray-200 rounded-sm text-sm focus:outline-none focus:ring-1 focus:ring-black" placeholder="Contoh: 50000">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Keterangan</label>
                        <textarea name="description" required rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-sm text-sm focus:outline-none focus:ring-1 focus:ring-black" placeholder="Cth: Bensin ke lokasi Jati Gede"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-black text-white px-4 py-2 text-xs font-medium uppercase tracking-wider rounded-sm hover:bg-gray-800 transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white border border-gray-200 rounded-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Keterangan</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4 text-right">Nominal</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($cashFlows as $flow)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ \Carbon\Carbon::parse($flow->date)->format('d M Y') }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $flow->description }}</td>
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-600 text-[10px] px-2 py-1 rounded-sm uppercase">{{ str_replace('_', ' ', $flow->category) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-red-600">
                                - Rp {{ number_format($flow->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('freelancer.cash_flows.destroy', $flow->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">Belum ada pengeluaran di bulan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            @if($cashFlows->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-white">
                    {{ $cashFlows->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection