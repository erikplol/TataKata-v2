<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id',
        'file_name',
        'file_location',
        'upload_status',
        'original_text',
        'corrected_text',
        'details',
        'progress_log',
    ];

    protected $casts = [
        'progress_log' => 'array',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function histories() {
        return $this->hasMany(History::class);
    }
}