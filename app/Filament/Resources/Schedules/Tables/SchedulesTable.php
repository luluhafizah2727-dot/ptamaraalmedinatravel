<?php

namespace App\Filament\Resources\Schedules\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('departure_date')->label('Tanggal')->date('d F Y')->sortable(),
                TextColumn::make('umrahPackage.name')->label('Paket')->searchable(),
                TextColumn::make('quota')->label('Kuota')->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Tersedia' => 'success',
                        'Hampir Penuh' => 'warning',
                        'Penuh' => 'danger',
                        default => 'gray',
                    }),
                IconColumn::make('is_active')->label('Tampil')->boolean(),
            ])
            ->defaultSort('departure_date')
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
