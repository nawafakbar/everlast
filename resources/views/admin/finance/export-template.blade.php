<!DOCTYPE html>
<html>
<head>
    <title>Laporan Keuangan Everlast</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; color: #111; }
        .header p { margin: 5px 0; color: #666; font-style: italic; }
        
        .summary-box { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .summary-box td { padding: 10px; border: 1px solid #ddd; text-align: center; background-color: #f9f9f9; }
        .summary-box h3 { margin: 5px 0 0 0; font-size: 16px; color: #000; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table.data-table th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .badge { padding: 3px 6px; border-radius: 3px; font-size: 9px; text-transform: uppercase; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h2>EVERLAST MOMENTS</h2>
        <p>Financial Report Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    </div>

    <table class="summary-box">
        <tr>
            <td>
                Total Down Payment<br>
                <h3>Rp {{ number_format($totalDP, 0, ',', '.') }}</h3>
            </td>
            <td>
                Total Full Payment<br>
                <h3>Rp {{ number_format($totalFullPayment, 0, ',', '.') }}</h3>
            </td>
            <td>
                <strong>Total Revenue</strong><br>
                <h3>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
            </td>
            <td>
                <strong>Total Expenses</strong><br>
                <h3>Rp {{ number_format($totalExpenses, 0, ',', '.') }}</h3>
            </td>
            <td>
                <strong>Total Net Profit</strong><br>
                <h3>Rp {{ number_format($netProfit, 0, ',', '.') }}</h3>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th colspan="6" style="font-size: 18px; font-weight: bold; text-align: center; background-color: #111827; color: white; padding: 12px;">
                    EVERLAST - FINANCIAL REPORT
                </th>
            </tr>
            <tr>
                <th colspan="6" style="font-size: 12px; text-align: center; background-color: #f3f4f6; padding: 8px;">
                    Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                </th>
            </tr>
            <tr><th colspan="6"></th></tr>

            {{-- 👇 SUMMARY SECTION --}}
            <tr>
                <th colspan="3" style="background-color: #e5e7eb; padding: 8px; font-weight: bold;">FINANCIAL SUMMARY</th>
                <th colspan="3" style="background-color: #e5e7eb;"></th>
            </tr>
            <tr>
                <td colspan="2" style="padding: 6px; font-weight: bold;">Total Revenue</td>
                <td style="padding: 6px; text-align: right; color: green; font-weight: bold;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 6px;">├─ Down Payment</td>
                <td style="padding: 6px; text-align: right;">Rp {{ number_format($totalDP, 0, ',', '.') }}</td>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 6px;">└─ Full Payment</td>
                <td style="padding: 6px; text-align: right;">Rp {{ number_format($totalFullPayment, 0, ',', '.') }}</td>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 6px; font-weight: bold;">Total Expenses</td>
                <td style="padding: 6px; text-align: right; color: red; font-weight: bold;">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 6px; font-weight: bold; font-size: 14px; background-color: {{ $netProfit >= 0 ? '#d1fae5' : '#fef3c7' }};">
                    {{ $netProfit >= 0 ? 'NET PROFIT' : 'NET LOSS' }}
                </td>
                <td style="padding: 6px; text-align: right; font-weight: bold; font-size: 14px; background-color: {{ $netProfit >= 0 ? '#d1fae5' : '#fef3c7' }}; color: {{ $netProfit >= 0 ? 'green' : 'orange' }};">
                    Rp {{ number_format(abs($netProfit), 0, ',', '.') }}
                </td>
                <td colspan="3" style="background-color: {{ $netProfit >= 0 ? '#d1fae5' : '#fef3c7' }};"></td>
            </tr>
            <tr><th colspan="6"></th></tr>

            {{-- 👇 INCOME TRANSACTIONS TABLE --}}
            <tr>
                <th colspan="6" style="background-color: #10b981; color: white; padding: 8px; font-weight: bold;">INCOME TRANSACTIONS</th>
            </tr>
            <tr style="background-color: #f9fafb; font-weight: bold;">
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Date</th>
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Order ID</th>
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Client Name</th>
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Email</th>
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Type</th>
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td style="padding: 6px; border: 1px solid #e5e7eb;">{{ $payment->created_at->format('d M Y H:i') }}</td>
                <td style="padding: 6px; border: 1px solid #e5e7eb;">#EVL-{{ $payment->booking_id }}</td>
                <td style="padding: 6px; border: 1px solid #e5e7eb;">{{ $payment->booking->user->name ?? 'N/A' }}</td>
                <td style="padding: 6px; border: 1px solid #e5e7eb;">{{ $payment->booking->user->email ?? 'N/A' }}</td>
                <td style="padding: 6px; border: 1px solid #e5e7eb;">{{ strtoupper($payment->payment_type) }}</td>
                <td style="padding: 6px; border: 1px solid #e5e7eb; text-align: right;">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>

        {{-- 👇 EXPENSES TABLE (BARU!) --}}
        <tbody>
            <tr><th colspan="6"></th></tr>
            <tr>
                <th colspan="6" style="background-color: #ef4444; color: white; padding: 8px; font-weight: bold;">EXPENSE TRANSACTIONS</th>
            </tr>
            <tr style="background-color: #f9fafb; font-weight: bold;">
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Date</th>
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Category</th>
                <th colspan="3" style="padding: 8px; border: 1px solid #e5e7eb;">Description</th>
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Amount</th>
            </tr>
            @forelse($expenses as $expense)
            <tr>
                <td style="padding: 6px; border: 1px solid #e5e7eb;">{{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}</td>
                <td style="padding: 6px; border: 1px solid #e5e7eb;">{{ strtoupper($expense->category) }}</td>
                <td colspan="3" style="padding: 6px; border: 1px solid #e5e7eb;">{{ $expense->description }}</td>
                <td style="padding: 6px; border: 1px solid #e5e7eb; text-align: right; color: red;">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding: 12px; text-align: center; font-style: italic; color: gray;">No expenses recorded for this period</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- <table class="data-table">
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Tanggal</th>
                <th>Order ID</th>
                <th>Nama Klien</th>
                <th>Metode</th>
                <th>Tipe Pembayaran</th>
                <th class="text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $index => $payment)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $payment->created_at->format('d M Y H:i') }}</td>
                    <td>#EVL-{{ $payment->booking_id }}</td>
                    <td>{{ $payment->booking->user->name ?? '-' }}</td>
                    <td style="text-transform: uppercase;">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                    <td>
                        @if($payment->payment_type == 'dp')
                            DP (50%)
                        @else
                            FULL PAYMENT
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">Tidak ada transaksi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table> -->

</body>
</html>