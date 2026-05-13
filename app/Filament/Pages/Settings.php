<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;

class Settings extends Page implements HasForms
{
    use InteractsWithFormActions;
    use InteractsWithForms;

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
            'company_legal_name' => Setting::get('company_legal_name', ''),
            'company_iban' => Setting::get('company_iban', ''),
            'company_rea' => Setting::get('company_rea', ''),
            'company_pec' => Setting::get('company_pec', ''),
            'company_sdi' => Setting::get('company_sdi', ''),
            'default_vat_rate' => Setting::get('default_vat_rate', 22),
            'shipping_cost' => Setting::get('shipping_cost', 0),
            'free_shipping_threshold' => Setting::get('free_shipping_threshold', 0),
            'vip_minimum_order_threshold' => Setting::get('vip_minimum_order_threshold', 1000),
            'vip_time_period_days' => Setting::get('vip_time_period_days', 365),
            'vip_auto_qualify_enabled' => Setting::get('vip_auto_qualify_enabled', true),
            'points_per_euro_rate' => Setting::get('points_per_euro_rate', 1.00),
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
                Section::make('Documenti e dati aziendali')
                    ->schema([
                        TextInput::make('company_legal_name')->label('Ragione sociale (documenti)')->maxLength(255),
                        TextInput::make('company_iban')->label('IBAN')->maxLength(64),
                        TextInput::make('company_rea')->label('REA')->maxLength(64),
                        TextInput::make('company_pec')->label('PEC')->maxLength(255),
                        TextInput::make('company_sdi')->label('Codice SDI')->maxLength(32),
                        TextInput::make('default_vat_rate')
                            ->label('Aliquota IVA predefinita prodotti (%)')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%'),
                    ])->columns(2),
                Section::make('Spedizione')
                    ->schema([
                        TextInput::make('shipping_cost')->label('Costo Spedizione (€)')->numeric(),
                        TextInput::make('free_shipping_threshold')->label('Soglia Spedizione Gratuita (€)')->numeric(),
                    ])->columns(2),
                Section::make('Sistema VIP e Punti')
                    ->schema([
                        TextInput::make('vip_minimum_order_threshold')
                            ->label('Soglia Ordini Minima per VIP (€)')
                            ->numeric()
                            ->default(1000)
                            ->helperText('Spesa minima nel periodo di tempo per qualificarsi come VIP'),
                        TextInput::make('vip_time_period_days')
                            ->label('Periodo di Tempo per VIP (giorni)')
                            ->numeric()
                            ->default(365)
                            ->helperText('Periodo di osservazione della cronologia ordini'),
                        Toggle::make('vip_auto_qualify_enabled')
                            ->label('Abilita Qualificazione VIP Automatica')
                            ->helperText('Assegna automaticamente lo status VIP quando i clienti raggiungono la soglia'),
                        TextInput::make('points_per_euro_rate')
                            ->label('Tasso Punti per Euro')
                            ->numeric()
                            ->step(0.01)
                            ->default(1.00)
                            ->helperText('Quanti punti equivalgono a 1 euro'),
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
        Notification::make()
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
