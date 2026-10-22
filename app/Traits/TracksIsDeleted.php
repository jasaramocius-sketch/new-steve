<?php

namespace App\Traits;

trait TracksIsDeleted
{
    protected static function bootTracksIsDeleted()
    {
        static::deleted(function ($model) {
            if (! $model->isForceDeleting()) {
                $model->newQueryWithoutScopes()
                    ->where($model->getKeyName(), $model->getKey())
                    ->update(['is_deleted' => 1]);
                $model->is_deleted = 1;
            }
        });

        static::restored(function ($model) {
            $model->newQueryWithoutScopes()
                ->where($model->getKeyName(), $model->getKey())
                ->update(['is_deleted' => 0]);
            $model->is_deleted = 0;
        });
    }
}
