<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f4f5; padding: 20px; color: #111827; margin: 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-top: 4px solid #C9A66B; padding: 40px 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid #f3f4f6; padding-bottom: 20px; }
        .logo { font-size: 24px; font-weight: 800; letter-spacing: 3px; text-transform: uppercase; color: #111; }
        .badge { display: inline-block; background-color: #FEF3C7; color: #B45309; padding: 6px 16px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; border-radius: 4px; margin-top: 15px; }
        
        .content { line-height: 1.8; font-size: 14px; color: #4b5563; }
        .box { background: #fdfbf7; border: 1px solid #ebe6dd; padding: 25px; margin: 25px 0; border-radius: 6px; }
        
        .amount-wrapper { text-align: center; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px dashed #ebe6dd; }
        .amount-label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; margin: 0; }
        .amount { font-size: 28px; font-weight: 800; color: #111; margin: 5px 0 0 0; }
        
        /* Table layout biar stabil */
        table { width: 100%; border-collapse: collapse; }
        td { padding: 10px 0; }
        .label { color: #888; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; width: 45%; }
        .value { font-weight: 600; text-align: right; color: #111827; font-size: 13px; }
        .highlight { color: #C9A66B; font-weight: 700; text-transform: uppercase; font-size: 12px; }

        .btn { display: inline-block; width: 100%; box-sizing: border-box; padding: 16px 24px; background: #111; color: #fff; text-decoration: none; font-size: 12px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; margin-top: 10px; text-align: center; border-radius: 4px; transition: background 0.3s; }
        .footer { margin-top: 40px; font-size: 11px; color: #9ca3af; text-align: center; letter-spacing: 0.5px; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Everlast</div>
            <div class="badge">Pembayaran Berhasil</div>
        </div>
        
        <div class="content">
            <p>Halo, <strong>{{ $booking->user->name }}</strong>.</p>
            <p>Terima kasih! Kami telah menerima konfirmasi pembayaran <strong>{{ $type }}</strong> untuk pesanan Anda.</p>
            
            <div class="box">
                <div class="amount-wrapper">
                    <p class="amount-label">Nominal Diterima</p>
                    <div class="amount">Rp {{ number_format($amount, 0, ',', '.') }}</div>
                </div>
                
                <table>
                    <tr>
                        <td class="label">Paket Layanan</td>
                        <td class="value highlight">{{ $booking->package->name ?? 'Custom Package' }}</td>
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
                </table>
            </div>

            <p>Jadwal pemotretan Anda telah diamankan di kalender sistem kami. Tim Everlast akan segera menghubungi Anda untuk koordinasi lebih lanjut menjelang hari H.</p>
            
            <a href="{{ url('/pesanan/'}}" class="btn">Lihat Detail Pesanan</a>
            
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Everlast Moments. All rights reserved.<br>
            Email ini dikirim secara otomatis oleh sistem, mohon tidak membalas email ini.
        </div>
    </div>
</body>
</html>