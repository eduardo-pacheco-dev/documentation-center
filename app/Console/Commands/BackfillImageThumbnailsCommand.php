<?php

namespace App\Console\Commands;

use App\Models\Form;
use App\Models\FormResponse;
use App\Services\Forms\ImageThumbnailer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

#[Signature('forms:backfill-image-thumbnails {--dry-run : Gera as miniaturas sem salvar as alterações}')]
#[Description('Gera miniaturas comprimidas para as imagens já enviadas nos formulários')]
class BackfillImageThumbnailsCommand extends Command
{
    /**
     * Number of thumbnails generated during the command.
     */
    private int $created = 0;

    /**
     * Number of images whose thumbnail could not be generated.
     */
    private int $failed = 0;

    /**
     * Number of images whose original file is not on the disk.
     */
    private int $missing = 0;

    public function __construct(private readonly ImageThumbnailer $thumbnails)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');

        Form::query()
            ->with('responses')
            ->chunkById(100, function ($forms) use ($disk, $dryRun): void {
                foreach ($forms as $form) {
                    $this->process($form, $disk, $dryRun);

                    foreach ($form->responses as $response) {
                        $this->process($response, $disk, $dryRun);
                    }
                }
            });

        $this->info($dryRun
            ? "Simulação concluída: {$this->created} miniatura(s) seriam geradas."
            : "Concluído: {$this->created} miniatura(s) gerada(s).");

        if ($this->missing > 0) {
            $this->warn("{$this->missing} imagem(ns) não encontrada(s) no disco foram ignoradas.");
        }

        if ($this->failed > 0) {
            $this->warn("{$this->failed} imagem(ns) não puderam ser convertidas.");
        }

        return self::SUCCESS;
    }

    /**
     * Generate the missing thumbnails of a form or response.
     */
    private function process(Form|FormResponse $record, Filesystem $disk, bool $dryRun): void
    {
        [$steps, $changed] = $this->withThumbnails($record->steps ?? [], $disk, $dryRun);

        if ($changed && ! $dryRun) {
            $record->update(['steps' => $steps]);
        }
    }

    /**
     * Add the missing thumbnails to every image field of the given steps.
     *
     * @param  array<int, array<string, mixed>>  $steps
     * @return array{0: array<int, array<string, mixed>>, 1: bool}
     */
    private function withThumbnails(array $steps, Filesystem $disk, bool $dryRun): array
    {
        $changed = false;

        foreach ($steps as $stepIndex => $step) {
            foreach ($step['fields'] ?? [] as $fieldIndex => $field) {
                if (($field['type'] ?? null) !== 'image') {
                    continue;
                }

                $images = array_values($field['images'] ?? []);

                if ($images === []) {
                    continue;
                }

                $thumbnails = array_values($field['imageThumbnails'] ?? []);
                $generated = false;

                foreach ($images as $index => $image) {
                    if (! empty($thumbnails[$index])) {
                        continue;
                    }

                    $relativePath = $this->relativePath($image);

                    if ($relativePath === null || ! $disk->exists($relativePath)) {
                        $this->missing++;
                        $thumbnails[$index] = $image;

                        continue;
                    }

                    $thumbnail = $this->makeThumbnail($disk, $relativePath);

                    if ($thumbnail === null) {
                        $this->failed++;
                        $thumbnails[$index] = $image;

                        continue;
                    }

                    $thumbnailPath = $this->thumbnailPath($relativePath);

                    if (! $dryRun) {
                        $disk->put($thumbnailPath, $thumbnail);
                    }

                    $thumbnails[$index] = $this->publicUrl($thumbnailPath);
                    $this->created++;
                    $generated = true;
                }

                if ($generated) {
                    $steps[$stepIndex]['fields'][$fieldIndex]['imageThumbnails'] = array_values($thumbnails);
                    $changed = true;
                }
            }
        }

        return [$steps, $changed];
    }

    /**
     * Build a thumbnail from a stored image, using a temporary local file.
     */
    private function makeThumbnail(Filesystem $disk, string $relativePath): ?string
    {
        $temporary = tempnam(sys_get_temp_dir(), 'form-thumbnail-');

        if ($temporary === false) {
            return null;
        }

        try {
            file_put_contents($temporary, $disk->get($relativePath));

            return $this->thumbnails->make($temporary);
        } catch (Throwable) {
            return null;
        } finally {
            @unlink($temporary);
        }
    }

    /**
     * Resolve the disk path of a stored image URL.
     */
    private function relativePath(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return null;
        }

        $path = ltrim($path, '/');

        return Str::startsWith($path, 'storage/') ? Str::after($path, 'storage/') : $path;
    }

    /**
     * Build the path of the thumbnail that belongs to an image.
     */
    private function thumbnailPath(string $path): string
    {
        return dirname($path).'/thumb-'.pathinfo($path, PATHINFO_FILENAME).'.jpg';
    }

    /**
     * Build a host-agnostic public URL for a stored file.
     */
    private function publicUrl(string $path): string
    {
        $url = Storage::disk('public')->url($path);
        $urlPath = parse_url($url, PHP_URL_PATH);

        return is_string($urlPath) && $urlPath !== '' ? $urlPath : $url;
    }
}
