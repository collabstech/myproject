<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $table = 'companies';

    protected $fillable = [
        'uuid', 'name', 'logo',
    ];

    public function user()
    {
        return $this->hasMany(User::class, 'company_id', 'id');
    }

    public function project()
    {
        return $this->hasMany(Project::class);
    }
}
