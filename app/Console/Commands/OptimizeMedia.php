<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\ProjectImage;
use App\Support\BrandColor;
use App\Support\ImageOptimizer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('media:optimize')]
#[Description('Convert existing project screenshots to WebP (safe to run repeatedly)')]
class OptimizeMedia extends Command
{
    public function handle(): int
    {
        $converted = 0;

        foreach (ProjectImage::all() as $image) {
            $converted += $this->convert($image, 'path');
        }
        foreach (Project::whereNotNull('cover_image')->get() as $project) {
            $converted += $this->convert($project, 'cover_image');
        }

        // Projects on "auto" brand colour that haven't been detected yet.
        $coloured = 0;
        foreach (Project::where('accent_auto', true)->whereNull('accent_color')->whereNotNull('cover_image')->get() as $project) {
            if ($colour = BrandColor::fromImage($project->cover_image)) {
                $project->forceFill(['accent_color' => $colour])->save();
                $coloured++;
            }
        }

        $this->components->info($converted ? "Optimised {$converted} image(s)." : 'Everything is already optimised.');
        if ($coloured) {
            $this->components->info("Detected brand colours for {$coloured} project(s).");
        }

        return self::SUCCESS;
    }

    private function convert($model, string $column): int
    {
        $path = $model->{$column};

        // Bundled images ship pre-converted in the repo — just point at the WebP.
        if (Str::startsWith($path, 'images/')) {
            $webp = preg_replace('/\.(png|jpe?g)$/i', '.webp', $path);
            if ($webp !== $path && is_file(public_path($webp))) {
                $model->forceFill([$column => $webp])->save();

                return 1;
            }

            return 0;
        }

        $new = ImageOptimizer::optimize($path);
        if ($new === $path) {
            return 0;
        }

        // Other rows (e.g. a cover that is also a gallery image) may share the old path.
        Project::where('cover_image', $path)->update(['cover_image' => $new]);
        ProjectImage::where('path', $path)->update(['path' => $new]);
        $model->forceFill([$column => $new]);

        return 1;
    }
}
