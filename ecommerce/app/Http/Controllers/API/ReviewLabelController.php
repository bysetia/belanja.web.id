<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReviewLabel;
use App\Helpers\ResponseFormatter;

class ReviewLabelController extends Controller
{
      public function index()
    {
        $labels = ReviewLabel::all();
        return ResponseFormatter::success($labels, 'Data label berhasil diambil.');
    }

    public function store(Request $request)
    {
        $label = $request->input('label');

        $newLabel = new ReviewLabel([
            'label' => $label,
        ]);
        $newLabel->save();

        return ResponseFormatter::success([
            'id' => $newLabel->id,
            'label' => $newLabel->label,
        ], 'Label berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $label = ReviewLabel::findOrFail($id);

        $label->update([
            'label' => $request->input('label'),
        ]);

         return ResponseFormatter::success([
            'id' => $newLabel->id,
            'label' => $newLabel->label,
        ], 'Label berhasil diperbaharui.');
    }

    public function destroy($id)
    {
        $label = ReviewLabel::findOrFail($id);
        $label->delete();

        return ResponseFormatter::success(null, 'Label berhasil dihapus.');
    }
}
