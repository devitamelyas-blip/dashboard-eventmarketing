<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pemberitahuan Koreksi Event</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
        <h2 style="color: #991b1b; margin-top: 0;">Pemberitahuan Perlu Koreksi Data Event</h2>
        <p>Halo <strong>{{ $event->pic_name }}</strong>,</p>
        <p>Tim Marketing telah memeriksa data event berikut dan menemukan bagian yang memerlukan koreksi atau klarifikasi lebih lanjut:</p>
        
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <tr>
                <td style="padding: 6px 0; font-weight: bold; width: 35%;">Kode Event:</td>
                <td style="padding: 6px 0;">{{ $event->code }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; font-weight: bold;">Nama Event:</td>
                <td style="padding: 6px 0;">{{ $event->name }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; font-weight: bold;">Cabang:</td>
                <td style="padding: 6px 0;">{{ $event->branch->name }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; font-weight: bold;">Catatan / Alasan:</td>
                <td style="padding: 6px 0; color: #b91c1c; font-weight: bold;">{{ $reason }}</td>
            </tr>
        </table>
    </div>

    <p>Silakan masuk ke sistem untuk memperbaiki laporan atau data yang bersangkutan:</p>
    <p>
        <a href="{{ config('app.url') }}/events" style="display: inline-block; background-color: #dc2626; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold;">
            Buka Halaman Event
        </a>
    </p>

    <p style="font-size: 12px; color: #6b7280; margin-top: 30px;">
        Email otomatis dikirim oleh Sistem Evaluasi Event Marketing — PT Kencana Maju Bersama.
    </p>
</body>
</html>
