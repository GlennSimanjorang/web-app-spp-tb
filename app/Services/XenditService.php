<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class XenditService
{
    protected string $secretKey;
    protected string $baseUrl = 'https://api.xendit.co';

    public function __construct()
    {
        $this->secretKey = config('services.xendit.secret_key');

        if (empty($this->secretKey)) {
            throw new Exception('Xendit Secret Key belum diatur di .env');
        }

        if (!str_starts_with($this->secretKey, 'xnd_development_') &&
            !str_starts_with($this->secretKey, 'xnd_production_')) {
            throw new Exception('Format Xendit Secret Key tidak valid. Harus dimulai dengan xnd_development_ atau xnd_production_');
        }
    }

    /**
     * Buat Invoice Xendit
     */
    public function createInvoice(
        string $externalId,
        string $payerEmail,
        string $description,
        float $amount,
        ?string $customerName = null
    ): array {
        $url = $this->baseUrl . '/v2/invoices';

        try {
            $payload = [
                'external_id' => $externalId,
                'payer_email' => $payerEmail,
                'description' => $description,
                'amount' => round($amount, 2),
                'invoice_duration' => 86400, // 24 jam
                'currency' => 'IDR',
                'should_send_email' => false,
                'success_redirect_url' => config('app.url') . '/payment/success',
                'failure_redirect_url' => config('app.url') . '/payment/failed',
            ];

            // Tambahkan customer name jika ada
            if ($customerName) {
                $payload['customer'] = ['given_name' => $customerName];
            }

            $response = Http::withBasicAuth($this->secretKey, '')
                ->timeout(30)
                ->post($url, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            $error = $response->json();
            $message = $error['message'] ?? $response->body();

            return [
                'success' => false,
                'message' => $message,
                'details' => $error
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke Xendit: ' . $e->getMessage(),
                'exception' => get_class($e),
                'trace' => app()->environment('local') ? $e->getTrace() : null
            ];
        }
    }

    /**
     * Ambil detail invoice dari Xendit
     */
    public function getInvoice(string $invoiceId): array
    {
        $url = $this->baseUrl . '/v2/invoices/' . $invoiceId;

        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->timeout(10)
                ->get($url);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            $error = $response->json();
            $message = $error['message'] ?? $response->body();

            return [
                'success' => false,
                'message' => $message
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal ambil data invoice: ' . $e->getMessage()
            ];
        }
    }
}
