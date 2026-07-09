<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; background:#f4f4f4; padding:30px;">
    <div style="max-width:550px; margin:0 auto; background:#ffffff; padding:30px; border-radius:4px;">
        <h2 style="color:#111; letter-spacing:1px;">EVERLAST.</h2>
        <p style="color:#d9534f; font-weight:bold; text-transform:uppercase; font-size:12px; letter-spacing:1px;">
            🔴 Pembatalan Booking dari Client
        </p>
        <hr style="border:none; border-top:1px solid #eee; margin:20px 0;">

        <table style="width:100%; font-size:13px; color:#333; border-collapse:collapse;">
            <tr>
                <td style="padding:6px 0; width:150px; color:#888;">Client</td>
                <td style="padding:6px 0;"><strong>{{ $booking->user->name }}</strong></td>
            </tr>
            <tr>
                <td style="padding:6px 0; color:#888;">Partner</td>
                <td style="padding:6px 0;">{{ $booking->partner_name }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0; color:#888;">Paket</td>
                <td style="padding:6px 0;">{{ $booking->package->name ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0; color:#888;">Tanggal Acara</td>
                <td style="padding:6px 0;">{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}</td>
            </tr>
        </table>

        <div style="background:#FDFBF7; border:1px solid #EBE6DD; padding:15px; margin:20px 0; border-radius:4px;">
            <p style="margin:0 0 5px 0; font-size:11px; text-transform:uppercase; color:#888; font-weight:bold;">Alasan Pembatalan</p>
            <p style="margin:0; font-size:13px; color:#333;">{{ $reason }}</p>
        </div>

        <table style="width:100%; font-size:13px; color:#333; border-collapse:collapse;">
            <tr>
                <td style="padding:6px 0; width:150px; color:#888;">Total Sudah Dibayar</td>
                <td style="padding:6px 0;"><strong>Rp {{ number_format($totalPaid, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td style="padding:6px 0; color:#888;">Bukti Pembayaran</td>
                <td style="padding:6px 0;">
                    @if($proofUrl)
                        <a href="{{ $proofUrl }}" target="_blank" style="color:#2563eb;">Lihat Bukti</a>
                    @else
                        Tidak ada bukti manual (via Midtrans / belum ada payment)
                    @endif
                </td>
            </tr>
        </table>

        <div style="background:#f0fdf4; border:1px solid #bbf7d0; padding:15px; margin:20px 0; border-radius:4px;">
            <p style="margin:0 0 8px 0; font-size:11px; text-transform:uppercase; color:#166534; font-weight:bold;">Data Rekening Pengembalian Dana</p>
            <p style="margin:0; font-size:13px; color:#333;">
                Bank: <strong>{{ $bankName }}</strong><br>
                No. Rek: <strong>{{ $accountNumber }}</strong><br>
                A/N: <strong>{{ $accountHolder }}</strong>
            </p>
        </div>

        <p style="font-size:12px; color:#888; margin-top:25px;">
            Segera proses pengembalian dana ke rekening di atas dan konfirmasi ke client via WhatsApp.
        </p>
    </div>
</body>
</html>