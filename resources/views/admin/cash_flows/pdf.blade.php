<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Arus Kas</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #C9A66B; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 20px; letter-spacing: 1px; text-transform: uppercase; }
        .header p { margin: 5px 0 0 0; color: #666; font-size: 14px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f8f9fa; font-size: 11px; text-transform: uppercase; color: #555; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-green { color: #16a34a; font-weight: bold; }
        .text-red { color: #dc2626; font-weight: bold; }
        
        .summary-box { width: 300px; float: right; border: 1px solid #ddd; background: #fafafa; padding: 15px; }
        .summary-row { margin-bottom: 8px; font-size: 13px; }
        .summary-row span { float: right; font-weight: bold; }
        .total-row { border-top: 2px solid #333; padding-top: 8px; margin-top: 8px; font-size: 15px; font-weight: bold; }
        
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Everlast Project</h2>
        <p>Laporan Arus Kas (Cash Flow)</p>
        <p>Periode: <strong>{{ date('F', mktime(0, 0, 0, $month, 10)) }} {{ $year }}</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="15%">Tanggal</th>
                <th width="20%">Kategori</th>
                <th width="40%">Keterangan</th>
                <th width="25%" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cashFlows as $flow)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($flow->date)->format('d/m/Y') }}</td>
                    <td style="text-transform: uppercase; font-size: 10px;">{{ str_replace('_', ' ', $flow->category) }}</td>
                    <td>{{ $flow->description }}</td>
                    <td class="text-right {{ $flow->type == 'income' ? 'text-green' : 'text-red' }}">
                        {{ $flow->type == 'income' ? '+' : '-' }} {{ number_format($flow->amount, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 30px;">Tidak ada transaksi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="clearfix">
        <div class="summary-box">
            <div class="summary-row">Total Pemasukan: <span class="text-green">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span></div>
            <div class="summary-row">Total Pengeluaran: <span class="text-red">Rp {{ number_format($totalExpense, 0, ',', '.') }}</span></div>
            <div class="total-row">Saldo Bersih: <span style="{{ $netBalance < 0 ? 'color: #dc2626;' : 'color: #333;' }}">Rp {{ number_format($netBalance, 0, ',', '.') }}</span></div>
        </div>
    </div>

    <div style="margin-top: 50px; text-align: right; font-size: 11px; color: #888;">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d M Y H:i') }}
    </div>

</body>
</html>