<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $guarded = [];
    
    public function profile_image()
    {
        $image_path = ($this->image) ? $this->image : 'profile/NPd2ACZazytgEacGyZBsdJ3PJeOMQgk235YYC8gc.png';
        return '/storage/' . $image_path;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class);
    }
}
