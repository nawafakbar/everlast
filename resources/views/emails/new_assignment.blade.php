<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f9f9f9; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-top: 4px solid #111; padding: 30px; text-align: center; }
        .logo { font-size: 24px; font-weight: bold; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 20px; }
        .content { text-align: left; line-height: 1.6; }
        .box { background: #fdfbf7; border: 1px solid #ebe6dd; padding: 15px; margin: 20px 0; }
        .title { font-size: 10px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; color: #888; margin-bottom: 5px; }
        .detail { font-size: 14px; font-weight: bold; color: #111; margin-bottom: 15px; }
        .btn { display: inline-block; padding: 12px 24px; background: #111; color: #fff; text-decoration: none; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; margin-top: 20px; text-align: center; width: 100%; box-sizing: border-box;}
        .footer { margin-top: 30px; font-size: 10px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">Everlast</div>
        
        <div class="content">
            <p>Halo, <strong>{{ $assignment->user->name }}</strong>.</p>
            <p>Admin Everlast baru saja menugaskan Anda untuk sebuah project baru. Berikut adalah detail pekerjaannya:</p>
            
            <div class="box">
                <div class="title">Klien</div>
                <div class="detail">{{ $assignment->booking->user->name ?? 'Client' }} & {{ $assignment->booking->partner_name }}</div>

                <div class="title">Tanggal Acara</div>
                <div class="detail">{{ \Carbon\Carbon::parse($assignment->booking->booking_date)->translatedFormat('d F Y') }}</div>

                <div class="title">Waktu & Lokasi</div>
                <div class="detail">
                    {{ \Carbon\Carbon::parse($assignment->booking->start_time)->format('H:i') }} WIB - Selesai<br>
                    {{ $assignment->booking->event_location }}
                </div>

                <div class="title">Tugas Anda (Role)</div>
                <div class="detail" style="color: #C9A66B;">{{ $assignment->task }}</div>
            </div>

            <p>Harap segera login ke Dashboard Anda untuk melakukan konfirmasi (Terima / Tolak) penugasan ini agar jadwal dapat segera diamankan.</p>
            
            <a href="{{ url('/freelance/schedules') }}" class="btn">Buka Dashboard</a>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Everlast Moments. All rights reserved.<br>
            Email otomatis, mohon tidak dibalas.
        </div>
    </div>
</body>
</html>