<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Http\Resources\TagResource;
use App\Models\Article;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api'])->except(['all']);
    }

    public function all()
    {
        $tags = Tag::query()->orderBy('order')->get();

        return TagResource::collection($tags);
    }
}
