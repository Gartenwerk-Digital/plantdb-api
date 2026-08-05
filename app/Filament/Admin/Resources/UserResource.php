<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Enums\UserTier;
use App\Filament\Admin\Resources\UserResource\Pages\EditUser;
use App\Filament\Admin\Resources\UserResource\Pages\ListUsers;
use App\Filament\Admin\Resources\UserResource\Pages\ViewUser;
use App\Filament\Admin\Resources\UserResource\RelationManagers\TokensRelationManager;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.users');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.users.nav');
    }

    public static function getModelLabel(): string
    {
        return __('admin.users.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.users.plural');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->label(__('admin.users.fields.name'))->disabled(),
            TextInput::make('email')->label(__('admin.users.fields.email'))->disabled(),
            Select::make('tier')
                ->label(__('admin.users.fields.tier'))
                ->options(UserTier::class)
                ->required()
                ->native(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label(__('admin.users.columns.id'))->sortable(),
                TextColumn::make('name')->label(__('admin.users.columns.name'))->searchable()->sortable(),
                TextColumn::make('email')->label(__('admin.users.columns.email'))->searchable()->sortable(),
                IconColumn::make('email_verified_at')
                    ->label(__('admin.users.columns.verified'))
                    ->boolean(),
                TextColumn::make('tier')
                    ->label(__('admin.users.columns.tier'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('tokens_count')
                    ->label(__('admin.users.columns.tokens'))
                    ->counts('tokens')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.users.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc');
    }

    /** @return array<int, class-string> */
    public static function getRelations(): array
    {
        return [
            TokensRelationManager::class,
        ];
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
