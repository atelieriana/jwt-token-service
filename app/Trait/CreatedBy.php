<?php

namespace App\Trait;

trait CreatedBy
{
    public static function bootCreatedBy()
    {
        static::creating(function($model){
            if (!$model->isDirty('created_by')) {
                $model->created_by = auth()->user()->id;
            }
        });
    }
}
