<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dictionary extends Model
{
    protected $table = 'dictionary';

    protected $fillable = ['word', 'arti', 'type'];
}
