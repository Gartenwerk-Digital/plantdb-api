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

    protected static ?int $navigationSort = 20;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.taxonomy');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.genera.nav');
    }

    public static function getModelLabel(): string
    {
        return __('admin.genera.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.genera.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Genus')
                ->columnSpanFull()
                ->tabs([
                    Tab::make(__('admin.genera.tabs.stammdaten'))->schema([
                        Select::make('family_id')
                            ->label(__('admin.genera.fields.family_id'))
                            ->relationship('family', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('name')
                            ->label(__('admin.genera.fields.name'))
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
                            ->label(__('admin.genera.fields.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),
                    Tab::make(__('admin.genera.tabs.translations'))->schema([self::translationsRepeater()]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'translations' => fn ($q) => $q->where('locale', 'de'),
            ]))
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.genera.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('common_name_de')
                    ->label(__('admin.genera.columns.common_name_de'))
                    ->getStateUsing(fn (Genus $record): ?string => $record->translations->firstWhere('locale', 'de')?->common_name)
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas(
                        'translations',
                        fn (Builder $q): Builder => $q->where('locale', 'de')->where('common_name', 'ilike', sprintf('%%%s%%', $search))
                    ))
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->leftJoin('genus_translations as gt_de', function ($join): void {
                            $join->on('gt_de.genus_id', '=', 'genera.id')
                                ->where('gt_de.locale', '=', 'de');
                        })
                        ->orderBy('gt_de.common_name', $direction)
                        ->select('genera.*')),
                TextColumn::make('slug')
                    ->label(__('admin.genera.fields.slug'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('family.name')
                    ->label(__('admin.genera.columns.family'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('plants_count')
                    ->label(__('admin.genera.columns.plants'))
                    ->counts([
                        'plants' => fn (Builder $query): Builder => $query->where('status', PlantStatus::Approved),
                    ])
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.genera.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('family_id')
                    ->relationship('family', 'name')
                    ->label(__('admin.genera.fields.family_id'))
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
