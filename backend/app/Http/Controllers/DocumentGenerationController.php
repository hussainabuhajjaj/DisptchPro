<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Load;
use App\Models\Invoice;
use App\Models\Settlement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentGenerationController extends Controller
{
    public function loadPdf(Request $request, Load $load)
    {
        $type = $request->get('type', 'general');
        $template = $request->get('template', 'clean');
        $force = $request->boolean('force');
        $templates = [
            'clean' => 'documents.templates.clean',
            'rate-con' => 'documents.templates.rate-con',
            'invoice' => 'documents.templates.invoice',
            'bol' => 'documents.templates.bol',
            'pod' => 'documents.templates.pod',
            'modern' => 'documents.templates.modern',
        ];
        $viewName = $templates[$template] ?? $templates['clean'];
        // Ensure dompdf facade exists
        if (!class_exists(Pdf::class)) {
            abort(500, 'PDF engine not installed. Run: composer require barryvdh/laravel-dompdf');
        }

        $brand = [
            'logo' => $request->get('brand_logo') ?? config('app.logo_url'),
            'color' => $request->get('brand_color', config('app.brand_color', '#2563eb')),
            'font' => $request->get('brand_font', 'Arial, sans-serif'),
            'company' => $request->get('company_name'),
            'address' => $request->get('company_address'),
        ];

        $view = View::make($viewName, [
            'load' => $load->load('client', 'carrier', 'driver', 'stops'),
            'type' => $type,
            'template' => $template,
            'brand' => $brand,
            'title' => strtoupper($type) . ' — ' . $load->load_number,
            'invoice_number' => $request->get('invoice_number'),
            'due_date' => $request->get('due_date'),
            'payment_terms' => $request->get('payment_terms'),
            'broker_ref' => $request->get('broker_ref'),
            'equipment' => $request->get('equipment'),
            'contact_name' => $request->get('contact_name'),
            'contact_phone' => $request->get('contact_phone'),
            'show_signatures' => $request->boolean('show_signatures'),
            'delivery_date' => $request->get('delivery_date'),
            'recipient_name' => $request->get('recipient_name'),
        ])->render();

        $fileName = Str::slug($load->load_number . '-' . $type . '-' . $template) . '.pdf';
        $path = "documents/loads/{$fileName}";

        // Try to reuse cached document for this template/type unless force
        $existingQuery = Document::where('documentable_type', Load::class)
            ->where('documentable_id', $load->id)
            ->where('type', $type . ':' . $template);

        if ($force) {
            $existingDocs = $existingQuery->get();
            foreach ($existingDocs as $doc) {
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
                $doc->delete();
            }
        } else {
            $existing = $existingQuery->latest('uploaded_at')->first();
            if ($existing && Storage::disk('public')->exists($existing->file_path ?? '')) {
                return $this->streamFromPath($existing->file_path, $existing->original_name ?? $fileName);
            }
        }

        $pdf = Pdf::loadHTML($view)->setPaper('a4');
        Storage::disk('public')->put($path, $pdf->output());

        Document::create([
            'documentable_type' => Load::class,
            'documentable_id' => $load->id,
            'type' => $type . ':' . $template,
            'file_path' => $path,
            'original_name' => $fileName,
            'mime_type' => 'application/pdf',
            'size' => Storage::disk('public')->size($path),
            'uploaded_by' => $request->user()?->id,
            'uploaded_at' => now(),
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName, ['Content-Type' => 'application/pdf']);
    }

    public function invoicePdf(Request $request, Invoice $invoice)
    {
        $template = $request->get('template', 'invoice-model');
        $templates = [
            'invoice-model' => 'documents.templates.invoice-model',
            'invoice-modern' => 'documents.templates.invoice-modern',
        ];
        $viewName = $templates[$template] ?? $templates['invoice-model'];

        if (!class_exists(Pdf::class)) {
            abort(500, 'PDF engine not installed. Run: composer require barryvdh/laravel-dompdf');
        }

        $brand = [
            'logo' => $request->get('brand_logo') ?? config('app.logo_url'),
            'color' => $request->get('brand_color', config('app.brand_color', '#2563eb')),
            'font' => $request->get('brand_font', 'Arial, sans-serif'),
            'company' => $request->get('company_name'),
            'address' => $request->get('company_address'),
        ];

        $invoice->load('client', 'items', 'payments', 'loadRelation');
        $view = View::make($viewName, [
            'invoice' => $invoice,
            'brand' => $brand,
            'template' => $template,
            'title' => 'Invoice ' . ($invoice->invoice_number ?? ('INV-' . $invoice->id)),
        ])->render();

        $fileName = Str::slug('invoice-' . ($invoice->invoice_number ?? $invoice->id) . '-' . $template) . '.pdf';
        $path = "documents/invoices/{$fileName}";

        $existing = Document::where('documentable_type', Invoice::class)
            ->where('documentable_id', $invoice->id)
            ->where('type', 'invoice:' . $template)
            ->latest('uploaded_at')
            ->first();

        if ($existing && !$request->boolean('force') && Storage::disk('public')->exists($existing->file_path)) {
            return $this->streamFromPath($existing->file_path, $existing->original_name ?? $fileName);
        }

        $pdf = Pdf::loadHTML($view)->setPaper('a4');
        Storage::disk('public')->put($path, $pdf->output());

        Document::updateOrCreate(
            [
                'documentable_type' => Invoice::class,
                'documentable_id' => $invoice->id,
                'type' => 'invoice:' . $template,
            ],
            [
                'file_path' => $path,
                'original_name' => $fileName,
                'mime_type' => 'application/pdf',
                'size' => Storage::disk('public')->size($path),
                'uploaded_by' => $request->user()?->id,
                'uploaded_at' => now(),
            ]
        );

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName, ['Content-Type' => 'application/pdf']);
    }

    public function settlementPdf(Request $request, Settlement $settlement)
    {
        $template = $request->get('template', 'settlement');
        $templates = [
            'settlement' => 'documents.templates.settlement',
            'settlement-modern' => 'documents.templates.settlement-modern',
        ];
        $viewName = $templates[$template] ?? $templates['settlement'];

        if (!class_exists(Pdf::class)) {
            abort(500, 'PDF engine not installed. Run: composer require barryvdh/laravel-dompdf');
        }

        $brand = [
            'logo' => $request->get('brand_logo') ?? config('app.logo_url'),
            'color' => $request->get('brand_color', config('app.brand_color', '#2563eb')),
            'font' => $request->get('brand_font', 'Arial, sans-serif'),
            'company' => $request->get('company_name'),
            'address' => $request->get('company_address'),
        ];

        $settlement->load('items', 'payments', 'loadRelation');
        $view = View::make($viewName, [
            'settlement' => $settlement,
            'brand' => $brand,
            'template' => $template,
            'title' => 'Settlement ' . $settlement->id,
        ])->render();

        $fileName = Str::slug('settlement-' . $settlement->id . '-' . $template) . '.pdf';
        $path = "documents/settlements/{$fileName}";

        $existing = Document::where('documentable_type', Settlement::class)
            ->where('documentable_id', $settlement->id)
            ->where('type', 'settlement:' . $template)
            ->latest('uploaded_at')
            ->first();

        if ($existing && !$request->boolean('force') && Storage::disk('public')->exists($existing->file_path)) {
            return $this->streamFromPath($existing->file_path, $existing->original_name ?? $fileName);
        }

        $pdf = Pdf::loadHTML($view)->setPaper('a4');
        Storage::disk('public')->put($path, $pdf->output());

        Document::updateOrCreate(
            [
                'documentable_type' => Settlement::class,
                'documentable_id' => $settlement->id,
                'type' => 'settlement:' . $template,
            ],
            [
                'file_path' => $path,
                'original_name' => $fileName,
                'mime_type' => 'application/pdf',
                'size' => Storage::disk('public')->size($path),
                'uploaded_by' => $request->user()?->id,
                'uploaded_at' => now(),
            ]
        );

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName, ['Content-Type' => 'application/pdf']);
    }

    protected function streamFromPath(string $path, string $downloadName): StreamedResponse
    {
        return response()->streamDownload(function () use ($path) {
            echo Storage::disk('public')->get($path);
        }, $downloadName, ['Content-Type' => 'application/pdf']);
    }
}
