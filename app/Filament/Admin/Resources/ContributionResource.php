<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Actions\Contributions\ApproveContribution;
use App\Actions\Contributions\RejectContribution;
use App\Enums\ContributionStatus;
use App\Enums\ContributionType;
use App\Filament\Admin\Resources\ContributionResource\Pages\EditContribution;
use App\Filament\Admin\Resources\ContributionResource\Pages\ListContributions;
use App\Filament\Admin\Resources\ContributionResource\Pages\ViewContribution;
use App\Models\Contribution;
use App\Models\User;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Throwable;

final class ContributionResource extends Resource
{
    protected static ?string $model = Contribution::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?int $navigationSort = 40;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.contributions.nav');
    }

    public static function getModelLabel(): string
    {
        return __('admin.contributions.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.contributions.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Placeholder::make('type')
                ->label(__('admin.contributions.fields.type'))
                ->content(fn (Contribution $record): string => $record->type->value),
            Placeholder::make('plant_id')
                ->label(__('admin.contributions.fields.plant'))
                ->content(fn (Contribution $record): string => $record->plant_id ?? __('admin.contributions.fields.plant_new_placeholder')),
            Placeholder::make('submitter')
                ->label(__('admin.contributions.fields.submitter'))
                ->content(fn (Contribution $record): string => $record->submitter?->email ?? __('admin.contributions.fields.submitter_unknown')),
            KeyValue::make('payload')
                ->required()
                ->columnSpanFull()
                ->keyLabel(__('admin.contributions.fields.payload_key'))
                ->valueLabel(__('admin.contributions.fields.payload_value'))
                ->reorderable(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label(__('admin.contributions.columns.type'))
                    ->badge()
                    ->color(fn (ContributionType $state): string => match ($state) {
                        ContributionType::NewPlant => 'info',
                        ContributionType::Update => 'primary',
                        ContributionType::Correction => 'warning',
                        ContributionType::Image => 'gray',
                    }),
                TextColumn::make('plant.scientific_name')
                    ->label(__('admin.contributions.columns.plant'))
                    ->placeholder(__('admin.contributions.columns.plant_new_placeholder'))
                    ->searchable(),
                TextColumn::make('submitter.email')
                    ->label(__('admin.contributions.columns.submitter'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(__('admin.contributions.columns.status'))
                    ->badge()
                    ->color(fn (ContributionStatus $state): string => match ($state) {
                        ContributionStatus::Pending => 'warning',
                        ContributionStatus::Approved => 'success',
                        ContributionStatus::Rejected => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->label(__('admin.contributions.columns.submitted_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.contributions.fields.status'))
                    ->options(ContributionStatus::class)
                    ->default(ContributionStatus::Pending->value),
                SelectFilter::make('type')
                    ->label(__('admin.contributions.fields.type'))
                    ->options(ContributionType::class),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (Contribution $record): bool => $record->status === ContributionStatus::Pending),
                self::approveTableAction(),
                self::rejectTableAction(),
            ]);
    }

    public static function approveTableAction(): TableAction
    {
        return TableAction::make('approve')
            ->label(__('admin.contributions.actions.approve'))
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (Contribution $record): bool => $record->status === ContributionStatus::Pending)
            ->requiresConfirmation()
            ->action(function (Contribution $record): void {
                /** @var User $reviewer */
                $reviewer = auth()->user();

                try {
                    resolve(ApproveContribution::class)($record, $reviewer);
                } catch (Throwable $throwable) {
                    Notification::make()
                        ->title(__('admin.contributions.notifications.approve_failed'))
                        ->body($throwable->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title(__('admin.contributions.notifications.approved'))
                    ->success()
                    ->send();
            });
    }

    public static function rejectTableAction(): TableAction
    {
        return TableAction::make('reject')
            ->label(__('admin.contributions.actions.reject'))
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (Contribution $record): bool => $record->status === ContributionStatus::Pending)
            ->form([
                Textarea::make('review_notes')
                    ->required()
                    ->rows(3)
                    ->label(__('admin.contributions.fields.review_notes')),
            ])
            ->action(function (array $data, Contribution $record): void {
                /** @var User $reviewer */
                $reviewer = auth()->user();

                resolve(RejectContribution::class)($record, $reviewer, (string) $data['review_notes']);

                Notification::make()
                    ->title(__('admin.contributions.notifications.rejected'))
                    ->success()
                    ->send();
            });
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListContributions::route('/'),
            'view' => ViewContribution::route('/{record}'),
            'edit' => EditContribution::route('/{record}/edit'),
        ];
    }
}
