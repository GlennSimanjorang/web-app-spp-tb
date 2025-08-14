<?php

namespace App\Http\Controllers;

use App\Formatter;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class BillController extends Controller
{
    public function index()
    {
        $bills = Bill::with(['student', 'category', 'academicYear'])->get();
        return Formatter::apiResponse(200, 'Daftar tagihan', $bills);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'month_year' => 'nullable|string|max:7',
                'due_date' => 'required|date',
                'amount' => 'required|numeric',
                'status' => 'required|in:unpaid,paid,overdue,cancelled',
                'payment_categories_id' => 'required|string|exists:payment_categories,id',
                'student_id' => 'required|string|exists:students,id',
                'academic_years_id' => 'required|string|exists:academic_years,id'
            ]
        );

        if ($validator->fails()) {
            return Formatter::apiResponse(422, 'Validasi gagal', $validator->errors());
        }

        // Ambil bill terakhir
        $lastBill = Bill::orderBy('bill_number', 'desc')->first();

        if ($lastBill && preg_match('/BILL-(\d+)/', $lastBill->bill_number, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        $billNumber = 'BILL-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        $bill = Bill::create([
            'id' => Str::uuid(),
            'bill_number' => $billNumber,
            'month_year' => $request->month_year,
            'due_date' => $request->due_date,
            'amount' => $request->amount,
            'status' => $request->status,
            'payment_categories_id' => $request->payment_categories_id,
            'student_id' => $request->student_id,
            'academic_years_id' => $request->academic_years_id,
        ]);

        return Formatter::apiResponse(201, 'Tagihan dibuat', $bill);
    }


    public function show($id)
    {
        $bill = Bill::with([
            'student.user',
            'category',
            'academicYear',
            'payments',
            'virtualAccount',
            'invoice'
        ])->find($id);

        if (!$bill) return Formatter::apiResponse(404, 'Tagihan tidak ditemukan');
        return Formatter::apiResponse(200, 'Detail tagihan', $bill);
    }

    public function update(Request $request, $id)
    {
        $bill = Bill::find($id);
        if (!$bill) return Formatter::apiResponse(404, 'Tidak ditemukan');

        $request->validate([
            'bill_number' => 'string|unique:bills,bill_number,' . $id . ',id',
            'month_year' => 'string|max:7',
            'due_date' => 'date',
            'amount' => 'numeric',
            'status' => 'in:unpaid,paid,overdue,cancelled',
            'payment_categories_id' => 'exists:payment_categories,id',
            'student_id' => 'exists:students,id',
            'academic_years_id' => 'exists:academic_years,id'
        ]);

        $bill->update($request->all());

        return Formatter::apiResponse(200, 'Tagihan diperbarui', $bill);
    }

    public function destroy($id)
    {
        $bill = Bill::find($id);
        if (!$bill) return Formatter::apiResponse(404, 'Tidak ditemukan');

        $bill->delete();
        return Formatter::apiResponse(200, 'Tagihan dihapus');
    }
}
