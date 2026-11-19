<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscriber extends Model
{
    use SoftDeletes;

    protected $fillable = ['email'];

    /**
     * Subscribe an email address, restoring a previously removed subscriber.
     */
    public static function firstOrCreateForEmail(string $email): self
    {
        $subscriber = static::withTrashed()->firstOrCreate([
            'email' => strtolower(trim($email)),
        ]);

        if ($subscriber->trashed()) {
            $subscriber->restore();
        }

        return $subscriber;
    }
}
