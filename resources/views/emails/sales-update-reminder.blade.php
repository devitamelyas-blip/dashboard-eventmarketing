<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pengingat Batas Waktu H+90</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #fefce8; border: 1px solid #fef08a; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
        <h2 style="color: #854d0e; margin-top: 0;">Pengingat Pembaruan Omzet H+90</h2>
        <p>Halo <strong>{{ $event->pic_name }}</strong>,</p>
        <p>Event dengan tujuan <strong>Sales</strong> berikut telah melewati batas waktu 90 hari pasca pelaksanaan:</p>
        
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
                <td style="padding: 6px 0; font-weight: bold;">Tanggal Event:</td>
                <td style="padding: 6px 0;">{{ $event->event_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; font-weight: bold;">Cabang:</td>
                <td style="padding: 6px 0;">{{ $event->branch->name }}</td>
            </tr>
        </table>
    </div>

    <p>Mohon segera mengunggah bukti invoice dan menginput omzet aktual hasil tindak lanjut event:</p>
    <p>
        <a href="{{ config('app.url') }}/events/{{ $event->id }}/sales-update" style="display: inline-block; background-color: #ca8a04; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold;">
            Isi Omzet H+90 Sekarang
        </a>
    </p>

    <p style="font-size: 12px; color: #6b7280; margin-top: 30px;">
        Email otomatis dikirim oleh Sistem Evaluasi Event Marketing — PT Kencana Maju Bersama.
    </p>
</body>
</html>
