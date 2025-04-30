<?php

namespace App\Http\Controllers;

use App\Events\PostCreated;
use App\Models\Post;
use App\Models\User;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as FacadesLog;
use Faker\Factory as Faker;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->get();

        return view('posts', compact('posts'));
    }


    public function store(Request $request)
    {
        $post = Post::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
        ]);


        $users = User::limit(1)->get();

        foreach ($users as $user) {


                event(new PostCreated($post, $user->id));

        }

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully.',
            'data' => $post,
        ], 201);
    }

    public function testMultipleReverb()
    {

        $faker = Faker::create();

        $users = User::limit(15)->get();
        // $users = User::get();

        $limit = 1000;
        foreach ($users as $user) {
            for ($i = 1; $i <= $limit; $i++) {
                $fakePost = [
                    'title' => $faker->sentence,
                    'content' => $faker->paragraph,
                    'event_loop_count' => $i,
                ];

                event(new PostCreated((object) $fakePost, $user->id));
            }
        }

        return response()->json(['message' => 'Created '.$limit.' events for '.count($users).' users.']);


        // $faker = Faker::create();

        // $limit = 10;

        // for ($i = 0; $i < $limit; $i++) {
        //     $fakePost = [
        //         'title' => $faker->sentence,
        //         'content' => $faker->paragraph,
        //         'event_loop_count' => $i,
        //     ];


        //     event(new PostCreated((object) $fakePost));
        // }

        // return response()->json(['message' => 'Simulated '.$limit.' events.']);
    }

}
