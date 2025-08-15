<?php

namespace App\Http\Controllers;
use App\Formatter;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;


class AcademicYearController extends Controller
{
    public function index()
    {
        $years = AcademicYear::orderBy('start_date', 'desc')->SimplePaginate(5);
        return Formatter::apiResponse(200, 'Data tahun ajaran', $years);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'school_year' => 'required|string|max:9',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean'
        ]);

        $year = AcademicYear::create([
            'id' => Str::uuid(),
            'school_year' => $request->school_year,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->is_active ?? false,
        ]);

        return Formatter::apiResponse(201, 'Tahun ajaran dibuat', $year);
    }

    public function show($id)
    {
        $year = AcademicYear::find($id);
        if (!$year) return Formatter::apiResponse(404, 'Tahun ajaran tidak ditemukan');
        return Formatter::apiResponse(200, 'Detail tahun ajaran', $year);
    }

    public function update(Request $request, $id)
    {
        $year = AcademicYear::find($id);
        if (!$year) return Formatter::apiResponse(404, 'Tidak ditemukan');

        $request->validate([
            'school_year' => 'string|max:9',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
            'is_active' => 'boolean'
        ]);

        $year->update($request->only(['school_year', 'start_date', 'end_date', 'is_active']));

        return Formatter::apiResponse(200, 'Tahun ajaran diperbarui', $year);
    }

    public function destroy($id)
    {
        $year = AcademicYear::find($id);
        if (!$year) return Formatter::apiResponse(404, 'Tidak ditemukan');

        $year->delete();
        return Formatter::apiResponse(200, 'Tahun ajaran dihapus');
    }
}
