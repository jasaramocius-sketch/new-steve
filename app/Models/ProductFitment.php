<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductFitment extends Model
{
    /** Prefix used on mirrored product_vehicle rows so admin edits only replace their own rows. */
    public const PIVOT_NOTE_PREFIX = 'admin-fitment:';

    protected $table = 'product_fitments';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'year_from',
        'year_to',
        'make',
        'model',
        'engine',
        'sort_order',
    ];

    protected $casts = [
        'year_from' => 'integer',
        'year_to' => 'integer',
        'sort_order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function label(): string
    {
        $years = $this->year_from == $this->year_to
            ? (string) $this->year_from
            : $this->year_from.'–'.$this->year_to;

        return trim($years.' '.$this->make.' '.$this->model.($this->engine ? ' '.$this->engine : ''));
    }
}
