<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;


    protected $fillable = [
        'title',
        'description',
        'location',
        'category',
        'privacy',
        'created_by',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dateOptions()
    {
        return $this->hasMany(DateOption::class);
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class)->nullable();
    }
}
