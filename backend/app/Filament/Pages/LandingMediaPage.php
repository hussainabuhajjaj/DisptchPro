<?php

namespace App\Filament\Pages;

use App\Models\Media;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Concerns\HasUnsavedDataChangesAlert;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 *
 * @property-read Schema $form
 */
class LandingMediaPage extends Page
{
    use CanUseDatabaseTransactions;
    use HasUnsavedDataChangesAlert;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-photo';

    protected static \UnitEnum|string|null $navigationGroup = 'Media Library';

    protected static ?string $navigationLabel = 'Landing Images';

    protected static ?string $title = 'Landing Images';

    public ?array $data = [];

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $media = Media::firstOrCreate(
            ['id' => 1],
            [
                'hero_image_url' => null,
                'why_choose_us_image_url' => null,
                'for_shippers_image_url' => null,
                'for_brokers_image_url' => null,
                'testimonial_avatar_1_url' => null,
                'testimonial_avatar_2_url' => null,
                'testimonial_avatar_3_url' => null,
            ],
        );

        $this->form->fill($media->toArray());

        $this->callHook('afterFill');
    }

    public function save(): void
    {
        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

        $this->callHook('beforeSave');

        $media = Media::firstOrCreate(['id' => 1]);
        $media->fill($data);
        $media->hero_image_meta = $this->buildImageMeta($data['hero_image_url'] ?? null);
        $media->why_choose_us_image_meta = $this->buildImageMeta($data['why_choose_us_image_url'] ?? null);
        $media->for_shippers_image_meta = $this->buildImageMeta($data['for_shippers_image_url'] ?? null);
        $media->for_brokers_image_meta = $this->buildImageMeta($data['for_brokers_image_url'] ?? null);
        $media->testimonial_avatar_1_meta = $this->buildImageMeta($data['testimonial_avatar_1_url'] ?? null);
        $media->testimonial_avatar_2_meta = $this->buildImageMeta($data['testimonial_avatar_2_url'] ?? null);
        $media->testimonial_avatar_3_meta = $this->buildImageMeta($data['testimonial_avatar_3_url'] ?? null);
        $media->save();

            $this->callHook('afterSave');
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction()
                ? $this->rollBackDatabaseTransaction()
                : $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->commitDatabaseTransaction();

        $this->rememberData();

        Notification::make()
            ->title('Landing images updated')
            ->success()
            ->send();

    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Hero & Highlights')
                ->columns(2)
                ->schema([
                    FileUpload::make('hero_image_url')
                        ->label('Hero background')
                        ->image()
                        ->disk('public')
                        ->directory('media/landing')
                        ->visibility('public')
                        ->maxSize(8096 ),
                    FileUpload::make('why_choose_us_image_url')
                        ->label('Why choose us')
                        ->image()
                        ->disk('public')
                        ->directory('media/landing')
                        ->visibility('public')
                        ->maxSize(8096 ),
                ]),
            Section::make('Audience Sections')
                ->columns(2)
                ->schema([
                    FileUpload::make('for_shippers_image_url')
                        ->label('For shippers')
                        ->image()
                        ->disk('public')
                        ->directory('media/landing')
                        ->visibility('public')
                        ->maxSize(8096 ),
                    FileUpload::make('for_brokers_image_url')
                        ->label('For brokers')
                        ->image()
                        ->disk('public')
                        ->directory('media/landing')
                        ->visibility('public')
                        ->maxSize(8096 ),
                ]),
            Section::make('Testimonials')
                ->columns(3)
                ->schema([
                    FileUpload::make('testimonial_avatar_1_url')
                        ->label('Testimonial avatar 1')
                        ->image()
                        ->circleCropper()
                        ->disk('public')
                        ->directory('media/landing/avatars')
                        ->visibility('public')
                        ->maxSize(2048),
                    FileUpload::make('testimonial_avatar_2_url')
                        ->label('Testimonial avatar 2')
                        ->image()
                        ->circleCropper()
                        ->disk('public')
                        ->directory('media/landing/avatars')
                        ->visibility('public')
                        ->maxSize(2048),
                    FileUpload::make('testimonial_avatar_3_url')
                        ->label('Testimonial avatar 3')
                        ->image()
                        ->circleCropper()
                        ->disk('public')
                        ->directory('media/landing/avatars')
                        ->visibility('public')
                        ->maxSize(2048),
                ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->sticky($this->areFormActionsSticky())
                    ->key('form-actions'),
            ]);
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label('Update images')
            ->submit('save')
            ->keyBindings(['mod+s']);
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }

    protected function buildImageMeta(?string $path): ?array
    {
        if (blank($path)) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $meta = [
            'path' => $path,
            'size' => null,
            'mime' => null,
            'last_modified' => null,
            'width' => null,
            'height' => null,
        ];

        try {
            $meta['size'] = $disk->size($path);
            $meta['mime'] = $disk->mimeType($path);
            $meta['last_modified'] = $disk->lastModified($path);
        } catch (\Throwable $e) {
            // swallow and return partial meta
        }

        try {
            $absolutePath = $disk->path($path);
            if (is_file($absolutePath)) {
                $dimensions = @getimagesize($absolutePath);
                if (is_array($dimensions)) {
                    $meta['width'] = $dimensions[0] ?? null;
                    $meta['height'] = $dimensions[1] ?? null;
                }
            }
        } catch (\Throwable $e) {
            // ignore dimension failures
        }

        return $meta;
    }
}
