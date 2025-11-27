<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Formatter;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Bill::with(['student', 'paymentCategory', 'academicYear']);

        // Filter hanya untuk parents
        if ($user->role === 'parents') {
            $query->whereHas('student.user', fn($q) => $q->where('id', $user->id));
        }

        if ($request->filled('search')) {
            $query->where('month_year', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $bills = $query->latest()->paginate(10);

        return Formatter::apiResponse(200, 'Data tagihan ditemukan.', $bills);
    }

    public function show(Bill $bill)
    {
        $this->authorize('view', $bill);

        $bill->load(['payments', 'student', 'paymentCategory']);

        return Formatter::apiResponse(200, 'Detail tagihan ditemukan.', $bill);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Bill::class);

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'payment_categories_id' => 'required|exists:payment_categories,id',
            'academic_years_id' => 'required|exists:academic_years,id',
            'month_year' => 'required|string|max:20',
            'due_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, 'Validasi gagal.', $validator->errors());
        }

        $bill = Bill::create([
            ...$validator->validated(),
            'total_paid' => 0,
            'status' => 'unpaid'
        ]);

        return Formatter::apiResponse(201, 'Tagihan berhasil dibuat.', $bill);
    }

    public function update(Request $request, Bill $bill)
    {
        $this->authorize('update', $bill);

        $validator = Validator::make($request->all(), [
            'month_year' => 'required|string|max:20',
            'due_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, 'Validasi gagal.', $validator->errors());
        }

        $bill->update($validator->validated());

        return Formatter::apiResponse(200, 'Tagihan diperbarui.', $bill);
    }

    public function destroy(Bill $bill)
    {
        $this->authorize('delete', $bill);

        $bill->delete();

        return Formatter::apiResponse(200, 'Tagihan dihapus.');
    }
}
