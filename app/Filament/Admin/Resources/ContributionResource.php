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

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 40;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Placeholder::make('type')
                ->content(fn (Contribution $record): string => $record->type->value),
            Placeholder::make('plant_id')
                ->label('Plant')
                ->content(fn (Contribution $record): string => $record->plant_id ?? '— (new plant)'),
            Placeholder::make('submitter')
                ->content(fn (Contribution $record): string => $record->submitter?->email ?? 'unknown'),
            KeyValue::make('payload')
                ->required()
                ->columnSpanFull()
                ->keyLabel('Field')
                ->valueLabel('Value')
                ->reorderable(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (ContributionType $state): string => match ($state) {
                        ContributionType::NewPlant => 'info',
                        ContributionType::Update => 'primary',
                        ContributionType::Correction => 'warning',
                        ContributionType::Image => 'gray',
                    }),
                TextColumn::make('plant.scientific_name')
                    ->label('Plant')
                    ->placeholder('— (new)')
                    ->searchable(),
                TextColumn::make('submitter.email')
                    ->label('Submitted by')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (ContributionStatus $state): string => match ($state) {
                        ContributionStatus::Pending => 'warning',
                        ContributionStatus::Approved => 'success',
                        ContributionStatus::Rejected => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(ContributionStatus::class)
                    ->default(ContributionStatus::Pending->value),
                SelectFilter::make('type')->options(ContributionType::class),
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
            ->label('Approve')
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
                        ->title('Approve failed')
                        ->body($throwable->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Contribution approved')
                    ->success()
                    ->send();
            });
    }

    public static function rejectTableAction(): TableAction
    {
        return TableAction::make('reject')
            ->label('Reject')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (Contribution $record): bool => $record->status === ContributionStatus::Pending)
            ->form([
                Textarea::make('review_notes')
                    ->required()
                    ->rows(3)
                    ->label('Reviewer notes (sent to submitter)'),
            ])
            ->action(function (array $data, Contribution $record): void {
                /** @var User $reviewer */
                $reviewer = auth()->user();

                resolve(RejectContribution::class)($record, $reviewer, (string) $data['review_notes']);

                Notification::make()
                    ->title('Contribution rejected')
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
