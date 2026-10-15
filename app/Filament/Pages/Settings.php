<?php

namespace App\Filament\Pages;

use App\Services\Integrations\AfricasTalkingClient;
use App\Services\Integrations\ConnectionResult;
use App\Services\Integrations\DarajaClient;
use App\Services\Integrations\PesapalClient;
use App\Support\Settings\SettingsRegistry;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Throwable;
use UnitEnum;

/**
 * System settings: business details, payment providers, email, SMS, analytics.
 * Super admins only, since it holds payment credentials.
 */
class Settings extends Page
{
    protected string $view = 'filament.pages.settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'System settings';

    protected static ?string $navigationLabel = 'Settings';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    public function mount(): void
    {
        $values = [];

        foreach (array_keys(SettingsRegistry::definitions()) as $key) {
            // Secrets are never sent to the browser; a blank field keeps the saved value.
            $values[$key] = SettingsRegistry::isSecret($key) ? null : SettingsRegistry::current($key);
        }

        $this->form->fill(Arr::undot($values));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('settings')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Business')
                            ->icon(Heroicon::OutlinedBuildingStorefront)
                            ->schema([
                                Section::make('Contact details')
                                    ->description('Shown in the website footer, contact page and order confirmations.')
                                    ->schema([
                                        TextInput::make('contact.phone')->label('Phone')->tel(),
                                        TextInput::make('contact.whatsapp')->label('WhatsApp number')->tel()->placeholder('2547XXXXXXXX'),
                                        TextInput::make('contact.email')->label('Sales email')->email(),
                                        TextInput::make('contact.showroom')->label('Showroom'),
                                        TextInput::make('contact.logistics_hub')->label('Logistics hub'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Payments')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->schema([
                                Section::make('Checkout options for individuals')
                                    ->description('Businesses always check out with a quotation and invoice.')
                                    ->schema([
                                        Toggle::make('payments.pay_on_delivery')->label('Pay on delivery'),
                                        Toggle::make('payments.mpesa_manual')->label('M-Pesa (customer enters confirmation code)')->live(),
                                        TextInput::make('mpesa.paybill')->label('Paybill number')
                                            ->visible(fn (Get $get): bool => (bool) $get('payments.mpesa_manual')),
                                        TextInput::make('mpesa.account')->label('Paybill account name')
                                            ->visible(fn (Get $get): bool => (bool) $get('payments.mpesa_manual')),
                                        TextInput::make('mpesa.till')->label('or Till number (Buy Goods)')
                                            ->visible(fn (Get $get): bool => (bool) $get('payments.mpesa_manual')),
                                    ])
                                    ->columns(2),

                                Section::make('M-Pesa Daraja API')
                                    ->description('Safaricom developer credentials from developer.safaricom.co.ke, for automatic M-Pesa (STK push) payments.')
                                    ->icon(Heroicon::OutlinedDevicePhoneMobile)
                                    ->schema([
                                        Toggle::make('daraja.enabled')->label('Enable Daraja')->columnSpanFull(),
                                        Select::make('daraja.environment')->label('Environment')
                                            ->options(['sandbox' => 'Sandbox (testing)', 'production' => 'Production (live)'])
                                            ->selectablePlaceholder(false),
                                        Select::make('daraja.transaction_type')->label('Account type')
                                            ->options(['CustomerPayBillOnline' => 'Paybill', 'CustomerBuyGoodsOnline' => 'Till (Buy Goods)'])
                                            ->selectablePlaceholder(false),
                                        self::secret('daraja.consumer_key', 'Consumer key'),
                                        self::secret('daraja.consumer_secret', 'Consumer secret'),
                                        TextInput::make('daraja.shortcode')->label('Business shortcode'),
                                        TextInput::make('daraja.till_number')->label('Till number (Buy Goods only)'),
                                        self::secret('daraja.passkey', 'Lipa na M-Pesa passkey')->columnSpanFull(),
                                        Actions::make([self::testAction('testDaraja', 'Test Daraja connection', fn () => app(DarajaClient::class)->testConnection())]),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),

                                Section::make('Pesapal')
                                    ->description('Card, M-Pesa and Airtel Money payments through Pesapal. Keys from your Pesapal merchant dashboard.')
                                    ->icon(Heroicon::OutlinedCreditCard)
                                    ->schema([
                                        Toggle::make('pesapal.enabled')->label('Enable Pesapal')->columnSpanFull(),
                                        Select::make('pesapal.environment')->label('Environment')
                                            ->options(['sandbox' => 'Sandbox (testing)', 'production' => 'Production (live)'])
                                            ->selectablePlaceholder(false),
                                        TextInput::make('pesapal.ipn_id')->label('IPN ID')
                                            ->helperText('Issued when the payment-notification URL is registered with Pesapal.'),
                                        self::secret('pesapal.consumer_key', 'Consumer key'),
                                        self::secret('pesapal.consumer_secret', 'Consumer secret'),
                                        Actions::make([self::testAction('testPesapal', 'Test Pesapal connection', fn () => app(PesapalClient::class)->testConnection())]),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),

                                Callout::make('Online payments at checkout')
                                    ->description('These credentials are verified and stored securely. Taking live Daraja (STK push) and Pesapal payments at checkout is the next build step; until then customers use pay on delivery or manual M-Pesa.')
                                    ->info(),
                            ]),

                        Tab::make('Email')
                            ->icon(Heroicon::OutlinedEnvelope)
                            ->schema([
                                Section::make('Outgoing email (SMTP)')
                                    ->description('Use the mailbox details from cPanel > Email Accounts > Connect Devices.')
                                    ->schema([
                                        Toggle::make('mail.enabled')->label('Send email through SMTP')->columnSpanFull()
                                            ->helperText('When off, emails are written to the log file instead of being sent.'),
                                        TextInput::make('mail.host')->label('SMTP host')->placeholder('mail.softia.co.ke'),
                                        TextInput::make('mail.port')->label('Port')->numeric()->placeholder('465'),
                                        TextInput::make('mail.username')->label('Username'),
                                        self::secret('mail.password', 'Password'),
                                        TextInput::make('mail.from_address')->label('From address')->email(),
                                        TextInput::make('mail.from_name')->label('From name'),
                                        Actions::make([
                                            Action::make('sendTestEmail')
                                                ->label('Send test email to me')
                                                ->icon(Heroicon::OutlinedPaperAirplane)
                                                ->color('gray')
                                                ->action(fn () => $this->sendTestEmail()),
                                        ]),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('SMS')
                            ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                            ->schema([
                                Section::make("Africa's Talking")
                                    ->description('For order and delivery SMS notifications.')
                                    ->schema([
                                        Select::make('sms.environment')->label('Environment')
                                            ->options(['sandbox' => 'Sandbox (testing)', 'production' => 'Production (live)'])
                                            ->selectablePlaceholder(false),
                                        TextInput::make('sms.username')->label('Username'),
                                        self::secret('sms.api_key', 'API key'),
                                        TextInput::make('sms.sender_id')->label('Sender ID')->placeholder('SOFTIA'),
                                        Actions::make([self::testAction('testSms', 'Test SMS connection', fn () => app(AfricasTalkingClient::class)->testConnection())]),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Analytics')
                            ->icon(Heroicon::OutlinedChartBar)
                            ->schema([
                                Section::make('Tracking')
                                    ->description('Tracking code is added to every storefront page (not the admin).')
                                    ->schema([
                                        TextInput::make('analytics.ga4_id')->label('Google Analytics 4 measurement ID')->placeholder('G-XXXXXXXXXX')
                                            ->regex('/^G-[A-Z0-9]+$/'),
                                        TextInput::make('analytics.meta_pixel_id')->label('Meta (Facebook) Pixel ID')
                                            ->regex('/^\d+$/'),
                                    ])
                                    ->columns(2),
                            ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        SettingsRegistry::save(Arr::dot($this->form->getState()));

        Notification::make()->title('Settings saved')->success()->send();
    }

    private static function secret(string $key, string $label): TextInput
    {
        return TextInput::make($key)
            ->label($label)
            ->password()
            ->revealable()
            ->autocomplete('new-password')
            ->placeholder(fn (): string => filled(SettingsRegistry::current($key)) ? '•••••••• saved' : '')
            ->helperText(fn (): ?string => filled(SettingsRegistry::current($key)) ? 'Saved. Leave blank to keep it.' : null);
    }

    /**
     * Tests with the values currently in the form, before they are saved.
     *
     * @param  callable(): ConnectionResult  $test
     */
    private static function testAction(string $name, string $label, callable $test): Action
    {
        return Action::make($name)
            ->label($label)
            ->icon(Heroicon::OutlinedSignal)
            ->color('gray')
            ->action(function (Settings $livewire) use ($test): void {
                $livewire->applyUnsavedToConfig();
                $result = $test();

                Notification::make()
                    ->title($result->ok ? 'Connection successful' : 'Connection failed')
                    ->body($result->message)
                    ->{$result->ok ? 'success' : 'danger'}()
                    ->send();
            });
    }

    public function applyUnsavedToConfig(): void
    {
        $values = Arr::dot($this->data ?? []);

        foreach (SettingsRegistry::definitions() as $key => $definition) {
            if (! array_key_exists($key, $values) || blank($values[$key])) {
                continue;
            }

            config([$definition['config'] => $values[$key]]);
        }
    }

    private function sendTestEmail(): void
    {
        $this->applyUnsavedToConfig();

        if (Arr::get($this->data, 'mail.enabled')) {
            config(['mail.default' => 'smtp']);
        }

        try {
            Mail::raw('This is a test email from the Softia admin. Your email settings work.', function ($message): void {
                $message->to(auth()->user()->email)->subject('Softia test email');
            });

            Notification::make()
                ->title(config('mail.default') === 'smtp' ? 'Test email sent to '.auth()->user()->email : 'SMTP is off: the email was written to the log')
                ->success()
                ->send();
        } catch (Throwable $e) {
            Notification::make()->title('Email failed')->body($e->getMessage())->danger()->send();
        }
    }
}
