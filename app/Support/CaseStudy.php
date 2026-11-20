<?php

namespace App\Support;

use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Support\Str;

/**
 * Turns a project's markdown into a scroll story: text before the first
 * "## " heading is the intro, each "## " section becomes a chapter, and
 * every chapter gets a screenshot (chosen in admin, or the gallery in order).
 */
class CaseStudy
{
    /** @return array{intro: ?string, chapters: list<array>, results: list<array>} */
    public static function build(Project $project): array
    {
        [$intro, $sections] = self::split((string) $project->body);
        $images = $project->relationLoaded('images') ? $project->images : $project->images()->get();
        $chosen = $project->chapter_images ?? [];
        $pool = $images->values();

        $chapters = [];
        foreach ($sections as $i => [$title, $markdown]) {
            $image = isset($chosen[$i]) ? $images->firstWhere('id', (int) $chosen[$i]) : null;
            $image ??= $pool->get($i);

            $chapters[] = [
                'title' => $title,
                'html' => self::html($markdown),
                'image' => $image ? self::image($image) : ($i === 0 && $project->cover_image ? [
                    'url' => Media::url($project->cover_image),
                    'srcset' => Media::srcset($project->cover_image),
                    'caption' => null,
                ] : null),
            ];
        }

        return [
            'intro' => $intro !== '' ? self::html($intro) : null,
            'chapters' => $chapters,
            'results' => array_values(array_filter($project->results ?? [], fn ($r) => filled($r['value'] ?? null))),
        ];
    }

    /** Chapter titles only — used by the admin form's image pickers. */
    public static function titles(?string $markdown): array
    {
        return array_column(self::split((string) $markdown)[1], 0);
    }

    /** @return array{0: string, 1: list<array{0: string, 1: string}>} */
    private static function split(string $markdown): array
    {
        $intro = [];
        $sections = [];
        $inFence = false;

        foreach (preg_split('/\R/', $markdown) as $line) {
            if (preg_match('/^\s*(```|~~~)/', $line)) {
                $inFence = ! $inFence;
            }

            if (! $inFence && preg_match('/^##\s+(.+?)\s*#*\s*$/', $line, $m)) {
                $sections[] = [trim($m[1]), []];

                continue;
            }

            if ($sections) {
                $sections[array_key_last($sections)][1][] = $line;
            } else {
                $intro[] = $line;
            }
        }

        return [
            trim(implode("\n", $intro)),
            array_map(fn ($s) => [$s[0], trim(implode("\n", $s[1]))], $sections),
        ];
    }

    private static function html(string $markdown): string
    {
        return Str::markdown($markdown, ['html_input' => 'strip', 'allow_unsafe_links' => false]);
    }

    private static function image(ProjectImage $image): array
    {
        return ['url' => $image->url, 'srcset' => Media::srcset($image->path), 'caption' => $image->caption];
    }
}
