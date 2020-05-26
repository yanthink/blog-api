<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    public function index(Request $request)
    {
        $this->authorize('index', Comment::class);

        $comments = Comment::query()
                           ->filter($request->all())
                           ->orderBy('id', 'desc')
                           ->paginate($request->get('per_page', 10));

        return CommentResource::collection($comments);
    }

    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', Comment::class);

        $this->validate($request, ['content.markdown' => 'required']);

        $comment->updated_at = now();
        $comment->save();

        return $this->withNoContent();
    }
}
