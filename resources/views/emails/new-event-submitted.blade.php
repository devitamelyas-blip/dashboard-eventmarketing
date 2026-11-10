<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pengajuan Event Baru</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f9f9f9; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #e0e0e0;">
        <h2 style="color: #c53030; margin-top: 0;">Dashboard Event Marketing — Notifikasi Pengajuan Event</h2>
        <p>Halo Tim Marketing PT Kencana Maju Bersama,</p>
        <p>Telah diajukan event baru oleh PIC dengan rincian sebagai berikut:</p>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #eee; font-weight: bold; width: 40%;">Kode Event</td>
                <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $event->code }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;">Nama Event</td>
                <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $event->name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;">Cabang</td>
                <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $event->branch->name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;">Tanggal</td>
                <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $event->event_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;">Tujuan Utama</td>
                <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $event->goal->label() }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;">Biaya Proposal</td>
                <td style="padding: 8px; border-bottom: 1px solid #eee;">Rp {{ number_format($event->proposal_cost, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;">Budget Tim</td>
                <td style="padding: 8px; border-bottom: 1px solid #eee;">Rp {{ number_format($event->team_budget, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;">Status Verifikasi</td>
                <td style="padding: 8px; border-bottom: 1px solid #eee;"><strong style="color: #dd6b20;">{{ $event->verification_status->label() }}</strong></td>
            </tr>
        </table>

        <p>Silakan periksa dan verifikasi data pengajuan tersebut melalui dashboard event marketing.</p>
        <p style="margin-top: 30px; font-size: 12px; color: #888;">Email ini dikirim secara otomatis oleh Dashboard Event Marketing — PT Kencana Maju Bersama.</p>
    </div>
</body>
</html>
