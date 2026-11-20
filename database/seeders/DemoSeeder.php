<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Enums\TestimonialStatus;
use App\Models\Invoice;
use App\Models\Message;
use App\Models\Project;
use App\Models\Service;
use App\Models\Share;
use App\Models\Testimonial;
use App\Support\Settings;
use Illuminate\Database\Seeder;

/**
 * Local/demo content only — sample testimonials, invoices, messages and shares
 * with placeholder people. Never run this against production.
 *
 *   php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('DemoSeeder is disabled in production.');

            return;
        }

        $this->call(DatabaseSeeder::class);

        app(Settings::class)->set([
            'paypal_email' => 'payments-sandbox@example.com',
            'paypal_sandbox' => true,
            'discord_handle' => 'siedestudios',
            'github_url' => 'https://github.com/siedestudios',
        ]);

        $projects = Project::pluck('id', 'slug');

        $testimonials = [
            ['name' => 'Sample Client A', 'company' => 'Demo Gaming Network', 'role' => 'Owner', 'rating' => 5, 'project_id' => $projects['siedegaming-web-suite'] ?? null, 'is_featured' => true,
                'body' => 'Placeholder testimonial. Communication was quick, the store went live ahead of schedule and the admin panel is the easiest thing our staff use.'],
            ['name' => 'Sample Client B', 'company' => 'Demo Tours Ltd', 'role' => 'Founder', 'rating' => 5, 'project_id' => $projects['sandervk-tours'] ?? null, 'is_featured' => true,
                'body' => 'Placeholder testimonial. The booking flow finally makes sense to our customers, and payments just work. Exactly what we asked for.'],
            ['name' => 'Sample Client C', 'company' => 'Demo RP Server', 'role' => 'Server manager', 'rating' => 4, 'project_id' => $projects['edgerp-f4-menu'] ?? null,
                'body' => 'Placeholder testimonial. Players noticed the new menu immediately. Clean code, no performance hit, and changes were turned around fast.'],
        ];

        foreach ($testimonials as $i => $t) {
            Testimonial::updateOrCreate(['name' => $t['name']], [...$t, 'status' => TestimonialStatus::Approved, 'sort_order' => $i, 'submitted_at' => now()->subDays(10 + $i), 'approved_at' => now()->subDays(9 + $i)]);
        }

        Testimonial::updateOrCreate(['name' => 'Pending Reviewer'], ['company' => 'Demo Studio', 'role' => 'Lead', 'status' => TestimonialStatus::Pending, 'rating' => 5, 'body' => 'Placeholder testimonial awaiting review in the admin panel.', 'submitted_at' => now()->subDay()]);
        Testimonial::updateOrCreate(['name' => 'Invited Client'], ['company' => 'Demo Agency', 'role' => 'Director', 'status' => TestimonialStatus::Invited, 'project_id' => $projects['sandervk-com'] ?? null]);

        $invoice = Invoice::updateOrCreate(['reference' => 'SS-2611-DEMO01'], [
            'client_name' => 'Demo Client', 'client_email' => 'client@example.com', 'client_company' => 'Demo Gaming Network',
            'currency' => 'GBP', 'status' => InvoiceStatus::Open, 'issued_at' => now()->subDays(2), 'due_at' => now()->addDays(12),
            'notes' => 'Payment covers phase one of the store build.',
        ]);
        $invoice->items()->delete();
        $invoice->items()->createMany([
            ['description' => 'Store module — packages, checkout & coupons', 'quantity' => 1, 'unit_price' => 450, 'sort_order' => 0],
            ['description' => 'Admin dashboard & revenue reporting', 'quantity' => 1, 'unit_price' => 220, 'sort_order' => 1],
            ['description' => 'Deployment & SSL setup', 'quantity' => 2, 'unit_price' => 40, 'sort_order' => 2],
        ]);
        $invoice->recalculateTotal();
        $invoice->save();

        $paid = Invoice::updateOrCreate(['reference' => 'SS-2610-DEMO02'], [
            'client_name' => 'Another Client', 'client_email' => 'another@example.com', 'currency' => 'GBP',
            'status' => InvoiceStatus::Paid, 'issued_at' => now()->subDays(30), 'due_at' => now()->subDays(16),
            'paid_at' => now()->subDays(20), 'paypal_txn_id' => 'DEMO8TXN0000001', 'paid_amount' => 180, 'payer_email' => 'another@example.com',
        ]);
        $paid->items()->delete();
        $paid->items()->create(['description' => 'Custom HUD — design & Lua', 'quantity' => 1, 'unit_price' => 180]);
        $paid->recalculateTotal();
        $paid->save();

        $service = Service::first();
        Message::firstOrCreate(['email' => 'lead@example.com'], [
            'name' => 'Example Lead', 'discord' => 'examplelead', 'service_id' => $service?->id, 'service_name' => $service?->title,
            'budget' => '£1,000 – £5,000', 'body' => 'Placeholder enquiry: looking for a store + admin panel for our community, launching early next year.',
        ]);

        Share::firstOrCreate(['slug' => 'demo1234'], [
            'title' => 'f4menu/cl_init.lua',
            'language' => 'lua',
            'content' => <<<'LUA'
local PANEL = {}

function PANEL:Init()
    self:SetSize(ScrW() * 0.6, ScrH() * 0.7)
    self:Center()
    self:MakePopup()

    self.Tabs = {}
    for _, tab in ipairs(SiedeF4.Config.Tabs) do
        self:AddTab(tab.name, tab.icon, tab.build)
    end
end

function PANEL:Paint(w, h)
    draw.RoundedBox(6, 0, 0, w, h, SiedeF4.Colors.Background)
    surface.SetDrawColor(SiedeF4.Colors.Accent)
    surface.DrawRect(0, 0, 4, h)
end

vgui.Register("SiedeF4.Frame", PANEL, "EditablePanel")
LUA,
        ]);
    }
}
