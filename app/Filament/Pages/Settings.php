<?php

namespace App\Filament\Pages;

use App\Models\PickupLocation;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
        // Load pickup locations
        $pickupLocations = PickupLocation::all();
        $locationsData = [];

        foreach ($pickupLocations as $location) {
            $locationsData[] = [
                'id' => $location->id,
                'name' => $location->name,
                'address' => $location->address,
                'city' => $location->city,
                'province' => $location->province,
                'postal_code' => $location->postal_code,
                'country' => $location->country,
                'phone' => $location->phone,
                'email' => $location->email,
                'hours' => $location->hours,
                'is_active' => $location->is_active,
                'sort_order' => $location->sort_order,
            ];
        }

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
            'pickup_locations' => $locationsData,
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
                Section::make('Punti di Ritiro (Warehouses/Branches)')
                    ->description('Gestisci i luoghi dove i clienti possono ritirare gli ordini')
                    ->schema([
                        Repeater::make('pickup_locations')
                            ->label('Località di Ritiro')
                            ->schema([
                                Grid::make(['default' => 1, 'md' => 2, 'lg' => 3])->schema([
                                    TextInput::make('name')
                                        ->label('Nome')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('phone')
                                        ->label('Telefono')
                                        ->maxLength(20),
                                    TextInput::make('email')
                                        ->label('Email')
                                        ->email()
                                        ->maxLength(255),
                                ]),
                                TextInput::make('address')
                                    ->label('Indirizzo')
                                    ->maxLength(255),
                                Grid::make(['default' => 1, 'md' => 3])->schema([
                                    TextInput::make('city')
                                        ->label('Città')
                                        ->required()
                                        ->maxLength(100),
                                    TextInput::make('province')
                                        ->label('Provincia')
                                        ->maxLength(100),
                                    TextInput::make('postal_code')
                                        ->label('CAP')
                                        ->maxLength(10),
                                ]),
                                Grid::make(['default' => 1, 'md' => 2])->schema([
                                    TextInput::make('country')
                                        ->label('Paese')
                                        ->default('IT')
                                        ->maxLength(2),
                                    TextInput::make('sort_order')
                                        ->label('Ordinamento')
                                        ->numeric()
                                        ->default(0)
                                        ->helperText('Ordine di visualizzazione (0 = primo)'),
                                ]),
                                Textarea::make('hours')
                                    ->label('Orari di Apertura')
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->helperText('Es: Lun-Ven: 9:00-18:00, Sab: 9:00-13:00'),
                                Toggle::make('is_active')
                                    ->label('Attivo')
                                    ->default(true)
                                    ->helperText('Mostra questa località ai clienti'),
                            ])
                            ->columns(1)
                            ->addActionLabel('Aggiungi Località di Ritiro')
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Save general settings
        foreach ($data as $key => $value) {
            if ($key !== 'pickup_locations') {
                Setting::set($key, $value);
            }
        }

        // Save pickup locations
        if (isset($data['pickup_locations'])) {
            $existingIds = [];

            foreach ($data['pickup_locations'] as $locationData) {
                $locationId = $locationData['id'] ?? null;

                if ($locationId) {
                    // Update existing location
                    $location = PickupLocation::find($locationId);
                    if ($location) {
                        $location->update([
                            'name' => $locationData['name'],
                            'address' => $locationData['address'] ?? null,
                            'city' => $locationData['city'],
                            'province' => $locationData['province'] ?? null,
                            'postal_code' => $locationData['postal_code'] ?? null,
                            'country' => $locationData['country'] ?? 'IT',
                            'phone' => $locationData['phone'] ?? null,
                            'email' => $locationData['email'] ?? null,
                            'hours' => $locationData['hours'] ?? null,
                            'is_active' => $locationData['is_active'] ?? true,
                            'sort_order' => $locationData['sort_order'] ?? 0,
                        ]);
                        $existingIds[] = $locationId;
                    }
                } else {
                    // Create new location
                    $location = PickupLocation::create([
                        'name' => $locationData['name'],
                        'address' => $locationData['address'] ?? null,
                        'city' => $locationData['city'],
                        'province' => $locationData['province'] ?? null,
                        'postal_code' => $locationData['postal_code'] ?? null,
                        'country' => $locationData['country'] ?? 'IT',
                        'phone' => $locationData['phone'] ?? null,
                        'email' => $locationData['email'] ?? null,
                        'hours' => $locationData['hours'] ?? null,
                        'is_active' => $locationData['is_active'] ?? true,
                        'sort_order' => $locationData['sort_order'] ?? 0,
                    ]);
                    $existingIds[] = $location->id;
                }
            }

            // Remove locations that were deleted
            PickupLocation::whereNotIn('id', $existingIds)->delete();
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
