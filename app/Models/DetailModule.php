<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailModule extends Model
{
    use HasFactory;

    protected $table = 'detail_module';
    protected $fillable = [
        'client_id', 'module_id', 'access_id'
    ];

    public $timestamps = false;

    public function module()
    {
        return $this->hasOne(Module::class, 'id', 'module_id');
    }

    public function access()
    {
        return $this->hasOne(Access::class, 'id', 'access_id');
    }
}
