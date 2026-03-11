<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f9f9f9; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-top: 4px solid #111; padding: 30px; text-align: center; }
        .logo { font-size: 24px; font-weight: bold; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 20px; }
        .content { text-align: left; line-height: 1.6; }
        .box { background: #fdfbf7; border: 1px solid #ebe6dd; padding: 15px; margin: 20px 0; text-align: center; }
        .amount { font-size: 24px; font-weight: bold; color: #111; margin: 10px 0; }
        .btn { display: inline-block; padding: 12px 24px; background: #111; color: #fff; text-decoration: none; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; margin-top: 20px; }
        .footer { margin-top: 30px; font-size: 10px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">Everlast</div>
        
        <div class="content">
            <p>Halo, <strong>{{ $booking->user->name }}</strong>.</p>
            <p>Terima kasih! Kami telah menerima pembayaran <strong>{{ $type }}</strong> untuk pesanan Anda.</p>
            
            <div class="box">
                <p style="margin: 0; font-size: 12px; color: #666; text-transform: uppercase;">Nominal Diterima</p>
                <div class="amount">Rp {{ number_format($amount, 0, ',', '.') }}</div>
                <p style="margin: 0; font-size: 12px; color: #666;">Paket: {{ $booking->package->name ?? 'Custom' }}</p>
            </div>

            <p>Jadwal pemotretan Anda ({{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}) telah kami konfirmasi dan amankan di kalender kami.</p>
            
            <div style="text-align: center;">
                <a href="http://127.0.0.1:8000/pesanan" class="btn">Lihat Detail Pesanan</a>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Everlast Moments. All rights reserved.<br>
            Email ini dikirim secara otomatis, mohon tidak membalas email ini.
        </div>
    </div>
</body>
</html>