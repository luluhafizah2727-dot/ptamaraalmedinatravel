<?php

namespace App\Filament\Resources\CompanyProfiles\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompanyProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_path')->label('Foto')->disk('public')->square()->imageSize(58),
                TextColumn::make('company_name')->label('Perusahaan')->searchable(),
                TextColumn::make('about')->label('Tentang')->limit(80),
                IconColumn::make('is_active')->label('Tampil')->boolean(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated(false)
            ->filters([
                //
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
