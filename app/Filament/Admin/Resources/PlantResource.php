<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Enums\AllergyPotential;
use App\Enums\FertilizingFrequency;
use App\Enums\GrowthRate;
use App\Enums\LifeCycle;
use App\Enums\MaintenanceLevel;
use App\Enums\PlantStatus;
use App\Enums\RootDepth;
use App\Enums\SoilMoisture;
use App\Enums\SunRequirement;
use App\Enums\WateringFrequency;
use App\Filament\Admin\Resources\Concerns\HasTranslationsRepeater;
use App\Filament\Admin\Resources\PlantResource\Pages\CreatePlant;
use App\Filament\Admin\Resources\PlantResource\Pages\EditPlant;
use App\Filament\Admin\Resources\PlantResource\Pages\ListPlants;
use App\Filament\Admin\Resources\PlantResource\Pages\ViewPlant;
use App\Filament\Admin\Resources\PlantResource\RelationManagers\CareTasksRelationManager;
use App\Filament\Admin\Resources\PlantResource\RelationManagers\CompanionsRelationManager;
use App\Filament\Admin\Resources\PlantResource\RelationManagers\MediaRelationManager;
use App\Models\Plant;
use Filament\Actions\Action as HeaderAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class PlantResource extends Resource
{
    use HasTranslationsRepeater;

    protected static ?string $model = Plant::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'scientific_name';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.plants.nav');
    }

    public static function getModelLabel(): string
    {
        return __('admin.plants.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.plants.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Plant')
                ->columnSpanFull()
                ->tabs([
                    Tab::make(__('admin.plants.tabs.botanik'))->schema(self::botanikSchema()),
                    Tab::make(__('admin.plants.tabs.standort'))->schema(self::standortSchema()),
                    Tab::make(__('admin.plants.tabs.wuchs'))->schema(self::wuchsSchema()),
                    Tab::make(__('admin.plants.tabs.blueten'))->schema(self::bluetenSchema()),
                    Tab::make(__('admin.plants.tabs.pflege'))->schema(self::pflegeSchema()),
                    Tab::make(__('admin.plants.tabs.eigenschaften'))->schema(self::eigenschaftenSchema()),
                    Tab::make(__('admin.plants.tabs.translations'))->schema([self::translationsRepeater()]),
                    Tab::make(__('admin.plants.tabs.moderation'))->schema(self::moderationSchema()),
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
                TextColumn::make('scientific_name')
                    ->label(__('admin.plants.fields.scientific_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('common_name_de')
                    ->label(__('admin.plants.columns.common_name_de'))
                    ->getStateUsing(fn (Plant $record): ?string => $record->translations->firstWhere('locale', 'de')?->common_name)
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas(
                        'translations',
                        fn (Builder $q): Builder => $q->where('locale', 'de')->where('common_name', 'ilike', sprintf('%%%s%%', $search))
                    ))
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->leftJoin('plant_translations as pt_de', function ($join): void {
                            $join->on('pt_de.plant_id', '=', 'plants.id')
                                ->where('pt_de.locale', '=', 'de');
                        })
                        ->orderBy('pt_de.common_name', $direction)
                        ->select('plants.*')),
                TextColumn::make('family.name')->label(__('admin.plants.fields.family_id'))->sortable()->toggleable(),
                TextColumn::make('genus.name')->label(__('admin.plants.fields.genus_id'))->sortable()->toggleable(),
                TextColumn::make('status')
                    ->label(__('admin.plants.fields.status'))
                    ->badge()
                    ->color(fn (PlantStatus $state): string => match ($state) {
                        PlantStatus::Draft => 'gray',
                        PlantStatus::PendingReview => 'warning',
                        PlantStatus::Approved => 'success',
                        PlantStatus::Rejected => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->label(__('admin.plants.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('scientific_name')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.plants.fields.status'))
                    ->options(PlantStatus::class)
                    ->default(PlantStatus::PendingReview->value),
                SelectFilter::make('family_id')
                    ->relationship('family', 'name')
                    ->label(__('admin.plants.fields.family_id'))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('life_cycle')
                    ->label(__('admin.plants.fields.life_cycle'))
                    ->options(LifeCycle::class),
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

    /** @return array<int, class-string> */
    public static function getRelations(): array
    {
        return [
            MediaRelationManager::class,
            CompanionsRelationManager::class,
            CareTasksRelationManager::class,
        ];
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListPlants::route('/'),
            'create' => CreatePlant::route('/create'),
            'view' => ViewPlant::route('/{record}'),
            'edit' => EditPlant::route('/{record}/edit'),
        ];
    }

    public static function approveHeaderAction(): HeaderAction
    {
        return HeaderAction::make('approve')
            ->label(__('admin.plants.actions.approve'))
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (Plant $record): bool => $record->status !== PlantStatus::Approved)
            ->requiresConfirmation()
            ->action(function (Plant $record): void {
                self::markApproved($record);

                Notification::make()
                    ->title(__('admin.plants.notifications.approved'))
                    ->success()
                    ->send();
            });
    }

    public static function rejectHeaderAction(): HeaderAction
    {
        return HeaderAction::make('reject')
            ->label(__('admin.plants.actions.reject'))
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (Plant $record): bool => $record->status !== PlantStatus::Rejected)
            ->form([
                Textarea::make('review_notes')
                    ->label(__('admin.plants.actions.rejection_reason'))
                    ->required()
                    ->rows(3),
            ])
            ->action(function (array $data, Plant $record): void {
                self::markRejected($record, (string) $data['review_notes']);

                Notification::make()
                    ->title(__('admin.plants.notifications.rejected'))
                    ->success()
                    ->send();
            });
    }

    private static function markApproved(Plant $plant): void
    {
        $plant->forceFill([
            'status' => PlantStatus::Approved,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ])->save();
    }

    private static function markRejected(Plant $plant, string $reviewNotes): void
    {
        $plant->forceFill([
            'status' => PlantStatus::Rejected,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $reviewNotes,
        ])->save();
    }

    /** @return array<int, mixed> */
    private static function botanikSchema(): array
    {
        return [
            TextInput::make('scientific_name')
                ->label(__('admin.plants.fields.scientific_name'))
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
                ->label(__('admin.plants.fields.slug'))
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            TextInput::make('cultivar')->label(__('admin.plants.fields.cultivar'))->maxLength(255),
            Select::make('family_id')
                ->label(__('admin.plants.fields.family_id'))
                ->relationship('family', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Select::make('genus_id')
                ->label(__('admin.plants.fields.genus_id'))
                ->relationship('genus', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Select::make('life_cycle')->label(__('admin.plants.fields.life_cycle'))->options(LifeCycle::class),
            Toggle::make('deciduous')->label(__('admin.plants.fields.deciduous')),
            TagsInput::make('native_regions')->label(__('admin.plants.fields.native_regions'))->columnSpanFull(),
        ];
    }

    /** @return array<int, mixed> */
    private static function standortSchema(): array
    {
        return [
            Select::make('sun_requirement')->label(__('admin.plants.fields.sun_requirement'))->options(SunRequirement::class),
            Select::make('soil_moisture')->label(__('admin.plants.fields.soil_moisture'))->options(SoilMoisture::class),
            TextInput::make('hardiness_zone_min')->label(__('admin.plants.fields.hardiness_zone_min'))->numeric()->minValue(0)->maxValue(13),
            TextInput::make('hardiness_zone_max')->label(__('admin.plants.fields.hardiness_zone_max'))->numeric()->minValue(0)->maxValue(13),
            Toggle::make('suitable_for_pot')->label(__('admin.plants.fields.suitable_for_pot')),
            TagsInput::make('soil_types')->label(__('admin.plants.fields.soil_types'))->columnSpanFull(),
            TextInput::make('soil_ph_min')->label(__('admin.plants.fields.soil_ph_min'))->numeric()->step(0.1),
            TextInput::make('soil_ph_max')->label(__('admin.plants.fields.soil_ph_max'))->numeric()->step(0.1),
        ];
    }

    /** @return array<int, mixed> */
    private static function wuchsSchema(): array
    {
        return [
            TextInput::make('height_min_cm')->label(__('admin.plants.fields.height_min_cm'))->numeric()->minValue(0),
            TextInput::make('height_max_cm')->label(__('admin.plants.fields.height_max_cm'))->numeric()->minValue(0),
            TextInput::make('width_min_cm')->label(__('admin.plants.fields.width_min_cm'))->numeric()->minValue(0),
            TextInput::make('width_max_cm')->label(__('admin.plants.fields.width_max_cm'))->numeric()->minValue(0),
            Select::make('growth_rate')->label(__('admin.plants.fields.growth_rate'))->options(GrowthRate::class),
            Select::make('root_depth')->label(__('admin.plants.fields.root_depth'))->options(RootDepth::class),
        ];
    }

    /** @return array<int, mixed> */
    private static function bluetenSchema(): array
    {
        return [
            Select::make('bloom_start_month')->label(__('admin.plants.fields.bloom_start_month'))->options(self::monthOptions()),
            Select::make('bloom_end_month')->label(__('admin.plants.fields.bloom_end_month'))->options(self::monthOptions()),
            TagsInput::make('bloom_colors')->label(__('admin.plants.fields.bloom_colors'))->columnSpanFull(),
            Toggle::make('fragrant')->label(__('admin.plants.fields.fragrant')),
            Select::make('fruit_season_start')->label(__('admin.plants.fields.fruit_season_start'))->options(self::monthOptions()),
            Select::make('fruit_season_end')->label(__('admin.plants.fields.fruit_season_end'))->options(self::monthOptions()),
            TagsInput::make('edible_parts')->label(__('admin.plants.fields.edible_parts'))->columnSpanFull(),
            Textarea::make('harvest_notes')->label(__('admin.plants.fields.harvest_notes'))->rows(3)->columnSpanFull(),
        ];
    }

    /** @return array<int, mixed> */
    private static function pflegeSchema(): array
    {
        return [
            Select::make('watering_frequency')->label(__('admin.plants.fields.watering_frequency'))->options(WateringFrequency::class),
            Select::make('fertilizing_frequency')->label(__('admin.plants.fields.fertilizing_frequency'))->options(FertilizingFrequency::class),
            Select::make('maintenance_level')->label(__('admin.plants.fields.maintenance_level'))->options(MaintenanceLevel::class),
            Toggle::make('pruning_required')->label(__('admin.plants.fields.pruning_required')),
            TagsInput::make('propagation_methods')->label(__('admin.plants.fields.propagation_methods'))->columnSpanFull(),
        ];
    }

    /** @return array<int, mixed> */
    private static function eigenschaftenSchema(): array
    {
        return [
            Toggle::make('toxic_to_humans')->label(__('admin.plants.fields.toxic_to_humans')),
            Toggle::make('toxic_to_pets')->label(__('admin.plants.fields.toxic_to_pets')),
            Toggle::make('toxic_to_livestock')->label(__('admin.plants.fields.toxic_to_livestock')),
            Toggle::make('invasive')->label(__('admin.plants.fields.invasive')),
            Toggle::make('attracts_bees')->label(__('admin.plants.fields.attracts_bees')),
            Toggle::make('attracts_butterflies')->label(__('admin.plants.fields.attracts_butterflies')),
            Toggle::make('deer_resistant')->label(__('admin.plants.fields.deer_resistant')),
            Select::make('allergy_potential')->label(__('admin.plants.fields.allergy_potential'))->options(AllergyPotential::class),
        ];
    }

    /** @return array<int, mixed> */
    private static function moderationSchema(): array
    {
        return [
            Select::make('status')
                ->label(__('admin.plants.fields.status'))
                ->options(PlantStatus::class)
                ->required()
                ->default(PlantStatus::Draft->value),
            Textarea::make('review_notes')->label(__('admin.plants.fields.review_notes'))->rows(3)->columnSpanFull(),
            DateTimePicker::make('reviewed_at')->label(__('admin.plants.fields.reviewed_at'))->disabled(),
            Select::make('reviewed_by')
                ->label(__('admin.plants.fields.reviewed_by'))
                ->relationship('reviewer', 'name')
                ->disabled(),
        ];
    }

    /** @return array<int, string> */
    private static function monthOptions(): array
    {
        return [
            1 => 'Januar', 2 => 'Februar', 3 => 'März', 4 => 'April',
            5 => 'Mai', 6 => 'Juni', 7 => 'Juli', 8 => 'August',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Dezember',
        ];
    }
}
