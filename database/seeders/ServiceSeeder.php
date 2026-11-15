<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'title' => 'Web Development',
                'slug' => 'web-development',
                'icon' => 'layers',
                'tagline' => 'Laravel platforms built to be extended, not rewritten.',
                'description' => 'Custom websites and web applications on Laravel — from marketing sites to full community platforms with stores, ticketing, forums and admin panels.',
                'features' => ['Laravel & Vue applications', 'Stores, payments & invoicing', 'Admin panels & dashboards', 'APIs and third-party integrations', 'Hosting, deployment & SSL'],
                'price_note' => 'Quoted per project',
            ],
            [
                'title' => 'UI/UX Design',
                'slug' => 'ui-ux-design',
                'icon' => 'pen',
                'tagline' => 'Interfaces that feel obvious the first time.',
                'description' => 'Interface design for web and in-game UI: layout, flow, and a visual language that fits your brand and your players.',
                'features' => ['Interface & visual design', 'User flows and wireframes', 'Interactive prototypes', 'Design systems', 'In-game HUD & menu design'],
                'price_note' => 'Quoted per project',
            ],
            [
                'title' => 'Lua Development',
                'slug' => 'lua-development',
                'icon' => 'terminal',
                'tagline' => 'Clean, optimised Lua that does not tank your tick rate.',
                'description' => 'Custom Lua scripting for game modifications — gameplay systems, networking, and performance work for servers that need to stay smooth under load.',
                'features' => ['Gameplay systems & mechanics', 'Net-message & networking design', 'Performance profiling & optimisation', 'Bug fixing & code review', 'Config-driven, documented code'],
                'price_note' => 'Quoted per system',
            ],
            [
                'title' => "Garry's Mod Addons",
                'slug' => 'gmod-addons',
                'icon' => 'gamepad',
                'tagline' => 'Bespoke content for servers that want to stand out.',
                'description' => "Custom addons for Garry's Mod: F4 menus, HUDs, entities, admin tooling and full server systems, built to your server's style.",
                'features' => ['F4 menus, HUDs & scoreboards', 'Custom entities & SWEPs', 'DarkRP modules', 'Admin & staff tooling', 'Web ↔ server integrations'],
                'price_note' => 'Quoted per addon',
            ],
        ];

        foreach ($services as $order => $service) {
            Service::updateOrCreate(['slug' => $service['slug']], [...$service, 'sort_order' => $order]);
        }
    }
}
