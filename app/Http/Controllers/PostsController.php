<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class PostsController extends Controller
{
    // __construct function will block users who are not logged in from reaching the create posts page.
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index() 
    {
        $users = auth()->user()->following()->pluck('profiles.user_id');

        $posts = Post::whereIn('user_id', $users)->with('user')->latest()->paginate(5);

        return view('posts.index', compact('posts'));
    }
    
    public function create()
    {
        return view('posts.create');
    }

    public function store()
    {
        $data = request()->validate([
            'caption' => 'required',
            'image' => ['required', 'image'],
        ]);
        

        // Make sure to run php artisan storage:link so photos can be accessed by public users.
        $image_path = request('image')->store('uploads', 'public');

        // This will crop the image to be 1200x1200 each time. Image object needs to use Intervention add on, NOT namespace.
        $image = Image::make(public_path("storage/{$image_path}"))->fit(1200, 1200);
        $image->save();

        // $data array is the same as the one being validated. unvalidated values will have blank rules. e.g 'value' => ''
        // Grab authenticated user, go into their posts, and create. Laravel will add user_id automatically.
        // auth()->user()->posts()->create($data);
        // Because we are passing the image path as a value to image, we can no longer pass only $data.
        auth()->user()->posts()->create([
            'caption' => $data['caption'],
            'image' => $image_path
        ]);

        return redirect('/profile/' . auth()->user()->id);
    }

    // \App\Post makes it so Laravel will fetch the post from post id. Also handles find or fail errors for invalid id.    
    public function show(\App\Post $post)
    {
        // compact('post') is the same thing as doing ['post' => post]
        return view('posts.show', compact('post'));
    }
}
