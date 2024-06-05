<?php

namespace App\Trait;

trait UpdatedBy
{
    public static function bootUpdatedBy()
    {
        static::updating(function($model){
            if (!$model->isDirty('updated_by')) {
                $model->updated_by = auth()->user()->id;
            }
        });
    }
}
