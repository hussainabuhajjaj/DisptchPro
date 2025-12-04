<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarrierDocument;
use App\Models\CarrierDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function store(Request $request, $draftId)
    {
        $draft = CarrierDraft::where(function ($query) use ($draftId) {
            $query->where('reference_code', $draftId);
            if (is_numeric($draftId)) {
                $query->orWhere('id', $draftId);
            }
        })->firstOrFail();

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:w9,coi,insurance,factoringNoa'],
            'file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $path = $request->file('file')->store('carrier-documents', 'public');

        $doc = CarrierDocument::updateOrCreate(
            ['draft_id' => $draft->id, 'type' => $validated['type']],
            [
                'path' => $path,
                'file_name' => $request->file('file')->getClientOriginalName(),
                'status' => 'pending',
                'reviewer_note' => null,
            ]
        );

        return response()->json([
            'id' => $doc->id,
            'status' => $doc->status,
            'reviewerNote' => $doc->reviewer_note,
            'url' => Storage::disk('public')->url($doc->path),
        ]);
    }

    public function index(Request $request, $draftId)
    {
        $draft = CarrierDraft::where(function ($query) use ($draftId) {
            $query->where('reference_code', $draftId);
            if (is_numeric($draftId)) {
                $query->orWhere('id', $draftId);
            }
        })->firstOrFail();

        $base = collect([
            'w9' => ['status' => 'missing', 'reviewerNote' => null, 'fileName' => null],
            'coi' => ['status' => 'missing', 'reviewerNote' => null, 'fileName' => null],
            'insurance' => ['status' => 'missing', 'reviewerNote' => null, 'fileName' => null],
            'factoringNoa' => ['status' => 'missing', 'reviewerNote' => null, 'fileName' => null],
        ]);

        $documents = CarrierDocument::where('draft_id', $draft->id)->get()
            ->reduce(function ($carry, $doc) {
                $carry[$doc->type] = [
                    'status' => $doc->status,
                    'reviewerNote' => $doc->reviewer_note,
                    'fileName' => $doc->file_name,
                ];
                return $carry;
            }, $base->toArray());

        return response()->json([
            'documents' => $documents,
        ]);
    }

    public function review(Request $request, CarrierDocument $carrierDocument)
    {
        // Admin/staff only routes should be protected via route middleware.
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'reviewerNote' => ['nullable', 'string', 'max:2000'],
        ]);

        $carrierDocument->update([
            'status' => $validated['status'],
            'reviewer_note' => $validated['reviewerNote'] ?? null,
        ]);

        return response()->json([
            'id' => $carrierDocument->id,
            'status' => $carrierDocument->status,
            'reviewerNote' => $carrierDocument->reviewer_note,
            'fileName' => $carrierDocument->file_name,
        ]);
    }
}
