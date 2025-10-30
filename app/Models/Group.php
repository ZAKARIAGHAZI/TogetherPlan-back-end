<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];


    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
