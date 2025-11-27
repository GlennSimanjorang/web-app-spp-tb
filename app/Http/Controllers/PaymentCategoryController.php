<?php

namespace App\Http\Controllers;

use App\Models\PaymentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Formatter; // pastikan helper Formatter dipakai

class PaymentCategoryController extends Controller
{
    /**
     * Daftar semua kategori pembayaran.
     */
    public function index()
    {
        $categories = PaymentCategory::orderBy('name')->get();

        return Formatter::apiResponse(200, 'Daftar kategori pembayaran', $categories);
    }

    /**
     * Detail satu kategori
     */
    public function show(PaymentCategory $paymentCategory = null)
    {
        if (!$paymentCategory) {
            return Formatter::apiResponse(404, 'Kategori pembayaran tidak ditemukan');
        }

        return Formatter::apiResponse(200, 'Detail kategori pembayaran', $paymentCategory);
    }

    /**
     * Buat kategori baru
     */
    public function store(Request $request)
    {
        Gate::authorize('create', PaymentCategory::class);

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('payment_categories'),
            ],
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:month,year,once',
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, 'Validasi gagal', $validator->errors());
        }

        $category = PaymentCategory::create($validator->validated());

        return Formatter::apiResponse(201, 'Kategori pembayaran berhasil dibuat', $category);
    }

    /**
     * Update kategori
     */
    public function update(Request $request, PaymentCategory $paymentCategory = null)
    {
        if (!$paymentCategory) {
            return Formatter::apiResponse(404, 'Kategori pembayaran tidak ditemukan');
        }

        Gate::authorize('update', $paymentCategory);

        $validator = Validator::make($request->all(), [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('payment_categories')->ignore($paymentCategory->id),
            ],
            'amount' => 'sometimes|required|numeric|min:0',
            'frequency' => 'sometimes|required|in:month,year,once',
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, 'Validasi gagal', $validator->errors());
        }

        $paymentCategory->update($validator->validated());

        return Formatter::apiResponse(200, 'Kategori pembayaran diperbarui', $paymentCategory);
    }

    /**
     * Hapus kategori
     */
    public function destroy(PaymentCategory $paymentCategory = null)
    {
        if (!$paymentCategory) {
            return Formatter::apiResponse(404, 'Kategori pembayaran tidak ditemukan');
        }

        Gate::authorize('delete', $paymentCategory);

        if ($paymentCategory->bills()->exists()) {
            return Formatter::apiResponse(400, 'Tidak bisa dihapus karena sudah digunakan di tagihan');
        }

        $paymentCategory->delete();

        return Formatter::apiResponse(200, 'Kategori pembayaran dihapus');
    }
}
