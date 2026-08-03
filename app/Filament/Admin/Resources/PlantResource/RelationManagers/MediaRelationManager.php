<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PlantResource\RelationManagers;

use App\Enums\PlantImageLicense;
use App\Enums\PlantImageType;
use App\Models\Plant;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $title = 'Bilder';

    public function form(Form $form): Form
    {
        return $form->schema([
            Placeholder::make('preview')
                ->label('Vorschau')
                ->content(fn (?Media $record): HtmlString => $record instanceof Media
                    ? new HtmlString('<img src="'.e($record->getUrl('portrait')).'" style="max-width:300px;border-radius:8px" alt="">')
                    : new HtmlString(''))
                ->columnSpanFull()
                ->visible(fn (?Media $record): bool => $record instanceof Media),
            Select::make('collection_name')
                ->label('Typ')
                ->options($this->typeOptions())
                ->required(),
            Select::make('custom_properties.license')
                ->label('Lizenz')
                ->options(PlantImageLicense::options())
                ->default(PlantImageLicense::CcBy->value)
                ->required()
                ->live()
                ->helperText('Standard für Community-Bilder: CC BY 4.0 (Namensnennung).'),
            TextInput::make('custom_properties.attribution')
                ->label('Urheber / Fotograf')
                ->helperText('Wer hat das Bild gemacht? Wird als Bildnachweis in der API angezeigt (z.B. "Foto: Max Mustermann"). Bei eigenen Bildern: dein Name oder Nickname.')
                ->placeholder('z.B. Max Mustermann oder dein Nickname')
                ->maxLength(255)
                ->columnSpanFull()
                ->required(fn (Get $get): bool => (PlantImageLicense::tryFrom((string) $get('custom_properties.license')) ?? PlantImageLicense::CcBy)->requiresAttribution())
                ->visible(fn (Get $get): bool => (PlantImageLicense::tryFrom((string) $get('custom_properties.license')) ?? PlantImageLicense::CcBy)->requiresAttribution()),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('file_name')
            ->columns([
                ImageColumn::make('preview')
                    ->label('Bild')
                    ->getStateUsing(fn (Media $record): string => $record->getUrl('thumb'))
                    ->size(60)
                    ->square(),
                TextColumn::make('collection_name')->label('Typ')->badge(),
                TextColumn::make('file_name')->limit(40),
                TextColumn::make('custom_properties.license')->label('Lizenz')->badge()->toggleable(),
                TextColumn::make('custom_properties.attribution')->label('Urheber')->toggleable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->headerActions([
                TableAction::make('upload')
                    ->label('Bild hochladen')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Select::make('collection_name')
                            ->label('Typ')
                            ->options($this->typeOptions())
                            ->required(),
                        FileUpload::make('file')
                            ->label('Datei')
                            ->required()
                            ->image()
                            ->maxSize(10240)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                            ->disk('local')
                            ->directory('media-tmp'),
                        Select::make('license')
                            ->label('Lizenz')
                            ->options(PlantImageLicense::options())
                            ->default(PlantImageLicense::CcBy->value)
                            ->required()
                            ->live()
                            ->helperText('Standard für Community-Bilder: CC BY 4.0 (Namensnennung).'),
                        Checkbox::make('own_work')
                            ->label('Ich habe dieses Bild selbst gemacht.')
                            ->live()
                            ->afterStateUpdated(function (bool $state, Set $set): void {
                                if ($state) {
                                    $set('attribution', auth()->user()?->name);
                                }
                            }),
                        TextInput::make('attribution')
                            ->label('Urheber / Fotograf')
                            ->helperText('Wer hat das Bild gemacht? Wird als Bildnachweis in der API angezeigt. Bei eigenen Bildern: dein Name oder Nickname.')
                            ->placeholder('z.B. Max Mustermann oder dein Nickname')
                            ->maxLength(255)
                            ->required(fn (Get $get): bool => (PlantImageLicense::tryFrom((string) $get('license')) ?? PlantImageLicense::CcBy)->requiresAttribution())
                            ->visible(fn (Get $get): bool => (PlantImageLicense::tryFrom((string) $get('license')) ?? PlantImageLicense::CcBy)->requiresAttribution()),
                    ])
                    ->action(function (array $data): void {
                        /** @var FilesystemAdapter $disk */
                        $disk = Storage::disk('local');
                        $path = $disk->path((string) $data['file']);

                        $upload = new UploadedFile(
                            $path,
                            basename($path),
                            null,
                            null,
                            true,
                        );

                        /** @var Plant $plant */
                        $plant = $this->getOwnerRecord();

                        $license = PlantImageLicense::from((string) $data['license']);

                        $plant->addMedia($upload)
                            ->withCustomProperties([
                                'license' => $license->value,
                                'attribution' => $license->requiresAttribution() ? (string) ($data['attribution'] ?? '') : null,
                                'own_work' => (bool) ($data['own_work'] ?? false),
                                'submitted_by' => auth()->id(),
                            ])
                            ->toMediaCollection((string) $data['collection_name']);
                    }),
            ])
            ->actions([
                TableAction::make('view')
                    ->label('Ansehen')
                    ->icon('heroicon-o-eye')
                    ->modalContent(fn (Media $record): HtmlString => new HtmlString(
                        '<img src="'.e($record->getUrl()).'" style="max-width:100%;height:auto;border-radius:8px" alt="">'
                    ))
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Schließen'),
                EditAction::make()
                    ->using(function (Media $record, array $data): Media {
                        $record->collection_name = (string) $data['collection_name'];
                        $license = PlantImageLicense::from((string) ($data['custom_properties']['license'] ?? PlantImageLicense::CcBy->value));
                        $record->setCustomProperty('license', $license->value);
                        $record->setCustomProperty(
                            'attribution',
                            $license->requiresAttribution()
                                ? ($data['custom_properties']['attribution'] ?? null)
                                : null,
                        );
                        $record->save();

                        return $record;
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }

    /** @return array<string, string> */
    private function typeOptions(): array
    {
        $options = [];
        foreach (PlantImageType::cases() as $case) {
            $options[$case->value] = ucfirst($case->value);
        }

        return $options;
    }
}
