<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class Vote extends Model
{

    use HasFactory;

    
    protected $fillable = [
        'user_id',
        'date_option_id',
        'vote',
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
}
