<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date_option_id',
        'event_id',
        'vote',
        'points',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dateOption()
    {
        return $this->belongsTo(DateOption::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
