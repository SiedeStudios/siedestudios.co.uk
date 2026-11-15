<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['name' => 'Web Platforms', 'slug' => 'web-platforms', 'description' => 'Laravel applications, stores and community platforms.'],
            ['name' => "Garry's Mod", 'slug' => 'garrys-mod', 'description' => 'Addons, menus and server systems.'],
            ['name' => 'Websites', 'slug' => 'websites', 'description' => 'Brand and business websites.'],
        ])->mapWithKeys(fn ($c, $i) => [$c['slug'] => ProjectCategory::updateOrCreate(['slug' => $c['slug']], [...$c, 'sort_order' => $i])]);

        $projects = [
            [
                'category' => 'web-platforms',
                'title' => 'SiedeGaming Web Suite',
                'slug' => 'siedegaming-web-suite',
                'summary' => 'A complete community platform for a gaming network: store, orders, coupons, support tickets, forum and a full admin panel.',
                'body' => "A single Laravel application that runs an entire game-server community.\n\n### What's inside\n\n- **Store** with packages, orders, coupons and revenue reporting\n- **Support desk** with ticket queues for players and staff\n- **Forum** with categories and boards\n- **Users, roles & permissions** plus achievements\n- **Server management** linked to the in-game side\n\nThe admin dashboard surfaces revenue trends, recent orders and open tickets at a glance.",
                'client' => 'SiedeGaming',
                'role' => 'Design & full-stack development',
                'year' => '2025',
                'tags' => ['Laravel', 'SCSS', 'jQuery', 'Payments'],
                'cover_image' => 'images/portfolio/dashboard.png',
                'is_featured' => true,
            ],
            [
                'category' => 'web-platforms',
                'title' => 'SanderVK Tours',
                'slug' => 'sandervk-tours',
                'summary' => 'Booking site for guided WWI & WWII battlefield tours, with online payments, a blog and multi-language content.',
                'body' => "A booking platform for guided battlefield tours focused on Operation Market Garden.\n\n- Tour listings with online booking and a **payment gateway**\n- **Blog** for history articles and tour updates\n- Testimonials and contact flows\n- Multi-language content\n- A typographic, archival visual style to match the subject",
                'client' => 'SanderVK Tours',
                'role' => 'Design & development',
                'year' => '2025',
                'site_url' => 'https://sandervktours.com',
                'tags' => ['Laravel', 'Payment gateway', 'Blog', 'Custom CSS'],
                'cover_image' => 'images/portfolio/sandervktours-com.png',
                'is_featured' => true,
            ],
            [
                'category' => 'garrys-mod',
                'title' => 'EdgeRP F4 Menu',
                'slug' => 'edgerp-f4-menu',
                'summary' => 'A custom DarkRP F4 menu with a live dashboard — richest player, job distribution, online players and staff — plus jobs and shop tabs.',
                'body' => "A replacement F4 menu for the EdgeRP DarkRP server.\n\n- **Dashboard** with live statistics: richest player, job distribution and player count\n- **Jobs** and **Shop** tabs styled to match the server\n- Online staff list with ranks\n- Quick links to Discord, website and donations",
                'client' => 'EdgeRP',
                'role' => 'UI design & Lua development',
                'year' => '2024',
                'tags' => ['Lua', 'DarkRP', 'Derma', 'UI'],
                'cover_image' => 'images/addons.png',
                'is_featured' => true,
            ],
            [
                'category' => 'websites',
                'title' => 'SanderVK.com',
                'slug' => 'sandervk-com',
                'summary' => 'Personal brand website with a custom SCSS design system and hand-built interactions.',
                'body' => 'A personal website built around a bespoke SCSS design system, with custom jQuery interactions and a focus on clean, readable layout.',
                'client' => 'Sander VK',
                'role' => 'UI/UX & front-end',
                'year' => '2024',
                'site_url' => 'https://sandervk.com',
                'tags' => ['SCSS', 'jQuery', 'UI/UX'],
                'cover_image' => 'images/portfolio/sandervk-com.png',
                'is_featured' => false,
            ],
        ];

        foreach ($projects as $order => $data) {
            $category = $categories[$data['category']];
            unset($data['category']);

            Project::updateOrCreate(['slug' => $data['slug']], [
                ...$data,
                'project_category_id' => $category->id,
                'status' => ProjectStatus::Published,
                'sort_order' => $order,
                'published_at' => now()->subDays($order * 30),
            ]);
        }
    }
}
