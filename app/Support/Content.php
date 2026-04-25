<?php

namespace App\Support;

use App\Models\Setting;

class Content
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default ?? static::defaults()[$key] ?? null);
    }

    public static function defaults(): array
    {
        return [
            'site_name' => 'SweetHome Milano',
            'site_email' => 'info@sweethome.it',
            'site_phone' => '328 899 5628',
            'site_address' => 'Via Carlo Espinasse 104/a, 20156 Milano MI',
            'site_logo' => null,
            'site_favicon' => null,
            'home_hero_eyebrow' => 'SweetHome Milano',
            'home_hero_title' => "IL LUSSO\nDI ABITARE.",
            'home_hero_description' => "Trasformiamo i tuoi spazi in ambienti unici e raffinati. Design d'interni di lusso, arredamento su misura e consulenza personalizzata.",
            'home_hero_primary_label' => 'Scopri la Collezione',
            'home_hero_primary_url' => '/shop',
            'home_hero_secondary_label' => 'I Nostri Progetti',
            'home_hero_secondary_url' => '/projects',
            'home_services_eyebrow' => 'Cosa Offriamo',
            'home_services_title' => 'I nostri servizi',
            'service_1_title' => 'Progettazione',
            'service_1_description' => 'Creiamo progetti su misura che riflettono il tuo stile di vita, ottimizzando ogni spazio con eleganza e funzionalita.',
            'service_2_title' => 'Arredamento Completo',
            'service_2_description' => "Dalla selezione dei materiali all'installazione finale, ci occupiamo di ogni aspetto per un risultato impeccabile.",
            'service_3_title' => 'Consulenza Personalizzata',
            'service_3_description' => "I nostri esperti ti guidano nella scelta dei materiali, colori e finiture per creare l'ambiente perfetto.",
            'showroom_eyebrow' => 'Dove Siamo',
            'showroom_title' => 'Visita il nostro showroom Sweet Home',
            'showroom_description' => 'Ti aspettiamo presso Tecnocasa24 - Showroom Sweet Home, uno spazio dedicato alla scelta di arredi, finiture e soluzioni per trasformare ogni ambiente con stile e funzionalita.',
            'showroom_extra_description' => 'Nel nostro showroom puoi scoprire da vicino materiali, colori e composizioni, ricevendo una consulenza personalizzata per progettare la tua casa con attenzione a ogni dettaglio.',
            'showroom_map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2796.5239225452415!2d9.140883199999998!3d45.499530899999996!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4786c100754f1773%3A0x91dce3f2838cab1d!2sTecnocasa24%20-%20Showroom%20Sweet%20Home!5e0!3m2!1sen!2seg!4v1777110719073!5m2!1sen!2seg',
            'showroom_map_url' => 'https://www.google.com/maps/search/?api=1&query=Tecnocasa24%20-%20Showroom%20Sweet%20Home',
            'services_page_eyebrow' => 'Servizi',
            'services_page_title' => 'Soluzioni complete per la tua casa',
            'services_page_description' => 'Dalla progettazione alla scelta dei materiali, accompagniamo ogni cliente con un percorso chiaro, elegante e personalizzato.',
            'contact_page_eyebrow' => 'Contattaci',
            'contact_page_title' => 'Parliamo del tuo progetto',
            'contact_page_description' => 'Raccontaci cosa desideri realizzare. Il nostro team ti ricontattera per una consulenza dedicata.',
            'footer_brand_text' => "Design d'interni di lusso per chi cerca l'eccellenza nell'arredamento.",
            'footer_links' => [
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'Shop', 'url' => '/shop'],
                ['label' => 'Servizi', 'url' => '/services'],
                ['label' => 'Contatti', 'url' => '/contact'],
            ],
            'social_links' => [
                ['platform' => 'instagram', 'url' => '#'],
                ['platform' => 'facebook', 'url' => '#'],
            ],
            'terms_content' => '<h1>Termini e Condizioni</h1><p>Inserisci qui i termini e le condizioni del sito.</p>',
            'privacy_policy_content' => '<h1>Privacy Policy</h1><p>Inserisci qui la privacy policy del sito.</p>',
        ];
    }

    public static function socialPlatforms(): array
    {
        return [
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'x' => 'X',
            'linkedin' => 'LinkedIn',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok',
            'pinterest' => 'Pinterest',
            'whatsapp' => 'WhatsApp',
            'telegram' => 'Telegram',
            'snapchat' => 'Snapchat',
            'threads' => 'Threads',
            'github' => 'GitHub',
            'behance' => 'Behance',
            'dribbble' => 'Dribbble',
        ];
    }
}
