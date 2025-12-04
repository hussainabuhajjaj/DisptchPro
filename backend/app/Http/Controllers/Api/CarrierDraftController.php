<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarrierDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CarrierDraftController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'draftId' => ['nullable', 'string'],
            'data' => ['required', 'array'],
        ]);

        $draft = DB::transaction(function () use ($request, $data) {
            if (!empty($data['draftId'])) {
                $draft = CarrierDraft::where(function ($query) use ($data) {
                    $query->where('reference_code', $data['draftId']);
                    if (is_numeric($data['draftId'])) {
                        $query->orWhere('id', $data['draftId']);
                    }
                })->firstOrFail();
                $draft->update([
                    'data' => $data['data'],
                    'user_id' => optional($request->user())->id,
                ]);
            } else {
                $draft = CarrierDraft::create([
                    'user_id' => optional($request->user())->id,
                    'reference_code' => $this->generateReferenceCode(),
                    'data' => $data['data'],
                    'status' => 'draft',
                ]);
            }
            return $draft;
        });

        return response()->json([
            'draftId' => $draft->reference_code,
            'updatedAt' => $draft->updated_at,
            'data' => $draft->data,
        ]);
    }

    public function show(Request $request, $id)
    {
        $draft = CarrierDraft::where(function ($query) use ($id) {
            $query->where('reference_code', $id);
            if (is_numeric($id)) {
                $query->orWhere('id', $id);
            }
        })
            ->with('documents')
            ->firstOrFail();

        return response()->json([
            'draftId' => $draft->reference_code,
            'updatedAt' => $draft->updated_at,
            'data' => $draft->data,
            'documents' => $draft->documents
                ->mapWithKeys(fn($doc) => [$doc->type => [
                    'status' => $doc->status,
                    'reviewerNote' => $doc->reviewer_note,
                    'fileName' => $doc->file_name,
                ]]),
        ]);
    }

    public function submit(Request $request, $id)
    {
        $data = $request->validate([
            'consent.signerName' => ['required', 'string'],
            'consent.signerTitle' => ['required', 'string'],
            'consent.signedAt' => ['required', 'date'],
        ]);

        $draft = CarrierDraft::where(function ($query) use ($id) {
            $query->where('reference_code', $id);
            if (is_numeric($id)) {
                $query->orWhere('id', $id);
            }
        })->firstOrFail();

        $draft->update([
            'consent' => $data['consent'],
            'status' => 'submitted',
        ]);

        return response()->json(['success' => true]);
    }

    protected function generateReferenceCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (CarrierDraft::where('reference_code', $code)->exists());

        return $code;
    }
}
