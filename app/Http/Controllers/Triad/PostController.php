<?php

namespace App\Http\Controllers\Triad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Triads\VideoRequest;
use App\Http\Resources\VideoResource;
use App\Models\Comment;
use App\Models\LikeVideo;
use App\Models\Video;
use App\Notifications\NewLikeOnVideo;
use App\Notifications\NewVideoFromFollowing;
use App\Notifications\NewVideoFromFollowingFollowing;
use Illuminate\Http\Request;

class PostController extends Controller
{

    public function index()
    {
        try {
            $videos = Video::with('user')->with('likes')->with('comments')->latest()->get();
            $videos = VideoResource::collection($videos);
            return response([
                'message' => 'success',
                'videos' => $videos
            ], 200);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function search(Request $request)
    {
        try {
            $videos = Video::where('description', 'LIKE', "%{$request->search}%")->with('user')->with('likes')->with('comments')->latest()->get();
            $videos = VideoResource::collection($videos);
            return response([
                'message' => 'success',
                'videos' => $videos
            ], 200);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function view($videoId)
    {
        try {
            $video = Video::with('user')->whereId($videoId)->first();
            if ($video) {
                // Video::with('user')->whereId($videoId)->update("video_popularity", "0.01");
                return response([
                    'message' => 'success',
                    'video' => $video
                ]);
            } else {
                return response([
                    'message' => 'Not found'
                ], 400);
            }
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function like($videoId)
    {
        try {
            $video = Video::whereId($videoId)->with("user")->first();
            if ($video) {
                $like = LikeVideo::whereUserId(auth()->id())->whereVideoId($videoId)->first();
                if (empty($like)) {
                    Video::whereId($videoId)->update(['video_popularity' => $video->video_popularity + 0.01]);
                    LikeVideo::create([
                        'user_id' => auth()->id(),
                        'video_id' => $videoId
                    ]);
                } else {
                    LikeVideo::whereUserId(auth()->id())->whereVideoId($videoId)->delete();
                }
                \App\Models\User::find($video->user->id)->notify(new NewLikeOnVideo($video->id, auth()->id()));
                return response([
                    'message' => 'success',
                    'likes' => LikeVideo::whereVideoId($videoId)->count(),
                    'like_count' => LikeVideo::whereVideoId($videoId)->pluck('user_id')
                ]);
            } else {
                return response([
                    'message' => 'Not found'
                ], 400);
            }
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(VideoRequest $videoRequest)
    {
        try {
            //validate data
            $videoRequest->validated();
            $data = [
                'description' => $videoRequest->description,
                'hashtags' => $videoRequest->hashtags,
                'categories' => $videoRequest->categories,
                'mentions' => $videoRequest->mentions,
                'poster_popularity_index' => 0.0,
                'poster_video_priority' => 0.0,
                'video_manual_boost_constant' => 0.0,
                'video_popularity' => 0.0
            ];
            $videoPath = 'public/videos';
            $video = $videoRequest->file('src');
            $video_name = $video->getClientOriginalName();
            $path = $videoRequest->file('src')->storeAs($videoPath, auth()->id() . '/' . uniqid() . uniqid() . $video_name);

            $data['src'] = $path;

            $save_video = auth()->user()->videos()->create($data);
            if ($save_video) {
                $video = Video::whereId($save_video->id)->first();
                $followers = \App\Models\User::find(auth()->id())->is_followers;
                if($followers)
                {
                    foreach ($followers as $follower)
                    {
                        \App\Models\User::find($follower)->notify(new NewVideoFromFollowing(auth()->id(), $video->id));
                        $followerFollowers = \App\Models\User::find($follower)->is_followers;
                        if($followerFollowers)
                        {
                            foreach ($followerFollowers as $followerFollower)
                            {
                                \App\Models\User::find($followerFollower)->notify(new NewVideoFromFollowingFollowing($followerFollower, auth()->id(), $video->id));
                            }
                        }
                    }
                }
                return response([
                    'message' => 'success',
                    'video' => $save_video
                ], 201);
            } else {
                return response([
                    'message' => 'error',
                ], 500);
            }
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function comment(Request $request)
    {
        try {
            $request->validate([
                'comment' => 'required'
            ]);
            $com = Comment::create([
                'user_id' => auth()->id(),
                'video_id' => $request->video_id,
                'content' => $request->comment,
            ]);
            if ($com) {
                $video = Video::whereId($request->video_id)->with("user")->first();
                \App\Models\User::find($video->user->id)->notify(new NewLikeOnVideo($video->id, auth()->id()));
                return response([
                    'message' => 'success',
                    'comment_count' => Comment::whereVideoId($request->video_id)->count(),
                ], 200);
            } else {
                return response([
                    'message' => 'Can\'t comment'
                ], 500);
            }
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function fetchComments($id)
    {
        try {
            $comments = Comment::whereVideoId($id)->with('user')->latest()->get();
            return response([
                'comments' => $comments
            ]);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], 500);
            //throw $th;
        }
    }
}
