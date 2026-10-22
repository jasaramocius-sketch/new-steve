<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class FileRevision extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'file_path',
        'event',
        'content_hash',
        'backup_path',
        'diff',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function truncateDiffs(int $olderThanDays = 90): int
    {
        return static::query()
            ->whereNotNull('diff')
            ->where('diff', '!=', '')
            ->where('created_at', '<', now()->subDays($olderThanDays))
            ->whereRaw('CHAR_LENGTH(diff) > 500')
            ->update([
                'diff' => DB::raw('CONCAT(LEFT(diff, 200), "\n...(truncated to save space)")'),
            ]);
    }

    public static function truncatePerFileLimit(int $keepPerFile = 50): int
    {
        return DB::statement("
            UPDATE file_revisions fr
            INNER JOIN (
                SELECT id, ROW_NUMBER() OVER (PARTITION BY file_path ORDER BY created_at DESC) as rn
                FROM file_revisions
            ) ranked ON fr.id = ranked.id
            SET fr.diff = CONCAT(LEFT(fr.diff, 200), '\n...(truncated per-file limit)')
            WHERE ranked.rn > ?
              AND CHAR_LENGTH(fr.diff) > 500
        ", [$keepPerFile]);
    }
}
