<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarrierDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarrierDraftController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'draftId' => ['nullable', 'integer', 'exists:carrier_drafts,id'],
            'data' => ['required', 'array'],
        ]);

        $draft = DB::transaction(function () use ($request, $data) {
            if (!empty($data['draftId'])) {
                $draft = CarrierDraft::where('id', $data['draftId'])
                    ->where('user_id', $request->user()->id)
                    ->firstOrFail();
                $draft->update(['data' => $data['data']]);
            } else {
                $draft = CarrierDraft::create([
                    'user_id' => $request->user()->id,
                    'data' => $data['data'],
                    'status' => 'draft',
                ]);
            }
            return $draft;
        });

        return response()->json([
            'draftId' => $draft->id,
            'updatedAt' => $draft->updated_at,
            'data' => $draft->data,
        ]);
    }

    public function show(Request $request, $id)
    {
        $draft = CarrierDraft::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with('documents')
            ->firstOrFail();

        return response()->json([
            'draftId' => $draft->id,
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

        $draft = CarrierDraft::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $draft->update([
            'consent' => $data['consent'],
            'status' => 'submitted',
        ]);

        return response()->json(['success' => true]);
    }
}
