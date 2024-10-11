<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;
    
    // protected $fillable = ['title'];
    public function tasks() : \Illuminate\Database\Eloquent\Relations\HasMany 
    {
        return $this->hasMany(Task::class);
    }

}
