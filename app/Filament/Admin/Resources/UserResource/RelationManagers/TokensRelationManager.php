<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\UserResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class TokensRelationManager extends RelationManager
{
    protected static string $relationship = 'tokens';

    protected static ?string $title = 'API keys';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('abilities')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => is_array($state) ? implode(', ', $state) : (string) $state),
                TextColumn::make('last_used_at')->dateTime()->placeholder('never'),
                TextColumn::make('expires_at')->dateTime()->placeholder('never'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->headerActions([])
            ->actions([
                DeleteAction::make('revoke')
                    ->label('Revoke')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([]);
    }
}
