<?php

namespace App\Filament\Resources\Galleries\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GalleriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')->label('Foto')->disk('public')->square()->imageSize(58),
                TextColumn::make('title')->label('Judul')->searchable(),
                TextColumn::make('taken_at')->label('Tanggal')->date('d F Y')->sortable(),
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
