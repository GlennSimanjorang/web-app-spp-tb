<?php

namespace App\Services;

use Exception;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;


class XenditService
{
    protected InvoiceApi $invoiceApi;

    public function __construct()
    {
        // Ambil Secret Key dari config
        $apiKey = config('services.xendit.secret_key');

        if (empty($apiKey)) {
            throw new Exception('Xendit Secret Key belum diatur. Pastikan .env berisi XENDIT_SECRET_KEY dan config sudah di-clear.');
        }

        // Validasi prefix key (development atau production)
        if (!str_starts_with($apiKey, 'xnd_development_') && !str_starts_with($apiKey, 'xnd_production_')) {
            throw new Exception('Xendit Secret Key format tidak valid. Pastikan menggunakan key dari dashboard Xendit.');
        }

        // Set API Key untuk semua request
        Configuration::setDefaultConfiguration(
            Configuration::getDefaultConfiguration()->setApiKey('Authorization', $apiKey)
        );

        $this->invoiceApi = new InvoiceApi();
    }

    public function createInvoice(
        string $external_id,
        string $payerEmail,
        string $description,
        float $amount
    ): array {
        try {
            $request = new CreateInvoiceRequest([
                'external_id'       => $external_id,
                'payer_email'       => $payerEmail,
                'description'       => $description,
                'amount'            => $amount,
                'invoice_duration'  => 86400, // 24 jam
                'should_send_email' => false,
                'currency'          => 'IDR',
            ]);

            $invoice = $this->invoiceApi->createInvoice($request);

            return [
                'success' => true,
                'data'    => $invoice
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal membuat invoice: ' . $e->getMessage()
            ];
        }
    }

    public function getInvoice(string $invoice_id): array
    {
        try {
            $invoice = $this->invoiceApi->getInvoiceById($invoice_id);
            return [
                'success' => true,
                'data'    => $invoice
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengambil data invoice: ' . $e->getMessage()
            ];
        }
    }
}
