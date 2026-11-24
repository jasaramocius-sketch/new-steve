<?php

namespace App\Models;

use App\Traits\Revisable;
use App\Traits\TracksIsDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SteveStore\PageBuilder\Traits\HasBlocks;

class Page extends Model
{
    use HasBlocks, Revisable, SoftDeletes, TracksIsDeleted;

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'image',
        'content',
        'design_mode',
        'show_title',
        'meta_title',
        'meta_description',
        'status',
        'is_deleted',
    ];

    protected $casts = [
        'status' => 'boolean',
        'show_title' => 'boolean',
    ];

    /**
     * Explicit design mode selected in the admin editor. Falls back to the
     * legacy data shape for rows created before design_mode existed.
     */
    public function designMode(): string
    {
        if (in_array($this->design_mode, ['classic', 'builder', 'both'], true)) {
            return $this->design_mode;
        }

        $hasBlocks = $this->getBlocks() !== [];
        $hasContent = trim(strip_tags((string) $this->content)) !== '';

        if ($hasBlocks) {
            return $hasContent ? 'both' : 'builder';
        }

        return 'classic';
    }
}
