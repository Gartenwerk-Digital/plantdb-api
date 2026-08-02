<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PlantResource\RelationManagers;

use App\Enums\PlantImageType;
use Filament\Forms\Components\Select;
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

final class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Images';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('url')->required()->url()->maxLength(2048)->columnSpanFull(),
            Select::make('type')->options(PlantImageType::class)->required(),
            TextInput::make('license')->maxLength(255),
            TextInput::make('attribution')->maxLength(255)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('url')
            ->columns([
                TextColumn::make('url')->limit(60)->searchable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('license')->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
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
}
