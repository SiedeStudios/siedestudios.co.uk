<?php

/*
|--------------------------------------------------------------------------
| Site settings schema
|--------------------------------------------------------------------------
|
| Every editable setting in the admin panel is declared here. Values are
| stored in the `settings` table; these definitions supply the admin form
| layout, validation rules and defaults. Fields marked `public` are shared
| with every page, so never mark secrets as public.
|
*/

return [

    'admin_discord_ids' => array_values(array_filter(array_map('trim', explode(',', (string) env('ADMIN_DISCORD_IDS', ''))))),

    'currencies' => ['GBP', 'USD', 'EUR', 'CAD', 'AUD'],

    'groups' => [

        'general' => [
            'label' => 'General',
            'description' => 'Identity and copy used across the public site.',
            'fields' => [
                'site_name' => ['type' => 'text', 'label' => 'Site name', 'default' => 'SiedeStudios', 'rules' => ['required', 'string', 'max:60'], 'public' => true],
                'site_tagline' => ['type' => 'text', 'label' => 'Tagline', 'default' => 'Web platforms & game systems, built to feel effortless.', 'rules' => ['nullable', 'string', 'max:140'], 'public' => true],
                'site_description' => ['type' => 'textarea', 'label' => 'Meta description', 'default' => 'SiedeStudios is an independent UK studio building Laravel web platforms, UI/UX and custom Garry\'s Mod / Lua systems.', 'rules' => ['nullable', 'string', 'max:300'], 'public' => true],
                'hero_intro' => ['type' => 'textarea', 'label' => 'Home hero intro', 'default' => 'An independent studio building web platforms, interfaces and game-server systems that hold up under real traffic and real players.', 'rules' => ['nullable', 'string', 'max:400'], 'public' => true],
                'is_available' => ['type' => 'toggle', 'label' => 'Taking new work', 'default' => '1', 'rules' => ['boolean'], 'public' => true],
                'availability' => ['type' => 'text', 'label' => 'Availability text', 'default' => 'Booking projects for Q1 2027', 'rules' => ['nullable', 'string', 'max:80'], 'public' => true],
                'location' => ['type' => 'text', 'label' => 'Location', 'default' => 'United Kingdom', 'rules' => ['nullable', 'string', 'max:80'], 'public' => true],
                'founded_year' => ['type' => 'text', 'label' => 'Founded', 'default' => '2020', 'rules' => ['nullable', 'string', 'max:10'], 'public' => true],
            ],
        ],

        'branding' => [
            'label' => 'Branding',
            'description' => 'Logo, favicon and social preview image. Leave the logo empty to use the built-in SiedeStudios mark.',
            'fields' => [
                'logo' => ['type' => 'image', 'label' => 'Site logo', 'default' => null, 'rules' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'], 'public' => true, 'help' => 'Wide logo for the header. SVG or PNG recommended.'],
                'favicon' => ['type' => 'image', 'label' => 'Favicon', 'default' => null, 'rules' => ['nullable', 'file', 'mimes:png,ico,svg', 'max:512'], 'public' => true, 'help' => 'Square PNG, ICO or SVG.'],
                'og_image' => ['type' => 'image', 'label' => 'Social share image', 'default' => null, 'rules' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:4096'], 'public' => true, 'help' => '1200×630 works best.'],
                'accent_color' => ['type' => 'color', 'label' => 'Accent colour', 'default' => '#DC143C', 'rules' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'public' => true],
            ],
        ],

        'contact' => [
            'label' => 'Contact & social',
            'description' => 'Where people can reach you.',
            'fields' => [
                'contact_email' => ['type' => 'email', 'label' => 'Contact email', 'default' => 'hello@siedestudios.co.uk', 'rules' => ['nullable', 'email', 'max:120'], 'public' => true],
                'response_time' => ['type' => 'text', 'label' => 'Response time', 'default' => 'Usually replies within 24 hours', 'rules' => ['nullable', 'string', 'max:80'], 'public' => true],
                'discord_invite' => ['type' => 'url', 'label' => 'Discord invite URL', 'default' => null, 'rules' => ['nullable', 'url', 'max:200'], 'public' => true],
                'discord_handle' => ['type' => 'text', 'label' => 'Discord username', 'default' => null, 'rules' => ['nullable', 'string', 'max:60'], 'public' => true],
                'github_url' => ['type' => 'url', 'label' => 'GitHub URL', 'default' => null, 'rules' => ['nullable', 'url', 'max:200'], 'public' => true],
                'gmodstore_url' => ['type' => 'url', 'label' => 'GmodStore URL', 'default' => null, 'rules' => ['nullable', 'url', 'max:200'], 'public' => true],
                'twitter_url' => ['type' => 'url', 'label' => 'X / Twitter URL', 'default' => null, 'rules' => ['nullable', 'url', 'max:200'], 'public' => true],
                'youtube_url' => ['type' => 'url', 'label' => 'YouTube URL', 'default' => null, 'rules' => ['nullable', 'url', 'max:200'], 'public' => true],
            ],
        ],

        'payments' => [
            'label' => 'Payments',
            'description' => 'PayPal Standard checkout with IPN verification for invoices.',
            'fields' => [
                'paypal_email' => ['type' => 'email', 'label' => 'PayPal business email', 'default' => null, 'rules' => ['nullable', 'email', 'max:120'], 'help' => 'Payments are only accepted when the IPN receiver matches this address.'],
                'paypal_sandbox' => ['type' => 'toggle', 'label' => 'Sandbox mode', 'default' => '0', 'rules' => ['boolean'], 'help' => 'Use PayPal sandbox for checkout and IPN verification.'],
                'currency' => ['type' => 'select', 'label' => 'Default currency', 'default' => 'GBP', 'options' => 'currencies', 'rules' => ['required', 'string', 'size:3'], 'public' => true],
                'invoice_prefix' => ['type' => 'text', 'label' => 'Invoice prefix', 'default' => 'SS', 'rules' => ['required', 'alpha_num', 'max:6']],
                'business_name' => ['type' => 'text', 'label' => 'Business name on invoices', 'default' => 'SiedeStudios', 'rules' => ['nullable', 'string', 'max:120']],
                'business_address' => ['type' => 'textarea', 'label' => 'Business address', 'default' => null, 'rules' => ['nullable', 'string', 'max:300']],
                'invoice_footer' => ['type' => 'textarea', 'label' => 'Invoice footer note', 'default' => 'Thank you for your business.', 'rules' => ['nullable', 'string', 'max:300']],
            ],
        ],

        'notifications' => [
            'label' => 'Notifications',
            'description' => 'Post alerts to a Discord channel via webhook.',
            'fields' => [
                'discord_webhook_url' => ['type' => 'url', 'label' => 'Discord webhook URL', 'default' => null, 'rules' => ['nullable', 'url', 'starts_with:https://discord.com/api/webhooks/,https://discordapp.com/api/webhooks/', 'max:300']],
                'notify_messages' => ['type' => 'toggle', 'label' => 'New contact messages', 'default' => '1', 'rules' => ['boolean']],
                'notify_payments' => ['type' => 'toggle', 'label' => 'Invoice payments', 'default' => '1', 'rules' => ['boolean']],
                'notify_testimonials' => ['type' => 'toggle', 'label' => 'Testimonial submissions', 'default' => '1', 'rules' => ['boolean']],
            ],
        ],

        'access' => [
            'label' => 'Access',
            'description' => 'Discord accounts allowed into this panel, in addition to ADMIN_DISCORD_IDS in .env.',
            'fields' => [
                'admin_discord_ids' => ['type' => 'textarea', 'label' => 'Admin Discord user IDs', 'default' => null, 'rules' => ['nullable', 'string', 'max:1000', 'regex:/^[\d,\s]*$/'], 'help' => 'One numeric Discord user ID per line (or comma separated).'],
            ],
        ],

        'seo' => [
            'label' => 'SEO & analytics',
            'description' => 'Search and tracking.',
            'fields' => [
                'meta_keywords' => ['type' => 'text', 'label' => 'Meta keywords', 'default' => 'laravel developer, garry\'s mod, gmod addons, lua, ui design, web development uk', 'rules' => ['nullable', 'string', 'max:300'], 'public' => true],
                'google_analytics_id' => ['type' => 'text', 'label' => 'Google Analytics ID', 'default' => null, 'rules' => ['nullable', 'string', 'max:30', 'regex:/^G-[A-Z0-9]+$/'], 'public' => true],
            ],
        ],

    ],

];
