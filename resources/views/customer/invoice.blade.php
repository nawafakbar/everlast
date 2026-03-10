<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #INV-EVL-{{ $booking->id }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #222; font-size: 13px; line-height: 1.5; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; letter-spacing: 3px; text-transform: uppercase; font-family: 'Times New Roman', serif; }
        .header p { margin: 5px 0 0; color: #777; font-size: 10px; letter-spacing: 2px; text-transform: uppercase; }
        
        .info-table { width: 100%; margin-bottom: 40px; }
        .info-table td { vertical-align: top; width: 50%; }
        .info-title { font-size: 10px; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .info-detail { font-size: 14px; font-weight: bold; margin: 0; }
        .text-right { text-align: right; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { border-bottom: 2px solid #000; padding: 10px 0; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #555; }
        .items-table td { padding: 15px 0; border-bottom: 1px solid #eee; }
        .items-table th.text-right, .items-table td.text-right { text-align: right; }
        
        .total-row td { font-size: 16px; font-weight: bold; border-top: 2px solid #000; border-bottom: none; padding-top: 15px; }
        
        .status-badge { display: inline-block; background: #000; color: #fff; padding: 4px 8px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-top: 10px; }
        
        .footer { text-align: center; margin-top: 50px; font-size: 10px; color: #888; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Everlast</h1>
            <p>Everlasting Moments</p>
        </div>

        <table class="info-table">
            <tr>
                <td>
                    <div class="info-title">Billed To:</div>
                    <p class="info-detail">{{ $booking->user->name ?? 'Client' }}</p>
                    <p style="margin: 3px 0; color: #555;">{{ $booking->couple_address ?? '-' }}</p>
                    <p style="margin: 0; color: #555;">{{ $booking->user->email ?? '-' }}</p>
                </td>
                <td class="text-right">
                    <div class="info-title">Invoice Number:</div>
                    <p class="info-detail">INV-EVL-{{ $booking->id }}</p>
                    <div class="info-title" style="margin-top: 15px;">Date of Event:</div>
                    <p style="margin: 0; color: #555;">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d F Y') }}</p>
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Package / Description</th>
                    <th>Payment Date</th>
                    <th>Method</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->payments as $payment)
                <tr>
                    <td>
                        <strong>{{ $booking->package->name ?? 'Custom Package' }}</strong><br>
                        <span style="font-size: 10px; color: #777; text-transform: uppercase; font-weight: bold;">
                            Tipe: {{ $payment->payment_type == 'dp' ? 'Down Payment (50%)' : 'Full Payment' }}
                        </span>
                    </td>
                    <td>{{ $payment->created_at->format('d M Y') }}</td>
                    <td style="text-transform: uppercase;">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                    <td class="text-right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                
                <tr class="total-row">
                    <td colspan="3" class="text-right" style="padding-right: 20px;">TOTAL PAID</td>
                    <td class="text-right">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div style="text-align: right;">
            @if($totalPaid >= ($booking->package->price ?? 0))
                <div class="status-badge">PAID IN FULL</div>
            @else
                <div class="status-badge" style="background: #f59e0b;">PARTIAL PAYMENT (DP)</div>
            @endif
        </div>

        <div class="footer">
            <p>Thank you for trusting Everlast Moments to capture your special day.</p>
            <p>If you have any questions concerning this invoice, contact us at hello@everlast.com</p>
        </div>
    </div>
</body>
</html>