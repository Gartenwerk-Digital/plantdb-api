<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PlantResource\RelationManagers;

use App\Enums\CareTaskType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class CareTasksRelationManager extends RelationManager
{
    protected static string $relationship = 'careTasks';

    protected static ?string $title = 'Care Tasks';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('task_type')->options(CareTaskType::class)->required(),
            Select::make('month_start')->options($this->monthOptions())->required(),
            Select::make('month_end')->options($this->monthOptions())->required(),
            TextInput::make('frequency')->maxLength(255),
            Textarea::make('notes')->rows(3)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('task_type')
            ->columns([
                TextColumn::make('task_type')->badge()->sortable(),
                TextColumn::make('month_start')->sortable(),
                TextColumn::make('month_end')->sortable(),
                TextColumn::make('frequency')->toggleable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }

    /** @return array<int, string> */
    private function monthOptions(): array
    {
        return [
            1 => 'Januar', 2 => 'Februar', 3 => 'März', 4 => 'April',
            5 => 'Mai', 6 => 'Juni', 7 => 'Juli', 8 => 'August',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Dezember',
        ];
    }
}
