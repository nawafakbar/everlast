<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f5; padding: 20px; color: #111827; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-top: 4px solid #10B981; padding: 30px; border-radius: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { text-align: center; margin-bottom: 25px; }
        .logo { font-size: 20px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; color: #111; }
        .badge { display: inline-block; background-color: #D1FAE5; color: #065F46; padding: 4px 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; border-radius: 20px; margin-top: 10px; }
        .content { line-height: 1.6; font-size: 14px; }
        .box { background: #f9fafb; border: 1px solid #e5e7eb; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .row { display: flex; justify-content: space-between; border-bottom: 1px solid #f3f4f6; padding: 8px 0; }
        .row:last-child { border-bottom: none; }
        .label { color: #6b7280; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .value { font-weight: bold; text-align: right; }
        .btn { display: block; padding: 12px 24px; background: #111; color: #fff; text-decoration: none; font-size: 12px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; margin-top: 25px; text-align: center; border-radius: 2px; }
        .footer { margin-top: 30px; font-size: 11px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Everlast System</div>
            <div class="badge">Pembayaran Berhasil</div>
        </div>
        
        <div class="content">
            <p>Halo <strong>Admin</strong>,</p>
            <p>Sistem baru saja menerima pembayaran otomatis dari Midtrans. Berikut adalah rincian transaksinya:</p>
            
            <div class="box">
                <div class="row">
                    <span class="label">Nama Klien</span>
                    <span class="value">{{ $booking->user->name }}</span>
                </div>
                <div class="row">
                    <span class="label">Jenis Pembayaran</span>
                    <span class="value">{{ $payment->payment_type == 'dp' ? 'Down Payment (50%)' : 'Pelunasan' }}</span>
                </div>
                <div class="row">
                    <span class="label">Metode</span>
                    <span class="value" style="text-transform: uppercase;">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                </div>
                <div class="row" style="margin-top: 10px; padding-top: 15px; border-top: 2px dashed #e5e7eb;">
                    <span class="label" style="color: #111;">Nominal Masuk</span>
                    <span class="value" style="font-size: 18px; color: #10B981;">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <p style="font-size: 12px; color: #6b7280;">Jadwal acara untuk klien ini pada tanggal <strong>{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}</strong> otomatis berstatus {{ $payment->payment_type == 'dp' ? 'DP Paid' : 'Paid in Full' }}. Silakan atur penugasan tim (Freelancer) jika diperlukan.</p>
            
            <a href="http://127.0.0.1:8000/admin/bookings/{{ $booking->id }}" class="btn">Kelola Booking Ini</a>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Everlast Moments Administration.
        </div>
    </div>
</body>
</html>