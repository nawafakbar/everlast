@extends('layouts.admin')

@section('content')
<div class="mb-4 border-b border-gray-200 pb-4 mt-2 flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Financial Report</h2>
        <p class="text-gray-500 text-xs mt-1">Monitor cash flow, incoming down payments, and completed transactions.</p>
    </div>
    
    <div class="flex gap-3">
        <a href="{{ route('admin.finance.pdf', request()->query()) }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 text-[10px] font-bold uppercase tracking-widest rounded-sm hover:bg-gray-50 hover:text-red-600 transition-colors flex items-center shadow-sm">
            <i class="fas fa-file-pdf mr-2 text-red-500"></i> Export PDF
        </a>
        <a href="{{ route('admin.finance.excel', request()->query()) }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 text-[10px] font-bold uppercase tracking-widest rounded-sm hover:bg-gray-50 hover:text-green-700 transition-colors flex items-center shadow-sm">
            <i class="fas fa-file-excel mr-2 text-green-600"></i> Export Excel
        </a>
    </div>
</div>

<form action="{{ route('admin.finance') }}" method="GET" class="mb-8 flex flex-col sm:flex-row items-start sm:items-center gap-4">
    <div class="flex flex-wrap items-center gap-1 bg-gray-50 border border-gray-200 rounded-sm p-1">
        <a href="?preset=today" class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-sm transition-colors {{ request('preset') == 'today' ? 'bg-black text-white' : 'text-gray-500 hover:text-black' }}">Today</a>
        <a href="?preset=this_week" class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-sm transition-colors {{ request('preset') == 'this_week' ? 'bg-black text-white' : 'text-gray-500 hover:text-black' }}">This Week</a>
        <a href="?preset=this_month" class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-sm transition-colors {{ request('preset') == 'this_month' || (!request('preset') && !request('start_date')) ? 'bg-black text-white' : 'text-gray-500 hover:text-black' }}">This Month</a>
        <a href="?preset=this_year" class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-sm transition-colors {{ request('preset') == 'this_year' ? 'bg-black text-white' : 'text-gray-500 hover:text-black' }}">This Year</a>
    </div>
    
    <div class="flex flex-wrap items-center gap-2">
        <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 bg-white border border-gray-200 rounded-sm text-xs focus:outline-none focus:border-black text-gray-700 cursor-pointer">
        <span class="text-xs text-gray-400 font-medium">to</span>
        <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 bg-white border border-gray-200 rounded-sm text-xs focus:outline-none focus:border-black text-gray-700 cursor-pointer">
        
        <button type="submit" class="bg-black text-white px-4 py-2 text-[10px] font-bold uppercase tracking-widest rounded-sm hover:bg-gray-800 transition-colors shadow-sm ml-2">Filter</button>
        @if(request()->has('start_date') || request()->has('preset'))
            <a href="{{ route('admin.finance') }}" class="px-4 py-2 border border-gray-200 text-gray-500 hover:text-black hover:bg-gray-50 text-[10px] font-bold uppercase tracking-widest rounded-sm transition-colors">Reset</a>
        @endif
    </div>
</form>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white border border-gray-200 p-6 rounded-sm shadow-sm flex flex-col justify-center">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Revenue</p>
        <h3 class="text-3xl font-bold text-gray-900">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
        <p class="text-[10px] text-gray-400 mt-2 italic font-serif">Filtered Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    </div>
    <div class="bg-gray-50 border border-gray-200 p-6 rounded-sm flex flex-col justify-center">
        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Total Down Payment</p>
        <h3 class="text-xl font-bold text-gray-800">Rp {{ number_format($totalDP, 0, ',', '.') }}</h3>
    </div>
    <div class="bg-gray-50 border border-gray-200 p-6 rounded-sm flex flex-col justify-center">
        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Total Full Payment</p>
        <h3 class="text-xl font-bold text-gray-800">Rp {{ number_format($totalFullPayment, 0, ',', '.') }}</h3>
    </div>
</div>

