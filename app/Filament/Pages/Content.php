<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\Content as SiteContent;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;

class Content extends Page
{
    use InteractsWithFormActions;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Content';
    protected static ?string $navigationGroup = 'Website';
    protected static ?int $navigationSort = 90;
    protected static string $view = 'filament.pages.content';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(collect(SiteContent::defaults())
            ->mapWithKeys(function (mixed $default, string $key): array {
                $value = SiteContent::get($key, $default);

                if ($key === 'social_links') {
                    $value = $this->normalizeSocialLinks($value);
                }

                return [$key => $value];
            })
            ->all());
    }

    protected function normalizeSocialLinks(mixed $links): array
    {
        if (! is_array($links)) {
            return [];
        }

        return collect($links)
            ->filter(fn (mixed $link): bool => is_array($link))
            ->map(function (array $link): array {
                if (filled($link['platform'] ?? null)) {
                    return $link;
                }

                $label = strtolower((string) ($link['label'] ?? ''));
                $platforms = SiteContent::socialPlatforms();

                foreach ($platforms as $platform => $name) {
                    if ($label === strtolower($name)) {
                        $link['platform'] = $platform;
                        break;
                    }
                }

                return $link;
            })
            ->values()
            ->all();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Content')
                    ->tabs([
                        Tabs\Tab::make('Home')
                            ->schema([
                                Section::make('Hero')
                                    ->schema([
                                        TextInput::make('home_hero_eyebrow')->label('Eyebrow')->required(),
                                        Textarea::make('home_hero_title')->label('Title')->rows(2)->required(),
                                        Textarea::make('home_hero_description')->label('Description')->rows(3)->required(),
                                        TextInput::make('home_hero_primary_label')->label('Primary button label'),
                                        TextInput::make('home_hero_primary_url')->label('Primary button URL'),
                                        TextInput::make('home_hero_secondary_label')->label('Secondary button label'),
                                        TextInput::make('home_hero_secondary_url')->label('Secondary button URL'),
                                    ])->columns(2),
                                Section::make('Services teaser')
                                    ->schema([
                                        TextInput::make('home_services_eyebrow')->label('Eyebrow')->required(),
                                        TextInput::make('home_services_title')->label('Title')->required(),
                                    ])->columns(2),
                                Section::make('Showroom')
                                    ->schema([
                                        TextInput::make('showroom_eyebrow')->label('Eyebrow'),
                                        TextInput::make('showroom_title')->label('Title'),
                                        Textarea::make('showroom_description')->label('Description')->rows(3)->columnSpanFull(),
                                        Textarea::make('showroom_extra_description')->label('Second paragraph')->rows(3)->columnSpanFull(),
                                        Textarea::make('showroom_map_embed')->label('Google Maps embed URL')->rows(3)->columnSpanFull(),
                                        TextInput::make('showroom_map_url')->label('Google Maps link')->columnSpanFull(),
                                    ])->columns(2),
                            ]),
                        Tabs\Tab::make('Services')
                            ->schema([
                                Section::make('Services page')
                                    ->schema([
                                        TextInput::make('services_page_eyebrow')->label('Eyebrow'),
                                        TextInput::make('services_page_title')->label('Title'),
                                        Textarea::make('services_page_description')->label('Description')->rows(3)->columnSpanFull(),
                                    ])->columns(2),
                                Section::make('Service cards')
                                    ->schema([
                                        TextInput::make('service_1_title')->label('Service 1 title'),
                                        Textarea::make('service_1_description')->label('Service 1 description')->rows(3),
                                        TextInput::make('service_2_title')->label('Service 2 title'),
                                        Textarea::make('service_2_description')->label('Service 2 description')->rows(3),
                                        TextInput::make('service_3_title')->label('Service 3 title'),
                                        Textarea::make('service_3_description')->label('Service 3 description')->rows(3),
                                    ])->columns(2),
                            ]),
                        Tabs\Tab::make('Contact')
                            ->schema([
                                Section::make('Contact page')
                                    ->schema([
                                        TextInput::make('contact_page_eyebrow')->label('Eyebrow'),
                                        TextInput::make('contact_page_title')->label('Title'),
                                        Textarea::make('contact_page_description')->label('Description')->rows(3)->columnSpanFull(),
                                        TextInput::make('site_email')->label('Email'),
                                        TextInput::make('site_phone')->label('Phone'),
                                        Textarea::make('site_address')->label('Address')->rows(3)->columnSpanFull(),
                                    ])->columns(2),
                            ]),
                        Tabs\Tab::make('Footer & Social')
                            ->schema([
                                Section::make('Footer')
                                    ->schema([
                                        Textarea::make('footer_brand_text')->label('Brand text')->rows(3)->columnSpanFull(),
                                        Repeater::make('footer_links')
                                            ->label('Footer links')
                                            ->schema([
                                                TextInput::make('label')->required(),
                                                TextInput::make('url')->required(),
                                            ])
                                            ->columns(2)
                                            ->columnSpanFull(),
                                        Repeater::make('social_links')
                                            ->label('Social media links')
                                            ->schema([
                                                Select::make('platform')
                                                    ->label('Platform')
                                                    ->options(SiteContent::socialPlatforms())
                                                    ->searchable()
                                                    ->required(),
                                                TextInput::make('url')->required(),
                                            ])
                                            ->itemLabel(fn (?array $state): ?string => SiteContent::socialPlatforms()[$state['platform'] ?? ''] ?? null)
                                            ->columns(2)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tabs\Tab::make('Legal')
                            ->schema([
                                RichEditor::make('terms_content')
                                    ->label('Terms & Conditions')
                                    ->columnSpanFull(),
                                RichEditor::make('privacy_policy_content')
                                    ->label('Privacy Policy')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $value) {
            $group = str_starts_with($key, 'site_') ? 'general' : 'content';
            Setting::set($key, $value, $group);
        }

        Notification::make()
            ->title('Contenuti salvati con successo.')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Salva contenuti')
                ->submit('save'),
        ];
    }
}
