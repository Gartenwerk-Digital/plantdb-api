<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PlantResource\RelationManagers;

use App\Enums\CompanionRelationship;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\DetachBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class CompanionsRelationManager extends RelationManager
{
    protected static string $relationship = 'companions';

    protected static ?string $title = 'Companions';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('relationship')
                ->options(CompanionRelationship::class)
                ->required(),
            Textarea::make('notes')->rows(3)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('scientific_name')
            ->columns([
                TextColumn::make('scientific_name')->searchable()->sortable(),
                TextColumn::make('pivot.relationship')
                    ->label('Relationship')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        CompanionRelationship::Beneficial->value => 'success',
                        CompanionRelationship::Incompatible->value => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('pivot.notes')->label('Notes')->limit(60),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('relationship')
                            ->options(CompanionRelationship::class)
                            ->required(),
                        Textarea::make('notes')->rows(3),
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DetachAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
