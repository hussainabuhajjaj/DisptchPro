<?php

namespace App\Filament\Resources\LoadResource\RelationManagers;

use App\Models\Document;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Events\TmsMapUpdated;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('original_name')
            ->columns([
                Tables\Columns\TextColumn::make('type')->label('Type'),
                Tables\Columns\TextColumn::make('original_name')->label('File'),
                Tables\Columns\TextColumn::make('mime_type')->label('MIME'),
                Tables\Columns\TextColumn::make('uploaded_at')->dateTime(),
            ])
            ->actions(array_filter([
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Document $record) => Storage::disk('public')->url($record->file_path))
                    ->openUrlInNewTab(),
                Action::make('regenerate')
                    ->label('Regenerate')
                    ->icon('heroicon-o-arrow-path')
                    ->url(fn (Document $record) => route('admin.documents.loads.pdf', [
                        'load' => $record->documentable_id,
                        'type' => explode(':', $record->type)[0] ?? 'general',
                        'template' => explode(':', $record->type)[1] ?? 'clean',
                        'force' => 1,
                    ]))
                    ->openUrlInNewTab(),
                $this->mediaPreviewAction(),
                ])
                )
            ->headerActions([
                Action::make('upload')
                    ->label('Upload')
                    ->schema([
                        Forms\Components\TextInput::make('type')->label('Type')->required(),
                        Forms\Components\FileUpload::make('file')
                            ->label('File')
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->disk('public')
                            ->directory('documents/loads')
                            ->preserveFilenames()
                            ->visibility('public'),
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        $record = $livewire->getOwnerRecord();
                        $upload = $data['file'];
                        if (is_string($upload)) {
                            $path = $upload;
                            $originalName = basename($upload);
                            $mime = Storage::disk('public')->mimeType($path);
                            $size = Storage::disk('public')->size($path);
                        } else {
                            $path = $upload->store('documents/loads', 'public');
                            $originalName = $upload->getClientOriginalName();
                            $mime = $upload->getClientMimeType();
                            $size = $upload->getSize();
                        }
                        Document::create([
                            'documentable_type' => \App\Models\Load::class,
                            'documentable_id' => $record->id,
                            'type' => $data['type'],
                            'file_path' => $path,
                            'original_name' => $originalName,
                            'mime_type' => $mime,
                            'size' => $size,
                            'uploaded_by' => auth()->id(),
                            'uploaded_at' => now(),
                        ]);
                        Cache::forget('tms-map-data');
                        broadcast(new TmsMapUpdated());
                    }),
            ]);
    }

    protected function mediaPreviewAction(): ?Action
    {
        if (!class_exists(\Hugomyb\FilamentMediaAction\Actions\MediaAction::class)) {
            return null;
        }
        return \Hugomyb\FilamentMediaAction\Actions\MediaAction::make('preview')
            ->label('Preview')
            ->media(fn (Document $record) => Storage::disk('public')->url($record->file_path))
            ->mediaType(\Hugomyb\FilamentMediaAction\Actions\MediaAction::TYPE_PDF)
            ->visible(fn (Document $record) => str_contains($record->mime_type ?? '', 'pdf'));
    }
}
