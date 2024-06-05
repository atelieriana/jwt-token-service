<?php

namespace App\Models;

use App\Trait\CreatedBy;
use App\Trait\DeletedBy;
use App\Trait\UpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Client extends Model
{
    use HasFactory, SoftDeletes, CreatedBy, UpdatedBy, DeletedBy;

    protected $table = 'client';
    protected $fillable = [
        'application', 'username', 'secret',
    ];

    /**
     * Digunakan untuk mendapatkan data client berdasarkan username dan password
     * @param $username
     * @param $password
     * @return mixed
     */
    public function getClient($username, $password)
    {
        return self::select(
            'id',
            'application',
            'secret'
        )
            ->where('username', $username)
            ->where('secret', $password)
            ->get();
    }

    /**
     * Digunakan untuk mendapatkan secret
     * @param $username
     * @return mixed
     */
    public function getSecretByHashID($username)
    {
        return self::select(
            'id',
            'application',
            'secret'
        )
            ->where(DB::raw('SHA1(MD5(id)) '), $username)
            ->get();
    }

    public function detail_modules()
    {
        return $this->hasMany(DetailModule::class, 'client_id');
    }
}
