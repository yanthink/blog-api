<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class RelationController extends Controller
{
    const RELATION_TYPES = [
        'like' => [
            Article::class,
        ],
        'favorite' => [
            Article::class,
        ],
        'upvote' => [
            Comment::class,
        ],
        'downvote' => [
            Comment::class,
        ],
    ];

    public function __construct()
    {
        $this->middleware(['auth:api'])->except('index');
    }

    public function toggleRelation(Request $request, $relation)
    {
        $this->validate($request, [
            'relation' => 'in:like,follow,subscribe,favorite,upvote,downvote',
        ]);

        $followableId = $request->input('followable_id');
        $followableType = $request->input('followable_type');

        if (!Str::contains($followableType, '\\')) {
            $followableType = 'App\\Models\\'.Str::studly($followableType);
        }

        if (!in_array($followableType, Arr::get(self::RELATION_TYPES, $relation, []))) {
            abort(403);
        }

        if (!resolve($followableType)->whereId($followableId)->exists()) {
            abort(404);
        }

        $method = 'toggle'.Str::studly($relation);

        call_user_func_array([$request->user(), $method], [$followableId, $followableType]);

        return $this->withNoContent();
    }
}
