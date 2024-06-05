<?php

namespace App\Trait;

trait DeletedBy
{
    public static function bootDeletedBy()
    {
        static::deleting(function ($model) {
            if (!$model->isDirty('deleted_by')) {
                $model->deleted_by = auth()->user()->id;
            }
        });
    }
}
