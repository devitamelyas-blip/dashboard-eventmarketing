<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pasca-Event Baru</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
        <h2 style="color: #166534; margin-top: 0;">Laporan Pasca-Event Telah Diisi</h2>
        <p>Halo Tim Marketing,</p>
        <p>PIC <strong>{{ $event->pic_name }}</strong> telah mengisi Laporan Pasca-Event untuk event berikut:</p>
        
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
                <td style="padding: 6px 0; font-weight: bold;">Peserta Aktual:</td>
                <td style="padding: 6px 0;">{{ $event->report?->actual_participants ?? '-' }} orang</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; font-weight: bold;">Lead Valid:</td>
                <td style="padding: 6px 0;">{{ $event->report?->valid_leads ?? '-' }} lead</td>
            </tr>
        </table>
    </div>

    <p>Silakan periksa dan verifikasi data event melalui dashboard:</p>
    <p>
        <a href="{{ config('app.url') }}/events" style="display: inline-block; background-color: #16a34a; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold;">
            Periksa & Verifikasi Event
        </a>
    </p>

    <p style="font-size: 12px; color: #6b7280; margin-top: 30px;">
        Email otomatis dikirim oleh Sistem Evaluasi Event Marketing — PT Kencana Maju Bersama.
    </p>
</body>
</html>
