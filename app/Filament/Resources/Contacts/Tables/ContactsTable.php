<?php

namespace App\Filament\Resources\Contacts\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('address')->label('Alamat')->limit(60),
                TextColumn::make('whatsapp')->label('WhatsApp')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('instagram')->label('Instagram'),
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
