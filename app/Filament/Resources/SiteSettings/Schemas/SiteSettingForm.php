<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use App\Models\SiteSetting;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SiteSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pengaturan Website')
                    ->description('Ubah teks dan media utama website dari halaman ini. Untuk logo, favicon, dan hero, upload baru akan mengganti file slot yang sama.')
                    ->columns(2)
                    ->schema([
                        Select::make('key')
                            ->label('Bagian yang Diatur')
                            ->options(SiteSetting::selectableOptions())
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabledOn('edit')
                            ->live()
                            ->afterStateUpdated(static function (Set $set): void {
                                $set('value', null);
                            })
                            ->helperText(fn (Get $get): string => SiteSetting::descriptionFor($get('key')))
                            ->columnSpanFull(),
                        Grid::make(1)
                            ->schema(fn (Get $get): array => self::valueFieldFor($get('key')))
                            ->key('siteSettingValueField')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function valueFieldFor(?string $key): array
    {
        if (SiteSetting::isImageKey($key)) {
            return [
                FileUpload::make('value')
                    ->label(SiteSetting::labelFor($key))
                    ->helperText(self::imageHelperText($key))
                    ->image()
                    ->disk('public')
                    ->directory((string) (SiteSetting::definition($key)['directory'] ?? 'site-settings'))
                    ->visibility('public')
                    ->imageEditor()
                    ->imageCropAspectRatio(SiteSetting::definition($key)['aspect_ratio'] ?? null)
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth(SiteSetting::definition($key)['resize_width'] ?? null)
                    ->imageResizeTargetHeight(SiteSetting::definition($key)['resize_height'] ?? null)
                    ->imageResizeUpscale(false)
                    ->imagePreviewHeight(SiteSetting::definition($key)['preview_height'] ?? null)
                    ->maxSize((int) (SiteSetting::definition($key)['max_size'] ?? 2048))
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->previewable()
                    ->openable()
                    ->downloadable()
                    ->removeUploadedFileButtonPosition('right')
                    ->uploadButtonPosition('right')
                    ->saveUploadedFileUsing(fn (BaseFileUpload $component, TemporaryUploadedFile $file): string => self::storeReplacementImage($component, $file))
                    ->columnSpanFull(),
            ];
        }

        if (SiteSetting::isTextareaKey($key)) {
            return [
                Textarea::make('value')
                    ->label(SiteSetting::labelFor($key))
                    ->helperText(SiteSetting::descriptionFor($key))
                    ->rows(4)
                    ->columnSpanFull(),
            ];
        }

        return [
            TextInput::make('value')
                ->label(SiteSetting::labelFor($key))
                ->helperText(SiteSetting::descriptionFor($key))
                ->maxLength(255)
                ->columnSpanFull(),
        ];
    }

    private static function imageHelperText(?string $key): string
    {
        $definition = SiteSetting::definition($key);
        $ratio = $definition['aspect_ratio'] ?? null;
        $width = $definition['resize_width'] ?? null;
        $height = $definition['resize_height'] ?? null;
        $maxSize = (int) ($definition['max_size'] ?? 2048);

        return trim(sprintf(
            '%s Upload baru akan mengganti gambar lama. Rasio crop: %s. Ukuran hasil: %sx%s px. Maksimal file: %s MB.',
            SiteSetting::descriptionFor($key),
            $ratio ?: 'otomatis',
            $width ?: '-',
            $height ?: '-',
            number_format($maxSize / 1024, 0),
        ));
    }

    private static function storeReplacementImage(BaseFileUpload $component, TemporaryUploadedFile $file): string
    {
        /** @var SiteSetting|null $record */
        $record = $component->getRecord();

        return SiteSetting::storeReplacementImage($record?->key, $file, $record?->value);
    }
}
