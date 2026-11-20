<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** A file shared between the studio and a client. Stored on the private disk. */
#[Fillable(['client_id', 'client_project_id', 'user_id', 'name', 'path', 'size', 'mime'])]
#[Hidden(['path'])]
class ClientFile extends Model
{
    public const DISK = 'local';

    /** Allowed upload types (sent back as downloads, never rendered inline). */
    public const MIMES = 'pdf,png,jpg,jpeg,gif,webp,svg,zip,rar,7z,txt,md,csv,json,lua,doc,docx,xls,xlsx,ppt,pptx,psd,fig,ai,mp4,mov';

    public const MAX_KB = 51200;

    protected static function booted(): void
    {
        static::deleted(fn (ClientFile $file) => Storage::disk(self::DISK)->delete($file->path));
    }

    public static function storeUpload(UploadedFile $upload, Client $client, ?ClientProject $project, User $uploader): self
    {
        return static::create([
            'client_id' => $client->id,
            'client_project_id' => $project?->id,
            'user_id' => $uploader->id,
            'name' => mb_substr($upload->getClientOriginalName(), 0, 250),
            'path' => $upload->store("client-files/{$client->id}", self::DISK),
            'size' => $upload->getSize(),
            'mime' => $upload->getMimeType(),
        ]);
    }

    public function download(): StreamedResponse
    {
        // Always an attachment, so uploaded HTML/SVG can never render on our domain.
        return Storage::disk(self::DISK)->download($this->path, $this->name, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ClientProject::class, 'client_project_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toPortalArray(string $downloadRoute): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'size' => $this->size,
            'mime' => $this->mime,
            'created_at' => $this->created_at?->toIso8601String(),
            'project' => $this->project?->title,
            'uploaded_by' => $this->user?->name,
            'from_studio' => (bool) $this->user?->is_admin,
            'user_id' => $this->user_id,
            'url' => route($downloadRoute, $this),
        ];
    }
}
