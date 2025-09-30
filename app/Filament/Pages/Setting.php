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
            Forms\Components\TextInput::make('telegram_bot_link')
                ->label('Telegram Bot Link')
                ->required()
                ->placeholder('https://t.me/your_bot_link'),
            Forms\Components\TextInput::make('telegram_bot_token')
                ->label('Telegram Bot Token')
                ->required()
                ->placeholder('123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZ'),
            Forms\Components\TextInput::make('admin_telegram_id')
                ->label('Admin Telegram ID')
                ->required()
                ->placeholder('123456789'),
            Forms\Components\Textarea::make('welcome_message')
                ->label('Welcome Message')
                ->required()
                ->placeholder('Welcome to our service!'),
        ])->statePath('data');
    }

    public function submit()
    {
        $data = $this->form->getState('data');
        $this->saveSettings($data);

        Notification::make('success')->icon('heroicon-o-check-circle')->body('Successfully save settings!')->success()->send();
    }
}
