<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProcessThumbnailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 60;
    public string $queue = 'media';

    public function __construct(
        public int $mediaId,
        public string $disk = 'public',
    ) {}

    public function handle(): void
    {
        $media = \App\Models\Media::find($this->mediaId);
        if (!$media || !$media->isImage) {
            return;
        }

        $original = Storage::disk($this->disk)->path($media->path);
        if (!file_exists($original)) {
            return;
        }

        $thumbDir = 'thumbnails/' . dirname($media->path);
        $thumbName = pathinfo($media->filename, PATHINFO_FILENAME) . '_thumb.webp';

        $thumbPath = $thumbDir . '/' . $thumbName;

        try {
            $img = Image::read($original);
            $img->cover(300, 300);
            Storage::disk($this->disk)->put($thumbPath, $img->encodeWebp(80));

            $media->update(['thumbnail' => $thumbPath]);
        } catch (\Throwable $e) {
            logger()->warning("Thumbnail generation failed for media #{$this->mediaId}: {$e->getMessage()}");
        }
    }
}
