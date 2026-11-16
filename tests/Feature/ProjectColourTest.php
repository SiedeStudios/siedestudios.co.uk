<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Support\BrandColor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectColourTest extends TestCase
{
    use RefreshDatabase;

    private function image(string $path, array $rgb): string
    {
        $img = imagecreatetruecolor(120, 80);
        imagefill($img, 0, 0, imagecolorallocate($img, ...$rgb));
        ob_start();
        imagepng($img);
        Storage::disk('public')->put($path, ob_get_clean());

        return $path;
    }

    private function hue(string $hex): float
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $d = $max - $min ?: 1;

        return fmod(match ($max) {
            $r => ($g - $b) / $d + 6,
            $g => ($b - $r) / $d + 2,
            default => ($r - $g) / $d + 4,
        }, 6) * 60;
    }

    public function test_detects_vivid_colours_and_ignores_grey_covers(): void
    {
        Storage::fake('public');

        $teal = BrandColor::fromImage($this->image('teal.png', [10, 170, 160]));
        $this->assertNotNull($teal);
        $this->assertEqualsWithDelta(176, $this->hue($teal), 12);

        $this->assertNull(BrandColor::fromImage($this->image('grey.png', [60, 60, 66])), 'grey screenshots keep the site accent');
        $this->assertNull(BrandColor::fromImage('missing.png'));
    }

    public function test_custom_colours_are_kept_readable(): void
    {
        $dark = BrandColor::normalize('#003300');
        [$r, $g, $b] = sscanf($dark, '#%02x%02x%02x');
        $this->assertGreaterThan(150, max($r, $g, $b), 'very dark colours are lifted');
        $this->assertSame(BrandColor::normalize('#E59533'), BrandColor::normalize('E59533'));
        $this->assertNull(BrandColor::normalize('red'));
    }

    public function test_admin_accent_modes(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        $project = Project::create(['title' => 'P', 'slug' => 'p', 'summary' => 'S', 'status' => ProjectStatus::Published, 'published_at' => now(), 'cover_image' => $this->image('projects/cover.png', [229, 149, 51])]);
        $base = ['title' => 'P', 'summary' => 'S', 'status' => 'published'];

        $this->put("/admin/projects/{$project->id}", [...$base, 'accent_mode' => 'auto'])->assertSessionHasNoErrors();
        $this->assertTrue($project->fresh()->accent_auto);
        $this->assertNotNull($project->fresh()->accent_color);

        $this->put("/admin/projects/{$project->id}", [...$base, 'accent_mode' => 'custom', 'accent_color' => '#3B82F6']);
        $this->assertFalse($project->fresh()->accent_auto);
        $this->assertSame(BrandColor::normalize('#3B82F6'), $project->fresh()->accent_color);

        $this->put("/admin/projects/{$project->id}", [...$base, 'accent_mode' => 'custom', 'accent_color' => 'blue'])->assertSessionHasErrors('accent_color');

        $this->put("/admin/projects/{$project->id}", [...$base, 'accent_mode' => 'none']);
        $this->assertNull($project->fresh()->accent_color);

        // Saving without touching the colour keeps the chosen mode.
        $this->put("/admin/projects/{$project->id}", $base);
        $this->assertNull($project->fresh()->accent_color);
        $this->assertFalse($project->fresh()->accent_auto);
    }

    public function test_case_study_page_receives_its_colour(): void
    {
        $p = Project::create(['title' => 'P', 'slug' => 'p', 'summary' => 'S', 'status' => ProjectStatus::Published, 'published_at' => now()]);
        $p->forceFill(['accent_color' => '#E59533', 'accent_auto' => false])->save();

        $this->get('/projects/p')->assertInertia(fn ($page) => $page->where('project.accent', '#E59533'));
    }

    public function test_seasonal_effects_setting_is_a_real_boolean(): void
    {
        $this->get('/')->assertInertia(fn ($page) => $page->where('site.seasonal_effects', true));
        $this->settings(['seasonal_effects' => false]);
        $this->get('/')->assertInertia(fn ($page) => $page->where('site.seasonal_effects', false));
    }
}
