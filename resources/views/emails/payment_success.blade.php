<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil</title>
</head>

<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f9f9f9;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9f9f9; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #4CAF50, #2E7D32); padding: 30px 20px; text-align: center;">
                            <div style="font-size: 28px; font-weight: bold; color: white; margin-bottom: 8px;">✅ Pembayaran Berhasil!</div>
                            <div style="font-size: 16px; color: rgba(255,255,255,0.9);">Terima kasih atas kepercayaan Anda</div>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 30px 25px;">
                            <p style="font-size: 16px; color: #333333; line-height: 1.6; margin-bottom: 20px;">
                                Halo <strong>{{ $studentName }}</strong>,
                            </p>
                            <p style="font-size: 16px; color: #555555; line-height: 1.6; margin-bottom: 25px;">
                                Pembayaran tagihan Anda telah <strong>berhasil diproses</strong> dan statusnya kini <strong>LUNAS</strong>.
                            </p>

                            <!-- Detail Tagihan -->
                            <table width="100%" cellpadding="12" style="background-color: #f5f9f5; border-radius: 8px; margin-bottom: 25px;">
                                <tr>
                                    <td style="font-weight: bold; color: #2E7D32; width: 40%;">Tagihan</td>
                                    <td style="color: #333;">{{ $monthYear }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #2E7D32;">Nominal</td>
                                    <td style="color: #333;">Rp{{ number_format($amount, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #2E7D32;">Status</td>
                                    <td style="color: #333;">LUNAS ✅</td>
                                </tr>
                            </table>

                            <p style="font-size: 15px; color: #666666; line-height: 1.6;">
                                Jika Anda memiliki pertanyaan, jangan ragu untuk menghubungi admin sekolah.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f1f8f1; padding: 20px; text-align: center; border-top: 1px solid #e0f0e0;">
                            <p style="font-size: 14px; color: #555555; margin: 0;">
                                © {{ date('Y') }} {{ config('app.name', 'SPP Sekolah') }}. Semua hak dilindungi.
                            </p>
                            <p style="font-size: 12px; color: #888888; margin: 8px 0 0;">
                                Email ini dikirim secara otomatis. Mohon tidak membalas.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>