<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PlantResource\RelationManagers;

use App\Enums\PlantImageType;
use App\Models\Plant;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $title = 'Bilder';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('collection_name')
                ->label('Typ')
                ->options($this->typeOptions())
                ->required(),
            TextInput::make('custom_properties.license')
                ->label('Lizenz')
                ->required()
                ->maxLength(255),
            TextInput::make('custom_properties.attribution')
                ->label('Attribution')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('file_name')
            ->columns([
                TextColumn::make('collection_name')->label('Typ')->badge(),
                TextColumn::make('file_name')->limit(40),
                TextColumn::make('custom_properties.license')->label('Lizenz')->toggleable(),
                TextColumn::make('custom_properties.attribution')->label('Attribution')->toggleable(),
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
                        TextInput::make('license')
                            ->label('Lizenz')
                            ->required()
                            ->default('CC BY 4.0')
                            ->maxLength(255),
                        TextInput::make('attribution')
                            ->label('Attribution')
                            ->required()
                            ->maxLength(255),
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

                        $plant->addMedia($upload)
                            ->withCustomProperties([
                                'license' => (string) $data['license'],
                                'attribution' => (string) $data['attribution'],
                                'submitted_by' => auth()->id(),
                            ])
                            ->toMediaCollection((string) $data['collection_name']);
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->using(function (Media $record, array $data): Media {
                        $record->collection_name = (string) $data['collection_name'];
                        $record->setCustomProperty('license', $data['custom_properties']['license'] ?? null);
                        $record->setCustomProperty('attribution', $data['custom_properties']['attribution'] ?? null);
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
