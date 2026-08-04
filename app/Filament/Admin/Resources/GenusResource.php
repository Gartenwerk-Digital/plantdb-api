<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Enums\PlantStatus;
use App\Filament\Admin\Resources\Concerns\HasTranslationsRepeater;
use App\Filament\Admin\Resources\GenusResource\Pages\CreateGenus;
use App\Filament\Admin\Resources\GenusResource\Pages\EditGenus;
use App\Filament\Admin\Resources\GenusResource\Pages\ListGenera;
use App\Filament\Admin\Resources\GenusResource\Pages\ViewGenus;
use App\Models\Genus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class GenusResource extends Resource
{
    use HasTranslationsRepeater;

    protected static ?string $model = Genus::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Taxonomy';

    protected static ?int $navigationSort = 20;

    protected static ?string $pluralModelLabel = 'Genera';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Genus')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Stammdaten')->schema([
                        Select::make('family_id')
                            ->relationship('family', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
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
                    ]),
                    Tab::make('Übersetzungen')->schema([self::translationsRepeater()]),
                ]),
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
                TextColumn::make('family.name')
                    ->label('Family')
                    ->searchable()
                    ->sortable(),
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
            ->filters([
                SelectFilter::make('family_id')
                    ->relationship('family', 'name')
                    ->label('Family')
                    ->searchable(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListGenera::route('/'),
            'create' => CreateGenus::route('/create'),
            'view' => ViewGenus::route('/{record}'),
            'edit' => EditGenus::route('/{record}/edit'),
        ];
    }
}
