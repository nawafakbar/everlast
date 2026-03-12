<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #INV-EVL-{{ $booking->id }}</title>
    <style>
        /* Reset & Base Setup */
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #111827; font-size: 13px; line-height: 1.5; margin: 0; padding: 0; }
        .container { max-width: 800px; margin: 0 auto; padding: 30px; }
        
        /* Header Section */
        .header-table { width: 100%; border-bottom: 2px solid #C9A66B; padding-bottom: 15px; margin-bottom: 30px; border-collapse: collapse; }
        .header-table td { vertical-align: middle; }
        .logo { font-size: 28px; font-weight: 800; letter-spacing: 4px; text-transform: uppercase; margin: 0; color: #111; }
        .tagline { margin: 5px 0 0; color: #C9A66B; font-size: 10px; letter-spacing: 2px; text-transform: uppercase; font-weight: bold; }
        .invoice-title { font-size: 24px; color: #111; text-transform: uppercase; letter-spacing: 2px; font-weight: bold; text-align: right; margin: 0; }
        
        /* Client & Booking Info */
        .info-table { width: 100%; margin-bottom: 40px; border-collapse: collapse; }
        .info-table td { vertical-align: top; width: 50%; }
        .info-title { font-size: 10px; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; font-weight: bold; }
        .info-detail { font-size: 14px; font-weight: bold; margin: 0 0 4px 0; color: #111; }
        .info-text { margin: 0 0 3px 0; color: #555; font-size: 12px; }
        .text-right { text-align: right; }
        
        /* Payment Items Table */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { border-bottom: 2px solid #111; border-top: 2px solid #111; padding: 12px 5px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #111; background-color: #fdfbf7; }
        .items-table td { padding: 15px 5px; border-bottom: 1px solid #eee; vertical-align: top; }
        .items-table th.text-right, .items-table td.text-right { text-align: right; }
        
        /* Totals Calculation */
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .summary-table td { padding: 8px 5px; }
        .summary-label { text-align: right; width: 75%; font-size: 12px; color: #555; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; }
        .summary-value { text-align: right; width: 25%; font-size: 13px; }
        .total-row td { font-size: 15px; font-weight: bold; border-top: 2px solid #111; padding-top: 15px; color: #111; }
        .balance-row td { font-size: 16px; font-weight: bold; color: #C9A66B; border-bottom: 2px solid #111; padding-bottom: 15px; }
        
        /* Status Badge */
        .status-badge { display: inline-block; padding: 6px 14px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 10px; border-radius: 2px; }
        .badge-paid { background: #10B981; color: #fff; }
        .badge-partial { background: #111; color: #C9A66B; border: 1px solid #C9A66B; }
        
        /* Footer */
        .footer { text-align: center; margin-top: 40px; font-size: 10px; color: #888; border-top: 1px solid #eee; padding-top: 20px; line-height: 1.8; }
    </style>
</head>
<body>
    <div class="container">
        
        <table class="header-table">
            <tr>
                <td>
                    <h1 class="logo">Everlast</h1>
                    <p class="tagline">Everlasting Moments</p>
                </td>
                <td>
                    <h2 class="invoice-title">Invoice</h2>
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <td>
                    <div class="info-title">Billed To:</div>
                    <p class="info-detail">{{ $booking->user->name ?? 'Client' }} & {{ $booking->partner_name }}</p>
                    <p class="info-text">{{ $booking->couple_address ?? '-' }}</p>
                    <p class="info-text">{{ $booking->user->email ?? '-' }}</p>
                </td>
                <td class="text-right">
                    <div class="info-title">Invoice Number:</div>
                    <p class="info-detail">INV-EVL-{{ $booking->id }}</p>
                    
                    <div class="info-title" style="margin-top: 15px;">Date of Event:</div>
                    <p class="info-text">
                        <strong>Utama:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}
                    </p>
                    
                    @if($booking->prewed_date)
                    <p class="info-text">
                        <strong>Prewedding:</strong> {{ \Carbon\Carbon::parse($booking->prewed_date)->translatedFormat('d F Y') }}
                    </p>
                    @endif
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
                        <strong style="color: #111;">{{ $booking->package->name ?? 'Custom Package' }}</strong><br>
                        <span style="font-size: 10px; color: #C9A66B; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px;">
                            {{ $payment->payment_type == 'dp' ? 'Down Payment (50%)' : 'Full Payment' }}
                        </span>
                    </td>
                    <td>{{ $payment->created_at->format('d M Y') }}</td>
                    <td style="text-transform: uppercase;">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                    <td class="text-right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $packagePrice = $booking->package->price ?? 0;
            $balanceDue = max(0, $packagePrice - $totalPaid);
        @endphp
        
        <table class="summary-table">
            <tr>
                <td class="summary-label">Total Harga Paket</td>
                <td class="summary-value">Rp {{ number_format($packagePrice, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td class="summary-label" style="color: #111;">TOTAL TERBAYAR</td>
                <td class="summary-value" style="color: #111;">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td>
            </tr>
            <tr class="balance-row">
                <td class="summary-label" style="color: #C9A66B;">SISA TAGIHAN</td>
                <td class="summary-value" style="color: #C9A66B;">Rp {{ number_format($balanceDue, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="text-right">
            @if($balanceDue <= 0)
                <div class="status-badge badge-paid"><i class="fas fa-check-circle"></i> PAID IN FULL</div>
            @else
                <div class="status-badge badge-partial">PARTIAL PAYMENT (DP)</div>
            @endif
        </div>

        <div class="footer">
            <p style="margin: 0 0 5px 0; font-weight: bold; color: #111;">Thank you for trusting Everlast Moments to capture your special day.</p>
            <p style="margin: 0;">If you have any questions concerning this invoice, contact us at hello@everlast.com</p>
        </div>
        
    </div>
</body>
</html>