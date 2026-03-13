<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoResource\Pages;
use App\Models\Category;
use App\Models\Group;
use App\Models\Tag;
use App\Models\Video;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;
    protected static ?string $navigationIcon = 'heroicon-o-video-camera';
    protected static ?string $navigationLabel = 'Videos';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Info Video')->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                        $set('slug', Str::slug($state))
                    ),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Video::class, 'slug', ignoreRecord: true),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(4)
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Video Source')->schema([
                Forms\Components\Radio::make('video_type')
                    ->label('Tipe Video')
                    ->options([
                        'upload' => 'Upload File',
                        'embed'  => 'Embed URL',
                    ])
                    ->default('upload')
                    ->required()
                    ->live(),
                Forms\Components\FileUpload::make('video_path')
                    ->label('File Video')
                    ->directory('videos')
                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/x-matroska'])
                    ->visible(fn (Get $get) => $get('video_type') === 'upload'),
                Forms\Components\TextInput::make('embed_url')
                    ->label('Embed URL')
                    ->url()
                    ->maxLength(500)
                    ->placeholder('https://www.youtube.com/watch?v=...')
                    ->visible(fn (Get $get) => $get('video_type') === 'embed'),
                Forms\Components\TextInput::make('duration')
                    ->label('Durasi (detik)')
                    ->numeric()
                    ->placeholder('120')
                    ->hint('Untuk embed, isi manual. Upload: otomatis dari konversi.'),
            ]),

            Forms\Components\Section::make('Thumbnail & Media')->schema([
                Forms\Components\FileUpload::make('thumbnail_path')
                    ->label('Thumbnail')
                    ->directory('thumbnails')
                    ->image()
                    ->imagePreviewHeight('150'),
            ]),

            Forms\Components\Section::make('Kategorisasi')->schema([
                Forms\Components\Select::make('group_id')
                    ->label('Group')
                    ->relationship('group', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\Select::make('tags')
                    ->label('Tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(Tag::class, 'slug'),
                    ]),
            ])->columns(3),

            Forms\Components\Section::make('Status & Publikasi')->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'draft'     => 'Draft',
                        'published' => 'Published',
                    ])
                    ->default('draft')
                    ->required(),
                Forms\Components\DateTimePicker::make('published_at')
                    ->label('Tanggal Publikasi')
                    ->nullable(),
                Forms\Components\Toggle::make('is_live')
                    ->label('Live Sekarang')
                    ->helperText('Aktifkan jika ini adalah siaran langsung'),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_path')
                    ->label('Thumb')
                    ->disk('public')
                    ->height(40)
                    ->width(70),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('group.name')
                    ->label('Group')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('video_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn ($state) => $state === 'embed' ? 'warning' : 'success'),
                Tables\Columns\IconColumn::make('is_live')
                    ->label('Live')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => $state === 'published' ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publikasi')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['draft' => 'Draft', 'published' => 'Published']),
                Tables\Filters\SelectFilter::make('video_type')
                    ->options(['upload' => 'Upload', 'embed' => 'Embed'])
                    ->label('Tipe'),
                Tables\Filters\SelectFilter::make('group_id')
                    ->relationship('group', 'name')
                    ->label('Group'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVideos::route('/'),
            'create' => Pages\CreateVideo::route('/create'),
            'edit'   => Pages\EditVideo::route('/{record}/edit'),
        ];
    }
}