<div class="bg-white border border-gray-200 p-6 rounded-sm shadow-sm mb-8">
    <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Revenue Trend</h3>
    <div class="relative h-72 w-full">
        <canvas id="revenueChart"></canvas>
    </div>
</div>

<div class="bg-white border border-gray-200 rounded-sm shadow-sm overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
        <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Transaction History</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="bg-white border-b border-gray-100 text-[10px] uppercase tracking-wider text-gray-500">
                    <th class="p-4 font-bold">Date & Time</th>
                    <th class="p-4 font-bold">Order ID / Package</th>
                    <th class="p-4 font-bold">Email / Client</th>
                    <th class="p-4 font-bold">Method</th>
                    <th class="p-4 font-bold">Type</th>
                    <th class="p-4 font-bold text-right">Amount</th>
                </tr>
            </thead>
            <tbody class="text-xs text-gray-700">
                @forelse($payments as $payment)
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <td class="p-4">
                            <span class="block font-bold text-gray-900">{{ $payment->created_at->format('d M Y') }}</span>
                            <span class="text-[10px] text-gray-400">{{ $payment->created_at->format('H:i') }} WIB</span>
                        </td>
                        <td class="p-4">
                            <span class="block font-bold text-gray-900">#EVL-{{ $payment->booking_id }}</span>
                            <span class="text-[10px] text-gray-500">{{ $payment->booking->package->name ?? 'Deleted Package' }}</span>
                        </td>
                        <td class="p-4">
                            <span class="block font-bold text-gray-900">{{ $payment->booking->user->email ?? 'Deleted Email' }}</span>
                            <span class="text-[10px] text-gray-500">{{ $payment->booking->user->name ?? 'Deleted User' }}</span>
                        </td>
                        <td class="p-4 uppercase tracking-wider text-[9px] font-bold text-gray-600">
                            {{ str_replace('_', ' ', $payment->payment_method) }}
                        </td>
                        <td class="p-4">
                            @if($payment->payment_type == 'dp')
                                <span class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-3 py-1 rounded-full text-[9px] uppercase tracking-widest font-bold">DP (50%)</span>
                            @else
                                <span class="bg-blue-50 border border-blue-200 text-blue-700 px-3 py-1 rounded-full text-[9px] uppercase tracking-widest font-bold">Full Payment</span>
                            @endif
                        </td>
                        <td class="p-4 text-right font-bold text-green-600">
                            Rp {{ number_format($payment->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-10 text-center text-gray-400 text-xs italic">
                            No successful transactions found for the selected period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="px-2">
    {{ $payments->links() }}
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        // Data Fallback (Kalau dari Controller belum ada, pakai data dummy ini dulu biar tampil)
        const labels = {!! json_encode($chartLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) !!};
        const data = {!! json_encode($chartData ?? [15000000, 20000000, 18000000, 30000000, 25000000, 40000000]) !!};

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (Rp)',
                    data: data,
                    borderColor: '#111827', // Hitam Elegan (gray-900)
                    backgroundColor: 'rgba(17, 24, 39, 0.05)', // Transparan Hitam
                    borderWidth: 2,
                    pointBackgroundColor: '#C9A66B', // Titik Emas (Sesuai tema)
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4 // Garis Melengkung Smooth
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }, // Sembunyikan legenda atas
                    tooltip: {
                        backgroundColor: '#111827',
                        padding: 12,
                        titleFont: { family: 'Montserrat', size: 10 },
                        bodyFont: { family: 'Montserrat', size: 12, weight: 'bold' },
                        callbacks: {
                            label: function(context) {
                                return ' Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6', drawBorder: false },
                        ticks: {
                            font: { family: 'Montserrat', size: 10 },
                            color: '#6b7280',
                            callback: function(value) {
                                // Format angka jadi 'Jt' (Juta) biar nggak kepanjangan
                                if(value >= 1000000) return (value / 1000000) + ' Jt';
                                if(value >= 1000) return (value / 1000) + ' Rb';
                                return value;
                            }
                        }
                    },
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: {
                            font: { family: 'Montserrat', size: 10 },
                            color: '#6b7280'
                        }
                    }
                }
            }
        });
    });
</script>
@endsection