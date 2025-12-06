<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PipelineFlowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage crm') || $this->user()?->hasAnyRole(['admin', 'staff']);
    }

    public function rules(): array
    {
        return [
            'nodes' => 'required|array',
            'nodes.*.id' => 'required|integer|exists:pipeline_stages,id',
            'nodes.*.x' => 'nullable|numeric',
            'nodes.*.y' => 'nullable|numeric',
            'nodes.*.position' => 'nullable|integer',
            'edges' => 'array',
            'edges.*.id' => 'nullable|integer|exists:pipeline_transitions,id',
            'edges.*.from' => 'required|integer|exists:pipeline_stages,id',
            'edges.*.to' => 'required|integer|exists:pipeline_stages,id',
            'edges.*.label' => 'nullable|string|max:255',
            'edges.*.actions' => 'nullable|array',
            'prune_missing' => 'sometimes|boolean',
        ];
    }
}
