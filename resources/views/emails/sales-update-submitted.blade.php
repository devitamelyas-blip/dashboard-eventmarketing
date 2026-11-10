<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Update Omzet H+90 Masuk</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
        <h2 style="color: #1e40af; margin-top: 0;">Pembaruan Omzet H+90 Disubmit</h2>
        <p>Halo Tim Marketing,</p>
        <p>PIC <strong>{{ $event->pic_name }}</strong> telah memperbarui data omzet H+90 beserta bukti invoice untuk event:</p>
        
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
                <td style="padding: 6px 0; font-weight: bold;">Omzet Dilaporkan:</td>
                <td style="padding: 6px 0; font-weight: bold; color: #1e3a8a;">
                    Rp {{ number_format((float) ($event->salesUpdate?->revenue ?? 0), 0, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

    <p>Silakan periksa bukti invoice dan lakukan verifikasi akhir event:</p>
    <p>
        <a href="{{ config('app.url') }}/events" style="display: inline-block; background-color: #2563eb; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold;">
            Tinjau Invoice & Verifikasi
        </a>
    </p>

    <p style="font-size: 12px; color: #6b7280; margin-top: 30px;">
        Email otomatis dikirim oleh Sistem Evaluasi Event Marketing — PT Kencana Maju Bersama.
    </p>
</body>
</html>
