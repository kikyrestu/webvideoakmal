<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.pages.site-settings';
    protected static ?string $navigationLabel = 'Site Settings';
    protected static ?string $title = 'Site Settings';
    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::all()->pluck('value', 'key')->toArray();

        $this->form->fill([
            'site_name'          => $settings['site_name'] ?? '',
            'site_description'   => $settings['site_description'] ?? '',
            'site_logo'          => $settings['site_logo'] ?? null,
            'site_favicon'       => $settings['site_favicon'] ?? null,
            'sidebar_menu'       => json_decode($settings['sidebar_menu'] ?? '[]', true),
            'nav_filter_labels'  => json_decode($settings['nav_filter_labels'] ?? '["Info","Umum"]', true),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Identitas Website')->schema([
                    TextInput::make('site_name')
                        ->label('Nama Website')
                        ->required()
                        ->maxLength(100),
                    Textarea::make('site_description')
                        ->label('Deskripsi Website')
                        ->rows(3)
                        ->maxLength(300),
                ])->columns(2),

                Section::make('Logo & Favicon')->schema([
                    FileUpload::make('site_logo')
                        ->label('Logo Header')
                        ->directory('settings')
                        ->image()
                        ->imagePreviewHeight('80'),
                    FileUpload::make('site_favicon')
                        ->label('Favicon')
                        ->directory('settings')
                        ->image()
                        ->imagePreviewHeight('40'),
                ])->columns(2),

                Section::make('Sidebar Menu')->schema([
                    Repeater::make('sidebar_menu')
                        ->label('Item Menu Sidebar')
                        ->schema([
                            TextInput::make('label')->required()->label('Label'),
                            TextInput::make('icon')->label('Icon Class')->placeholder('heroicon-o-home'),
                            TextInput::make('url')->required()->label('URL'),
                        ])
                        ->columns(3)
                        ->addActionLabel('Tambah Menu Item'),
                ]),

                Section::make('Filter Navbar')->schema([
                    TextInput::make('nav_filter_labels.0')
                        ->label('Label Filter Kiri')
                        ->default('Info'),
                    TextInput::make('nav_filter_labels.1')
                        ->label('Label Filter Kanan')
                        ->default('Umum'),
                ])->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $map = [
            'site_name'        => $data['site_name'],
            'site_description' => $data['site_description'],
            'site_logo'        => $data['site_logo'] ?? '',
            'site_favicon'     => $data['site_favicon'] ?? '',
            'sidebar_menu'     => json_encode($data['sidebar_menu'] ?? []),
            'nav_filter_labels'=> json_encode(array_values($data['nav_filter_labels'] ?? ['Info', 'Umum'])),
        ];

        foreach ($map as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget('site_settings');

        Notification::make()
            ->title('Pengaturan berhasil disimpan!')
            ->success()
            ->send();
    }
}
