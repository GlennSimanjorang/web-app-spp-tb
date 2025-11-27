<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\PaymentReport;

class PaymentObserver
{
    public function created(Payment $payment)
    {
        if ($payment->status === 'success') {
            $this->createOrUpdateReport($payment);
        }
    }

    public function updated(Payment $payment)
    {
        if ($payment->isDirty('status') && $payment->status === 'success') {
            $this->createOrUpdateReport($payment);
        }
    }

    private function createOrUpdateReport(Payment $payment)
    {
        $bill = $payment->bill;

        if (!$bill || !$bill->academicYear || !$bill->paymentCategory) {
            \Log::warning('Cannot create report: missing required relations', [
                'payment_id' => $payment->id,
                'bill_id' => $bill?->id,
                'has_academic_year' => $bill?->academicYear ? 'yes' : 'no',
                'has_payment_category' => $bill?->paymentCategory ? 'yes' : 'no',
            ]);
            return;
        }

        $report = PaymentReport::where('bills_id', $bill->id)->first();

        if ($report) {
            $report->update([
                'total_amount' => $report->total_amount + $payment->amount_paid,
                'total_transactions' => $report->total_transactions + 1,
                'report_date' => now(),
            ]);
        } else {
            PaymentReport::create([
                'report_payment' => 'Lunas',
                'report_date' => now(),
                'notes' => 'Pembayaran berhasil',
                'bills_id' => $bill->id,
                'total_amount' => $payment->amount_paid,
                'total_transactions' => 1,
                'academic_year_id' => $bill->academicYear->id,
                'payment_category_id' => $bill->paymentCategory->id,
            ]);
        }
    }
}
