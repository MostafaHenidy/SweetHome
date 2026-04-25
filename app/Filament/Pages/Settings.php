<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Filament\Pages\Concerns\InteractsWithFormActions;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithFormActions;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.pages.settings';
    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => Setting::get('site_name', 'SweetHome Milano'),
            'site_email' => Setting::get('site_email', 'info@sweethome.it'),
            'site_phone' => Setting::get('site_phone', '328 899 5628'),
            'site_address' => Setting::get('site_address', 'Via Carlo Espinasse 104/a, 20156 Milano MI'),
            'site_logo' => Setting::get('site_logo', null),
            'site_favicon' => Setting::get('site_favicon', null),
            'vat_number' => Setting::get('vat_number', ''),
            'tax_code' => Setting::get('tax_code', ''),
            'shipping_cost' => Setting::get('shipping_cost', 0),
            'free_shipping_threshold' => Setting::get('free_shipping_threshold', 0),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informazioni Sito')
                    ->schema([
                        TextInput::make('site_name')->label('Nome Sito'),
                        TextInput::make('site_email')->label('Email'),
                        TextInput::make('site_phone')->label('Telefono'),
                        Textarea::make('site_address')->label('Indirizzo'),
                        FileUpload::make('site_logo')
                            ->label('Logo')
                            ->image()
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->maxSize(2048),
                        FileUpload::make('site_favicon')
                            ->label('Favicon')
                            ->image()
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->maxSize(1024),
                        TextInput::make('vat_number')->label('Partita IVA'),
                        TextInput::make('tax_code')->label('Codice Fiscale'),
                    ])->columns(2),
                Section::make('Spedizione')
                    ->schema([
                        TextInput::make('shipping_cost')->label('Costo Spedizione (€)')->numeric(),
                        TextInput::make('free_shipping_threshold')->label('Soglia Spedizione Gratuita (€)')->numeric(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }
        \Filament\Notifications\Notification::make()
            ->title('Impostazioni salvate con successo.')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Salva Impostazioni')
                ->submit('save'),
        ];
    }
}
