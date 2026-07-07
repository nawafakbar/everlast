<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:30px;">
    <div style="max-width:500px; margin:0 auto; background:#ffffff; padding:30px; border-radius:4px;">
        <h2 style="color:#111; letter-spacing:1px;">EVERLAST.</h2>
        <hr style="border:none; border-top:1px solid #eee; margin:20px 0;">

        <p>Halo {{ $booking->user->name }},</p>

        <p>
            Booking Anda untuk acara bersama <strong>{{ $booking->partner_name }}</strong>
            pada tanggal <strong>{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}</strong>
            telah <strong style="color:#d9534f;">dibatalkan secara otomatis</strong>,
            karena belum ada pembayaran (DP) yang kami terima dalam 7 hari sejak booking dibuat.
        </p>

        <p>
            Jika Anda masih berminat menggunakan jasa kami, silakan melakukan booking ulang
            melalui website atau menghubungi admin kami secara langsung.
        </p>

        <p style="margin-top:30px;">Terima kasih,<br><strong>Tim Everlast</strong></p>
    </div>
</body>
</html>