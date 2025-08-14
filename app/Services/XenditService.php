<?php

namespace App\Services;

use Exception;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;
use Xendit\VirtualAccount\CreateVirtualAccountRequest;
use Xendit\VirtualAccount\VirtualAccountApi;

class XenditService
{
    protected InvoiceApi $invoiceApi;
    protected VirtualAccountApi $vaApi;

    public function __construct()
    {
        // Set API key
        Configuration::setXenditKey(config('services.xendit.secret_key'));

        // Init API instance untuk invoice & virtual account
        $this->invoiceApi = new InvoiceApi();
        $this->vaApi = new VirtualAccountApi();
    }

    /**
     * Buat Virtual Account
     */
    public function createVirtualAccount(
        string $externalId,
        string $bankCode,
        string $name,
        float $amount,
        string $expirationDate
    ) {
        try {
            $request = new CreateVirtualAccountRequest([
                'external_id' => $externalId,
                'bank_code' => $bankCode,
                'name' => $name,
                'expected_amount' => $amount,
                'is_closed' => true,
                'expiration_date' => $expirationDate,
            ]);

            $response = $this->vaApi->createVirtualAccount($request);

            return [
                'success' => true,
                'data' => $response
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Buat Invoice (Multi Payment)
     */
    public function createInvoice(
        string $externalId,
        string $payerEmail,
        string $description,
        float $amount
    ) {
        try {
            $request = new CreateInvoiceRequest([
                'external_id' => $externalId,
                'payer_email' => $payerEmail,
                'description' => $description,
                'amount' => $amount,
                'invoice_duration' => 86400,
                'should_send_email' => false,
                'currency' => 'IDR',
            ]);

            $invoice = $this->invoiceApi->createInvoice($request);

            return [
                'success' => true,
                'data' => $invoice
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Cek status Virtual Account
     */
    public function getVirtualAccount(string $vaId)
    {
        try {
            $va = $this->vaApi->getVirtualAccountById($vaId);

            return [
                'success' => true,
                'data' => $va
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
