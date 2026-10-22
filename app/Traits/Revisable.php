<?php

namespace App\Traits;

use App\Models\Revision;
use Illuminate\Support\Facades\Auth;

trait Revisable
{
    protected static function bootRevisable()
    {
        static::created(function ($model) {
            static::logRevision($model, 'created', [], $model->toArray());
        });

        static::updated(function ($model) {
            $original = $model->getOriginal();
            $changes = $model->getDirty();

            $oldValues = [];
            $newValues = [];

            foreach ($changes as $key => $newValue) {
                if (in_array($key, ['created_at', 'updated_at'])) {
                    continue;
                }
                $oldValues[$key] = $original[$key] ?? null;
                $newValues[$key] = $newValue;
            }

            if (! empty($oldValues) || ! empty($newValues)) {
                static::logRevision($model, 'updated', $oldValues, $newValues);
            }
        });

        static::deleted(function ($model) {
            static::logRevision($model, 'deleted', $model->toArray(), []);
        });
    }

    protected static function logRevision($model, string $action, array $oldValues, array $newValues): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        Revision::create([
            'user_id' => $user->id,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'url' => request()->fullUrl(),
        ]);
    }
}
