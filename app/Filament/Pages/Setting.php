<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;

class Setting extends Page implements HasForms
{
    use InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static string $view = 'filament.pages.setting';

    protected static ?string $navigationGroup = 'Settings';

    public $data = [];
    
    public function mount(): void
    {
        $this->data = $this->loadSettings();
        
        $this->form->fill($this->loadSettings());
    }

    protected function loadSettings(): array
    {
        $settings = Storage::get('settings.json'); // storage/app/settings.json  
        return json_decode($settings, true) ?? [];
    }

    protected function saveSettings(array $data): void
    {
        $settings = json_encode($data, JSON_PRETTY_PRINT);
        Storage::put('settings.json', $settings);
    }

    public function form(Form $form):Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('server_ip')->label('IP SERVER'),
            Forms\Components\TextInput::make('site_url')->prefix('https://')->required(),
            Forms\Components\TextInput::make('site_name')->required(),
            Forms\Components\TextInput::make('site_description')->required(),
            Forms\Components\FileUpload::make('icon')->avatar()->image(),
            Forms\Components\FileUpload::make('logo')->image()->imageEditor(),

            Forms\Components\Section::make('sosial media')->schema([
                Forms\Components\TextInput::make('sosmed.facebook_url'),
                Forms\Components\TextInput::make('sosmed.instagram_url'),
                Forms\Components\TextInput::make('sosmed.titktok_url'),
                Forms\Components\TextInput::make('sosmed.whatsapp_url'),
            ])->columns(2),
            Forms\Components\Repeater::make('meta_setting')->schema([
                Forms\Components\TextInput::make('name'),
                Forms\Components\TextInput::make('content')
            ])->columns(2)
        ])->statePath('data');
    }

    public function submit()
    {
        $data = $this->form->getState('data');
        $this->saveSettings($data);

        Notification::make('success')->icon('heroicon-o-check-circle')->body('Successfully save settings!')->success()->send();
    }
}
