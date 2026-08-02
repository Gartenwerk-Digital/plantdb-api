<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Enums\PlantStatus;
use App\Filament\Admin\Resources\FamilyResource\Pages\CreateFamily;
use App\Filament\Admin\Resources\FamilyResource\Pages\EditFamily;
use App\Filament\Admin\Resources\FamilyResource\Pages\ListFamilies;
use App\Filament\Admin\Resources\FamilyResource\Pages\ViewFamily;
use App\Filament\Admin\Resources\FamilyResource\RelationManagers\GeneraRelationManager;
use App\Models\Family;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class FamilyResource extends Resource
{
    protected static ?string $model = Family::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Taxonomy';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
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
            Textarea::make('description')
                ->rows(4)
                ->maxLength(2000)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('plants_count')
                    ->label('Plants')
                    ->counts([
                        'plants' => fn (Builder $query): Builder => $query->where('status', PlantStatus::Approved),
                    ])
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }

    /** @return array<int, class-string> */
    public static function getRelations(): array
    {
        return [
            GeneraRelationManager::class,
        ];
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListFamilies::route('/'),
            'create' => CreateFamily::route('/create'),
            'view' => ViewFamily::route('/{record}'),
            'edit' => EditFamily::route('/{record}/edit'),
        ];
    }
}
