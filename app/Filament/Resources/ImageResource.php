<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImageResource\Pages;
use App\Models\Image;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\File;

class ImageResource extends Resource
{
    protected static ?string $model = Image::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $modelLabel = 'Imagen';

    protected static ?string $pluralModelLabel = 'Imágenes';

    protected static ?string $navigationLabel = 'Imágenes';

    protected static ?string $navigationGroup = 'Gestión de Archivos';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Imagen')
                    ->schema([
                        Forms\Components\TextInput::make('empresa')
                            ->label('Empresa')
                            ->placeholder('Ej: fombiopol')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('descripcion')
                            ->label('Descripción / Categoría')
                            ->placeholder('Ej: madicamentos')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\FileUpload::make('imagen_tmp')
                            ->label('Archivo de Imagen')
                            ->image()
                            ->maxSize(2048) // 2MB limit
                            ->required(fn ($livewire) => $livewire instanceof Pages\CreateImage)
                            ->helperText(fn ($livewire) => $livewire instanceof Pages\EditImage
                                ? 'Dejar en blanco para mantener la imagen actual o seleccione una nueva (Máx 2MB, .webp)'
                                : 'Seleccione una imagen (Máx 2MB). Se convertirá automáticamente a formato .webp'
                            ),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('url')
                    ->label('Vista Previa')
                    ->square()
                    ->size(60),

                Tables\Columns\TextColumn::make('empresa')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('size_kb')
                    ->label('Tamaño')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->suffix(' KB'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Subida')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('empresa')
                    ->options(fn () => Image::distinct()->pluck('empresa', 'empresa')->toArray()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('copy_url')
                    ->label('Copiar URL')
                    ->icon('heroicon-m-clipboard-document-check')
                    ->color('success')
                    ->action(function (Image $record) {
                        Notification::make()
                            ->title('URL copiada al portapapeles')
                            ->body($record->url)
                            ->success()
                            ->send();
                    })
                    ->extraAttributes(fn (Image $record) => [
                        'x-on:click' => "(function(val) { if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(val); } else { var ta = document.createElement('textarea'); ta.value = val; ta.style.position = 'fixed'; ta.style.left = '-9999px'; document.body.appendChild(ta); ta.focus(); ta.select(); try { document.execCommand('copy'); } catch(e){} document.body.removeChild(ta); } })('" . addslashes($record->url) . "')",
                    ]),

                Tables\Actions\DeleteAction::make()
                    ->before(function (Image $record) {
                        $filePath = public_path($record->full_path);
                        if (File::exists($filePath)) {
                            File::delete($filePath);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                $filePath = public_path($record->full_path);
                                if (File::exists($filePath)) {
                                    File::delete($filePath);
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImages::route('/'),
            'create' => Pages\CreateImage::route('/create'),
            'edit' => Pages\EditImage::route('/{record}/edit'),
        ];
    }
}
