<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f4f5; padding: 20px; color: #111827; margin: 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-top: 4px solid #10B981; padding: 40px 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid #f3f4f6; padding-bottom: 20px; }
        .logo { font-size: 24px; font-weight: 800; letter-spacing: 3px; text-transform: uppercase; color: #111; }
        .badge { display: inline-block; background-color: #D1FAE5; color: #065F46; padding: 6px 16px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; border-radius: 4px; margin-top: 15px; }
        .content { line-height: 1.8; font-size: 14px; color: #4b5563; }
        .box { background: #fafafa; border: 1px solid #eaeaea; padding: 25px; margin: 25px 0; border-radius: 6px; }
        
        table { width: 100%; border-collapse: collapse; }
        td { padding: 12px 0; border-bottom: 1px solid #f3f4f6; }
        tr:last-child td { border-bottom: none; }
        
        .label { color: #6b7280; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; width: 45%; }
        .value { font-weight: 600; text-align: right; color: #111827; }
        .total-row td { margin-top: 15px; padding-top: 20px; border-top: 2px dashed #e5e7eb; }
        .highlight { color: #10B981; font-size: 18px; font-weight: bold; }
        
        .btn { display: inline-block; width: 100%; box-sizing: border-box; padding: 16px 24px; background: #111; color: #fff; text-decoration: none; font-size: 12px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; margin-top: 10px; text-align: center; border-radius: 4px; }
        .footer { margin-top: 40px; font-size: 11px; color: #9ca3af; text-align: center; letter-spacing: 0.5px; line-height: 1.6; }
        
        .info-text { font-size: 12px; color: #4b5563; padding: 16px; background: #f8fafc; border-left: 3px solid #10B981; border-radius: 0 4px 4px 0; margin-bottom: 25px; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Everlast</div>
            <div class="badge">Pembayaran Berhasil</div>
        </div>
        
        <div class="content">
            <p>Halo <strong>Admin</strong>,</p>
            <p>Sistem baru saja menerima pembayaran otomatis dari Midtrans. Berikut adalah rincian transaksinya:</p>
            
            <div class="box">
                <table>
                    <tr>
                        <td class="label">Klien</td>
                        <td class="value">{{ $booking->user->name }} & {{ $booking->partner_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Paket Pilihan</td>
                        <td class="value">{{ $booking->package->name ?? 'Custom Package' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tgl. Acara Utama</td>
                        <td class="value">{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}</td>
                    </tr>
                    
                    @if($booking->prewed_date)
                    <tr>
                        <td class="label">Tgl. Prewedding</td>
                        <td class="value">{{ \Carbon\Carbon::parse($booking->prewed_date)->translatedFormat('d F Y') }}</td>
                    </tr>
                    @endif

                    <tr>
                        <td class="label">Pembayaran</td>
                        <td class="value">{{ $payment->payment_type == 'dp' ? 'Down Payment (50%)' : 'Pelunasan' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Metode</td>
                        <td class="value" style="text-transform: uppercase;">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td class="label" style="color: #111;">Nominal Masuk</td>
                        <td class="value highlight">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>

            <div class="info-text">
                Jadwal acara utama klien ini pada tanggal <strong>{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}</strong> 
                @if($booking->prewed_date)
                    dan sesi prewedding pada <strong>{{ \Carbon\Carbon::parse($booking->prewed_date)->translatedFormat('d F Y') }}</strong> 
                @endif
                otomatis berstatus <strong>{{ $payment->payment_type == 'dp' ? 'DP Paid' : 'Paid in Full' }}</strong>. Silakan atur penugasan tim (Freelancer) jika diperlukan.
            </div>

            <a href="{{ url('/admin/bookings/' . $booking->id) }}" class="btn">Kelola Booking Ini</a>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Everlast Moments Administration.<br>
            Pemberitahuan Sistem Otomatis
        </div>
    </div>
</body>
</html>