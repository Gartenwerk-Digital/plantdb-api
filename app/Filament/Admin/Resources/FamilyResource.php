<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Enums\PlantStatus;
use App\Filament\Admin\Resources\Concerns\HasTranslationsRepeater;
use App\Filament\Admin\Resources\FamilyResource\Pages\CreateFamily;
use App\Filament\Admin\Resources\FamilyResource\Pages\EditFamily;
use App\Filament\Admin\Resources\FamilyResource\Pages\ListFamilies;
use App\Filament\Admin\Resources\FamilyResource\Pages\ViewFamily;
use App\Filament\Admin\Resources\FamilyResource\RelationManagers\GeneraRelationManager;
use App\Models\Family;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
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
    use HasTranslationsRepeater;

    protected static ?string $model = Family::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.taxonomy');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.families.nav');
    }

    public static function getModelLabel(): string
    {
        return __('admin.families.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.families.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Family')
                ->columnSpanFull()
                ->tabs([
                    Tab::make(__('admin.families.tabs.stammdaten'))->schema([
                        TextInput::make('name')
                            ->label(__('admin.families.fields.name'))
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
                            ->label(__('admin.families.fields.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->label(__('admin.families.fields.description'))
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),
                    Tab::make(__('admin.families.tabs.translations'))->schema([self::translationsRepeater()]),
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
                    ->label(__('admin.families.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('common_name_de')
                    ->label(__('admin.families.columns.common_name_de'))
                    ->getStateUsing(fn (Family $record): ?string => $record->translations->firstWhere('locale', 'de')?->common_name)
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas(
                        'translations',
                        fn (Builder $q): Builder => $q->where('locale', 'de')->where('common_name', 'ilike', sprintf('%%%s%%', $search))
                    ))
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->leftJoin('family_translations as ft_de', function ($join): void {
                            $join->on('ft_de.family_id', '=', 'families.id')
                                ->where('ft_de.locale', '=', 'de');
                        })
                        ->orderBy('ft_de.common_name', $direction)
                        ->select('families.*')),
                TextColumn::make('slug')
                    ->label(__('admin.families.fields.slug'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('plants_count')
                    ->label(__('admin.families.columns.plants'))
                    ->counts([
                        'plants' => fn (Builder $query): Builder => $query->where('status', PlantStatus::Approved),
                    ])
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.families.columns.created_at'))
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
