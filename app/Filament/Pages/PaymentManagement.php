<?php

namespace App\Filament\Pages;

use App\Models\PaymentMethodConfig;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Actions\Action;

class PaymentManagement extends Page implements HasForms
{
    use InteractsWithFormActions;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Shop Management';
    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.payment-management';

    public ?array $data = [];

    public function mount(): void
    {
        $config = PaymentMethodConfig::getInstance();

        $this->form->fill([
            // Payment method toggles
            'payment_fawaterak_enabled' => $config->payment_fawaterak_enabled,
            'payment_stripe_enabled' => $config->payment_stripe_enabled,
            'payment_paypal_enabled' => $config->payment_paypal_enabled,
            'payment_bank_transfer_enabled' => $config->payment_bank_transfer_enabled,
            'payment_cash_on_delivery_enabled' => $config->payment_cash_on_delivery_enabled,

            // Stripe credentials
            'stripe_secret_key' => $config->stripe_secret_key,
            'stripe_publishable_key' => $config->stripe_publishable_key,
            'stripe_webhook_secret' => $config->stripe_webhook_secret,

            // PayPal credentials
            'paypal_client_id' => $config->paypal_client_id,
            'paypal_client_secret' => $config->paypal_client_secret,
            'paypal_mode' => $config->paypal_mode,

            // Fawaterak credentials
            'fawaterak_api_key' => $config->fawaterak_api_key,
            'fawaterak_secret_key' => $config->fawaterak_secret_key,
            'fawaterak_base_url' => $config->fawaterak_base_url,

            // Debt settings
            'global_debt_limit' => $config->global_debt_limit,
            'enable_debt_limits' => $config->enable_debt_limits,
            'debt_review_period_days' => $config->debt_review_period_days,

            // Installment settings
            'max_installments' => $config->max_installments,
            'min_installment_amount' => $config->min_installment_amount,
            'installment_interest_rate' => $config->installment_interest_rate,
            'enable_installments' => $config->enable_installments,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Payment Method Toggles
                Section::make('Metodi di Pagamento')
                    ->description('Abilita o disabilita i metodi di pagamento disponibili per i clienti.')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2, 'lg' => 3])->schema([
                            Toggle::make('payment_fawaterak_enabled')
                                ->label('Fawaterak')
                                ->helperText('Pagamento sicuro via Fawaterak')
                                ->inline(false),

                            Toggle::make('payment_stripe_enabled')
                                ->label('Carta di Credito (Stripe)')
                                ->helperText('Visa, Mastercard, American Express')
                                ->inline(false),

                            Toggle::make('payment_paypal_enabled')
                                ->label('PayPal')
                                ->helperText('PayPal account e carte')
                                ->inline(false),

                            Toggle::make('payment_bank_transfer_enabled')
                                ->label('Bonifico Bancario')
                                ->helperText('Pagamento anticipato tramite bonifico')
                                ->inline(false),

                            Toggle::make('payment_cash_on_delivery_enabled')
                                ->label('Contrassegno')
                                ->helperText('Pagamento alla consegna')
                                ->inline(false),
                        ])->columns(3),
                    ]),

                // Stripe Configuration
                Section::make('Configurazione Stripe')
                    ->description('Credenziali API Stripe per i pagamenti con carta.')
                    ->schema([
                        TextInput::make('stripe_publishable_key')
                            ->label('Stripe Publishable Key')
                            ->password()
                            ->revealable()
                            ->helperText('Trovabile nel pannello Stripe Dashboard')
                            ->maxLength(255),

                        TextInput::make('stripe_secret_key')
                            ->label('Stripe Secret Key')
                            ->password()
                            ->revealable()
                            ->helperText('Mantenere segreta questa chiave')
                            ->maxLength(255),

                        TextInput::make('stripe_webhook_secret')
                            ->label('Stripe Webhook Secret')
                            ->password()
                            ->revealable()
                            ->helperText('Per la verifica degli eventi webhook')
                            ->maxLength(255),
                    ])->columns(1),

                // PayPal Configuration
                Section::make('Configurazione PayPal')
                    ->description('Credenziali API PayPal per i pagamenti.')
                    ->schema([
                        TextInput::make('paypal_client_id')
                            ->label('PayPal Client ID')
                            ->password()
                            ->revealable()
                            ->helperText('ID client dalla dashboard PayPal')
                            ->maxLength(255),

                        TextInput::make('paypal_client_secret')
                            ->label('PayPal Client Secret')
                            ->password()
                            ->revealable()
                            ->helperText('Secret dalla dashboard PayPal')
                            ->maxLength(255),

                        Select::make('paypal_mode')
                            ->label('Modalità PayPal')
                            ->helperText('Sandbox per test, Live per produzione')
                            ->default('sandbox')
                            ->options([
                                'sandbox' => 'Sandbox (Test)',
                                'live' => 'Live (Produzione)',
                            ]),
                    ])->columns(1),

                // Fawaterak Configuration
                Section::make('Configurazione Fawaterak')
                    ->description('Credenziali API Fawaterak per i pagamenti.')
                    ->schema([
                        TextInput::make('fawaterak_api_key')
                            ->label('Fawaterak API Key')
                            ->password()
                            ->revealable()
                            ->helperText('API Key da Fawaterak')
                            ->maxLength(255),

                        TextInput::make('fawaterak_secret_key')
                            ->label('Fawaterak Secret Key')
                            ->password()
                            ->revealable()
                            ->helperText('Secret Key da Fawaterak')
                            ->maxLength(255),

                        TextInput::make('fawaterak_base_url')
                            ->label('Fawaterak Base URL')
                            ->helperText('URL API Fawaterak (default: https://api.fawaterak.com)')
                            ->default('https://api.fawaterak.com')
                            ->maxLength(255),
                    ])->columns(1),

                // Customer Debt Settings
                Section::make('Gestione Debito Clienti')
                    ->description('Configurazione limiti di debito e credito per i clienti.')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])->schema([
                            Toggle::make('enable_debt_limits')
                                ->label('Abilita Limiti di Debito')
                                ->helperText('Attiva il controllo dei limiti di debito per i clienti')
                                ->inline(false),

                            TextInput::make('global_debt_limit')
                                ->label('Limite Debito Globale (€)')
                                ->numeric()
                                ->default(10000.00)
                                ->minValue(0)
                                ->step(0.01)
                                ->helperText('Limite di debito predefinito per tutti i clienti'),
                        ])->columns(2),

                        TextInput::make('debt_review_period_days')
                            ->label('Periodo di Revisione Debito (giorni)')
                            ->numeric()
                            ->default(30)
                            ->minValue(1)
                            ->helperText('Numero di giorni tra le revisioni del debito del cliente'),
                    ])->columns(2),

                // Installment Policy Settings
                Section::make('Politiche di Rateizzazione')
                    ->description('Configurazione globale per le rateizzazioni degli ordini.')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])->schema([
                            Toggle::make('enable_installments')
                                ->label('Abilita Rateizzazione')
                                ->helperText('Permette ai clienti di rateizzare gli ordini')
                                ->inline(false),

                            TextInput::make('max_installments')
                                ->label('Numero Massimo di Rate')
                                ->numeric()
                                ->default(12)
                                ->minValue(1)
                                ->maxValue(36)
                                ->helperText('Numero massimo di rate consentite'),
                        ])->columns(2),

                        Grid::make(['default' => 1, 'md' => 2])->schema([
                            TextInput::make('min_installment_amount')
                                ->label('Importo Minimo per Rateizzazione (€)')
                                ->numeric()
                                ->default(50.00)
                                ->minValue(0)
                                ->step(0.01)
                                ->helperText('Ordine minimo per essere rateizzato'),

                            TextInput::make('installment_interest_rate')
                                ->label('Tasso di Interesse (%)')
                                ->numeric()
                                ->default(0.00)
                                ->minValue(0)
                                ->maxValue(100)
                                ->step(0.01)
                                ->helperText('Tasso di interesse aggiuntivo per rateizzazione'),
                        ])->columns(2),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Validate at least one payment method is enabled
        $enabledMethods = array_filter([
            'fawaterak' => $data['payment_fawaterak_enabled'],
            'stripe' => $data['payment_stripe_enabled'],
            'paypal' => $data['payment_paypal_enabled'],
            'bank_transfer' => $data['payment_bank_transfer_enabled'],
            'cash_on_delivery' => $data['payment_cash_on_delivery_enabled'],
        ]);

        if (empty($enabledMethods)) {
            Notification::make()
                ->title('Errore')
                ->body('Almeno un metodo di pagamento deve essere abilitato.')
                ->danger()
                ->send();
            return;
        }

        // Update payment configuration
        $config = PaymentMethodConfig::getInstance();

        // Update all settings
        $fillable = $config->getFillable();
        foreach ($data as $key => $value) {
            if (in_array($key, $fillable)) {
                $config->$key = $value;
            }
        }

        $saved = $config->save();

        if ($saved) {
            Notification::make()
                ->title('Impostazioni salvate')
                ->body('Le impostazioni di pagamento sono state aggiornate con successo.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Errore')
                ->body('Impossibile salvare le impostazioni. Riprova.')
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Salva Impostazioni')
                ->submit('save')
                ->color('success')
                ->icon('heroicon-o-check'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return $this->getFormActions();
    }

    /**
     * Get enabled payment methods count for dashboard display
     */
    public function getEnabledMethodsCount(): int
    {
        $config = PaymentMethodConfig::getInstance();
        return count($config->getEnabledPaymentMethods());
    }

    /**
     * Get payment methods status for display
     */
    public function getPaymentMethodsStatus(): array
    {
        $config = PaymentMethodConfig::getInstance();

        return [
            'fawaterak' => [
                'enabled' => $config->payment_fawaterak_enabled,
                'has_credentials' => $config->hasCredentials('fawaterak'),
            ],
            'stripe' => [
                'enabled' => $config->payment_stripe_enabled,
                'has_credentials' => $config->hasCredentials('stripe'),
            ],
            'paypal' => [
                'enabled' => $config->payment_paypal_enabled,
                'has_credentials' => $config->hasCredentials('paypal'),
            ],
            'bank_transfer' => [
                'enabled' => $config->payment_bank_transfer_enabled,
                'has_credentials' => true, // Bank transfer doesn't need credentials
            ],
            'cash_on_delivery' => [
                'enabled' => $config->payment_cash_on_delivery_enabled,
                'has_credentials' => true, // Cash on delivery doesn't need credentials
            ],
        ];
    }
}