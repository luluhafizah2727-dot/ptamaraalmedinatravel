<?php

namespace App\Filament\Resources\UmrahPackages\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UmrahPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')->label('Gambar')->disk('public')->square()->imageSize(58),
                TextColumn::make('name')->label('Nama Paket')->searchable()->sortable(),
                TextColumn::make('duration_days')->label('Durasi')->suffix(' Hari')->sortable(),
                TextColumn::make('price')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state): string => filled($state)
                        ? 'Rp '.number_format((float) $state, 0, ',', '.')
                        : '-')
                    ->sortable(),
                IconColumn::make('is_featured')->label('Utama')->boolean(),
                IconColumn::make('is_active')->label('Tampil')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->paginated(false)
            ->filters([
                TernaryFilter::make('is_active')->label('Status Tampil'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger'),
            ]);
    }
}
