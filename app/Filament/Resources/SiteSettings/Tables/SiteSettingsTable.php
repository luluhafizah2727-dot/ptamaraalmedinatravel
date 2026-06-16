<?php

namespace App\Filament\Resources\SiteSettings\Tables;

use App\Models\SiteSetting;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Bagian')
                    ->formatStateUsing(fn (?string $state): string => SiteSetting::labelFor($state))
                    ->description(fn (SiteSetting $record): string => $record->key)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('value')
                    ->label('Isi Saat Ini')
                    ->formatStateUsing(fn (?string $state, SiteSetting $record): string => SiteSetting::isImageKey($record->key) ? basename((string) $state) : (string) $state)
                    ->limit(80),
            ])
            ->defaultSort('key')
            ->paginated(false)
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning'),
            ]);
    }
}
