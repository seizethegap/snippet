<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;

class ProfilesController extends Controller
{
    public function index(User $user)
    {
        $follows = (auth()->user()) ? auth()->user()->following->contains($user->id) : false;
        
        // post_count will be a cache for 30 seconds. If there is no cache, the function will run.
        $post_count = Cache::remember(
            'count.posts' . $user->id, 
            now()->addSeconds(30), 
            function() use ($user) {
                return $user->posts->count();
            });

        $followers_count = Cache::remember(
            'count.followers' . $user->id,
            now()->addSeconds(30),
            function() use ($user) {
                return $user->profile->followers->count();
            });

        $following_count = Cache::remember(
            'count.following' . $user->id,
            now()->addSeconds(30),
            function() use ($user) {
                return $user->following->count();
            });


        return view('profiles.index', compact('user', 'follows', 'post_count', 'followers_count', 'following_count'));
    }

    // use policies to make sure only authorized user can edit/update profile
    public function edit(User $user)
    {
        $this->authorize('update', $user->profile);
        return view('profiles.edit', compact('user'));
    }

    public function update(User $user)
    {
        $this->authorize('update', $user->profile);
        $data = request()->validate([
            'title' => '',
            'description' => 'required',
            'url' => '',
            'image' => '',
        ]);

        if (request('image'))
        {
            // Make sure to run php artisan storage:link so photos can be accessed by public users.
            $image_path = request('image')->store('profile', 'public');

            // This will crop the image to be 1200x1200 each time. Image object needs to use Intervention add on, NOT namespace.
            $image = Image::make(public_path("storage/{$image_path}"))->fit(1000, 1000);
            $image->save();

            $image_array = ['image' => $image_path];
        }

        
        auth()->user()->profile->update(array_merge(
            $data,
            $image_array ?? []
        ));
        

        return redirect("/profile/{$user->id}");

    }
}
