<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    use HasFactory;

     // マスアサインメント可能な属性を指定
     protected $fillable = [
        'title',
        'user_id',
    ];
    
    // protected $fillable = ['title'];
    public function tasks() : HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
