<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserMainDealer extends Model
{
    protected $fillable = ['project_id', 'main_dealer_code', 'user_id'];
}
