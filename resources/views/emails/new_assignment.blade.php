<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f4f5; padding: 20px; color: #111827; margin: 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-top: 4px solid #111; padding: 40px 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid #f3f4f6; padding-bottom: 20px; }
        .logo { font-size: 24px; font-weight: 800; letter-spacing: 3px; text-transform: uppercase; color: #111; }
        .badge { display: inline-block; background-color: #111; color: #fff; padding: 6px 16px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; border-radius: 4px; margin-top: 15px; }
        
        .content { line-height: 1.8; font-size: 14px; color: #4b5563; }
        .box { background: #fdfbf7; border: 1px solid #ebe6dd; padding: 25px; margin: 25px 0; border-radius: 6px; }
        
        /* Table layout untuk kestabilan di semua aplikasi email */
        table { width: 100%; border-collapse: collapse; }
        td { padding: 12px 0; border-bottom: 1px solid #ebe6dd; vertical-align: top; }
        tr:last-child td { border-bottom: none; }
        
        .label { color: #888; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; width: 35%; padding-top: 14px; }
        .value { font-weight: 600; font-size: 14px; color: #111827; padding-top: 12px; }
        .highlight { color: #C9A66B; font-weight: 800; text-transform: uppercase; font-size: 13px; }

        .btn { display: inline-block; width: 100%; box-sizing: border-box; padding: 16px 24px; background: #111; color: #fff; text-decoration: none; font-size: 12px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; margin-top: 10px; text-align: center; border-radius: 4px; }
        .footer { margin-top: 40px; font-size: 11px; color: #9ca3af; text-align: center; letter-spacing: 0.5px; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Everlast</div>
            <div class="badge">Penugasan Baru</div>
        </div>
        
        <div class="content">
            <p>Halo, <strong>{{ $assignment->user->name }}</strong>.</p>
            <p>Admin Everlast baru saja menugaskan Anda untuk sebuah project baru. Berikut adalah ringkasan pekerjaannya:</p>
            
            @php
                // Logika pintar untuk menentukan detail acara berdasarkan jenis penugasan
                $isPrewed = $assignment->event_type === 'all_in_prewedding';
                
                $eventDate = $isPrewed ? $assignment->booking->prewed_date : $assignment->booking->booking_date;
                $eventTime = $isPrewed ? $assignment->booking->prewed_start_time : $assignment->booking->start_time;
                $eventEnd = $isPrewed ? $assignment->booking->prewed_end_time : $assignment->booking->end_time;
                
                $eventLoc = $assignment->booking->event_location;
                if ($isPrewed) {
                    $eventLoc = $assignment->booking->event_location_3 ?? ($assignment->booking->event_location_2 ?? 'Lokasi belum ditentukan');
                }
            @endphp

            <div class="box">
                <table>
                    <tr>
                        <td class="label">Klien</td>
                        <td class="value">{{ $assignment->booking->user->name ?? 'Client' }} & {{ $assignment->booking->partner_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Paket Acara</td>
                        <td class="value">{{ $assignment->booking->package->name ?? 'Custom Package' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tugas (Sesi)</td>
                        <td class="value highlight">{{ $assignment->task }} <br><span style="font-size: 10px; color: #111;">{{ ($assignment->event_type) }}</span></td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Bertugas</td>
                        <td class="value">{{ \Carbon\Carbon::parse($eventDate)->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Waktu Standby</td>
                        <td class="value">{{ \Carbon\Carbon::parse($eventTime)->format('H:i') }} - {{ \Carbon\Carbon::parse($eventEnd)->format('H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Lokasi</td>
                        <td class="value">{{ $eventLoc }}</td>
                    </tr>
                </table>
            </div>

            <p>Harap segera login ke Dashboard Anda untuk melakukan konfirmasi (Terima / Tolak) penugasan ini agar jadwal dapat segera diamankan oleh tim Admin.</p>
            
            <a href="{{ url('/freelance/schedules') }}" class="btn">Buka Dashboard</a>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Everlast Moments. All rights reserved.<br>
            Pemberitahuan Sistem Otomatis
        </div>
    </div>
</body>
</html>