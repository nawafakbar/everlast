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
        </tr>
    </table>

    <table class="data-table">
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
    </table>

</body>
</html>