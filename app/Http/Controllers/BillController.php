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
        $validator = Validator::make($request->all(),
        [
            'bill_number' => 'required|string|unique:bills',
            'month_year' => 'nullable|string|max:7',
            'due_date' => 'required|date',
            'amount' => 'required|numeric',
            'status' => 'required|in:unpaid,paid,overdue,cancelled',
            'payment_categories_id' => 'required|string|exists:payment_categories,sqlid',
            'student_id' => 'required|string|exists:students,sqlid',
            'academic_years_id' => 'required|string|exists:academic_years,sqlid'
        ]);

        $bill = Bill::create([
            'sqlid' => Str::uuid(),
            'bill_number' => $request->bill_number,
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
            'bill_number' => 'string|unique:bills,bill_number,' . $id . ',sqlid',
            'month_year' => 'string|max:7',
            'due_date' => 'date',
            'amount' => 'numeric',
            'status' => 'in:unpaid,paid,overdue,cancelled',
            'payment_categories_id' => 'exists:payment_categories,sqlid',
            'student_id' => 'exists:students,sqlid',
            'academic_years_id' => 'exists:academic_years,sqlid'
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
