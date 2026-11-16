<?php

namespace App\Http\Controllers;

use App\Support\Retention;
use App\Support\Settings;
use Inertia\Inertia;
use Inertia\Response;

/** Privacy policy, cookie policy and terms — filled in from Settings → Legal & business. */
class LegalController extends Controller
{
    public function __construct(private readonly Settings $settings) {}

    public function privacy(): Response
    {
        return Inertia::render('Legal/Privacy', [...$this->shared(), 'retention' => [
            'enquiry_months' => Retention::ENQUIRY_MONTHS,
            'enquiry_ip_days' => Retention::ENQUIRY_IP_DAYS,
            'unused_invite_months' => Retention::UNUSED_INVITE_MONTHS,
            'financial_years' => Retention::FINANCIAL_YEARS,
            'log_days' => Retention::LOG_DAYS,
        ]]);
    }

    public function cookies(): Response
    {
        return Inertia::render('Legal/Cookies', [...$this->shared(), 'cookies' => $this->cookieTable()]);
    }

    public function terms(): Response
    {
        return Inertia::render('Legal/Terms', $this->shared());
    }

    private function shared(): array
    {
        $s = $this->settings;

        return [
            'business' => [
                'trading_name' => $s->get('business_name') ?: $s->get('site_name'),
                'legal_name' => $s->get('legal_name'),
                'entity' => $s->get('legal_entity', 'Sole trader'),
                'company_number' => $s->get('company_number'),
                'vat_number' => $s->get('vat_number'),
                'ico_number' => $s->get('ico_number'),
                'address' => $s->get('business_address'),
                'email' => $s->get('privacy_email') ?: $s->get('contact_email'),
                'contact_email' => $s->get('contact_email'),
                'hosting' => $s->get('hosting_provider'),
                'jurisdiction' => $s->get('legal_jurisdiction', 'England and Wales'),
                'updated' => $s->get('legal_updated'),
            ],
            'analytics' => filled($s->get('google_analytics_id')),
            'discord_alerts' => filled($s->get('discord_webhook_url')),
        ];
    }

    /** Every cookie the site can set, generated from the real configuration. */
    private function cookieTable(): array
    {
        $session = (int) config('session.lifetime');
        $rows = [
            ['name' => config('session.cookie'), 'purpose' => 'Keeps you signed in and remembers form state between pages.', 'duration' => "{$session} minutes", 'type' => 'Strictly necessary'],
            ['name' => 'XSRF-TOKEN', 'purpose' => 'Protects forms against cross-site request forgery.', 'duration' => "{$session} minutes", 'type' => 'Strictly necessary'],
            ['name' => 'remember_web_*', 'purpose' => 'Keeps the admin or client portal signed in on this device. Only set when you sign in.', 'duration' => '400 days', 'type' => 'Strictly necessary'],
        ];

        if (filled($this->settings->get('google_analytics_id'))) {
            $rows[] = ['name' => 'cookie_consent', 'purpose' => 'Remembers whether you accepted or rejected analytics cookies.', 'duration' => '6 months', 'type' => 'Strictly necessary'];
            $rows[] = ['name' => '_ga, _ga_*', 'purpose' => 'Google Analytics: counts visits and how pages are used, so we can improve the site. Only set if you accept.', 'duration' => '2 years', 'type' => 'Analytics (optional)'];
        }

        return $rows;
    }
}
