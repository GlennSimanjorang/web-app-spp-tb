<?php

namespace App\Http\Controllers;

use App\Formatter;
use App\Models\PaymentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class PaymentCategoryController extends Controller
{
    public function index()
    {
        $categories = PaymentCategory::orderBy('name')->SimplePaginate(5);
        return Formatter::apiResponse(200, 'Data kategori pembayaran', $categories);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), 
            [
                'name' => 'required|string|max:100',
                'amount' => 'required|numeric|min:0',
                'frequency' => 'required|in:monthly,yearly,once',
                'description' => 'nullable|string',
                'is_active' => 'boolean'
            ]);
            
        if ($validator->fails()) {
            return Formatter::apiResponse(422, 'Validasi gagal', $validator->errors());
        }

        $category = PaymentCategory::create([
            'sqlid' => Str::uuid(),
            'name' => $request->name,
            'amount' => $request->amount,
            'frequency' => $request->frequency,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
        ]);

        return Formatter::apiResponse(201, 'Kategori pembayaran dibuat', $category);
    }

    public function show($id)
    {
        $category = PaymentCategory::find($id);
        if (!$category) return Formatter::apiResponse(404, 'Kategori pembayaran tidak ditemukan');
        return Formatter::apiResponse(200, 'Detail kategori', $category);
    }

    public function update(Request $request, $id)
    {
        $category = PaymentCategory::find($id);
        if (!$category) return Formatter::apiResponse(404, 'Kategori pembayaran Tidak ditemukan');

        $request->validate([
            'name' => 'string|max:100',
            'amount' => 'numeric|min:0',
            'frequency' => 'in:monthly,yearly,once',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $category->update($request->only(['name', 'amount', 'frequency', 'description', 'is_active']));

        return Formatter::apiResponse(200, 'Kategori pembayaran diperbarui', $category);
    }

    public function destroy($id)
    {
        $category = PaymentCategory::find($id);
        if (!$category) return Formatter::apiResponse(404, 'Tidak ditemukan kategori pembayaran');

        $category->delete();
        return Formatter::apiResponse(200, 'Kategori pembayaran dihapus');
    }
}
