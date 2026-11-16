<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Support\CaseStudy;
use App\Support\ImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CaseStudyAndMediaTest extends TestCase
{
    use RefreshDatabase;

    private const BODY = <<<'MD'
Intro paragraph about **the client**.

## The brief
They needed bookings.

```md
## not a chapter (inside a code block)
```

## What I built
- Checkout
- Newsletter

## Results
It worked.
MD;

    private function project(array $attrs = []): Project
    {
        return Project::create([
            'title' => 'Trails', 'slug' => 'trails', 'summary' => 'Tours', 'body' => self::BODY,
            'status' => ProjectStatus::Published, 'published_at' => now(), ...$attrs,
        ]);
    }

    public function test_markdown_headings_become_chapters(): void
    {
        $story = CaseStudy::build($this->project());

        $this->assertSame(['The brief', 'What I built', 'Results'], array_column($story['chapters'], 'title'));
        $this->assertStringContainsString('<strong>the client</strong>', $story['intro']);
        $this->assertStringContainsString('not a chapter', $story['chapters'][0]['html'], 'headings inside code blocks stay in the chapter');
        $this->assertStringContainsString('<li>Newsletter</li>', $story['chapters'][1]['html']);
    }

    public function test_chapter_images_use_admin_choice_then_gallery_order(): void
    {
        $p = $this->project();
        $a = ProjectImage::create(['project_id' => $p->id, 'path' => 'projects/a.png', 'sort_order' => 1]);
        $b = ProjectImage::create(['project_id' => $p->id, 'path' => 'projects/b.png', 'sort_order' => 2]);
        $p->update(['chapter_images' => [null, null, $a->id]]);

        $images = array_map(fn ($c) => $c['image']['url'] ?? null, CaseStudy::build($p->fresh())['chapters']);
        $this->assertStringEndsWith('a.png', $images[0]);
        $this->assertStringEndsWith('b.png', $images[1]);
        $this->assertStringEndsWith('a.png', $images[2], 'explicit choice wins');
    }

    public function test_project_page_gets_the_story_and_results(): void
    {
        $this->project(['results' => [['value' => '38', 'label' => 'addons'], ['value' => '', 'label' => 'ignored']]]);

        $this->get('/projects/trails')->assertOk()->assertInertia(fn ($page) => $page
            ->component('Projects/Show')
            ->has('project.story.chapters', 3)
            ->has('project.story.results', 1)
            ->where('project.story.results.0.value', '38'));
    }

    public function test_admin_saves_results_and_only_own_chapter_images(): void
    {
        $this->actingAs($this->admin());
        $p = $this->project();
        $mine = ProjectImage::create(['project_id' => $p->id, 'path' => 'projects/mine.png', 'sort_order' => 1]);
        $other = $this->project(['title' => 'Other', 'slug' => 'other']);
        $theirs = ProjectImage::create(['project_id' => $other->id, 'path' => 'projects/theirs.png', 'sort_order' => 1]);

        $this->put("/admin/projects/{$p->id}", [
            'title' => 'Trails', 'summary' => 'Tours', 'body' => self::BODY, 'status' => 'published',
            'results' => [['value' => '50', 'label' => 'emails per batch'], ['value' => '', 'label' => '']],
            'chapter_images' => [1 => $mine->id, 2 => $theirs->id],
        ])->assertSessionHasNoErrors();

        $p->refresh();
        $this->assertSame([['value' => '50', 'label' => 'emails per batch']], $p->results);
        $this->assertSame([null, $mine->id, null], $p->chapter_images);
    }

    public function test_uploads_are_converted_to_webp_with_a_small_copy(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        $p = $this->project();

        $this->post("/admin/projects/{$p->id}/images", ['images' => [UploadedFile::fake()->image('shot.png', 2600, 1600)]])->assertSessionHas('success');

        $path = ProjectImage::sole()->path;
        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists([$path, ImageOptimizer::smallPath($path)]);
        $this->assertSame(ImageOptimizer::MAX_WIDTH, getimagesize(Storage::disk('public')->path($path))[0]);
        $this->assertSame(ImageOptimizer::SMALL_WIDTH, getimagesize(Storage::disk('public')->path(ImageOptimizer::smallPath($path)))[0]);
    }

    public function test_optimize_command_converts_existing_images(): void
    {
        Storage::fake('public');
        $p = $this->project();
        Storage::disk('public')->put('projects/old.png', UploadedFile::fake()->image('old.png', 1200, 800)->get());
        ProjectImage::create(['project_id' => $p->id, 'path' => 'projects/old.png', 'sort_order' => 1]);
        $p->update(['cover_image' => 'projects/old.png']);

        $this->artisan('media:optimize')->assertSuccessful();

        $this->assertSame('projects/old.webp', ProjectImage::sole()->path);
        $this->assertSame('projects/old.webp', $p->fresh()->cover_image);
        Storage::disk('public')->assertMissing('projects/old.png');
        $this->artisan('media:optimize')->expectsOutputToContain('already optimised');
    }

    public function test_pages_no_longer_publish_the_route_list(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('admin.projects', $html);
        $this->assertStringNotContainsString('const Ziggy', $html);
    }
}
