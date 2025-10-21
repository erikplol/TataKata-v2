<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puebi extends Model
{
    protected $table = 'puebi_entries';
    protected $fillable = ['slug','title','path','content_markdown'];
    public $timestamps = false;
}
