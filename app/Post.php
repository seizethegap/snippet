<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    // Turn off guarding so post validation won't throw a fillable mass assignment error during snippet post. 
    // As long as we are naming off each value in validation, it is OK turn off.
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
