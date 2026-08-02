<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\FamilyResource\RelationManagers;

use App\Enums\PlantStatus;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class GeneraRelationManager extends RelationManager
{
    protected static string $relationship = 'genera';

    protected static ?string $title = 'Genera';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (?string $state, Set $set, string $operation): void {
                    if ($operation !== 'create' || $state === null) {
                        return;
                    }

                    $set('slug', Str::slug($state));
                }),
            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('slug')->toggleable(),
                TextColumn::make('plants_count')
                    ->label('Plants')
                    ->counts([
                        'plants' => fn (Builder $query): Builder => $query->where('status', PlantStatus::Approved),
                    ])
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
