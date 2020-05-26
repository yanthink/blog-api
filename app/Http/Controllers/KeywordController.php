<?php

namespace App\Http\Controllers;

use App\Http\Requests\TagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api'])->except(['all']);
    }

    public function index(Request $request)
    {
        $this->authorize('index', Tag::class);

        $tags = Tag::query()
                   ->filter($request->all())
                   ->orderBy('order')
                   ->paginate($request->get('per_page', 10));

        return TagResource::collection($tags);
    }

    public function store(TagRequest $request)
    {
        $this->authorize('store', Tag::class);

        Tag::create($request->only('name', 'slug', 'order'));

        return $this->withNoContent();
    }

    public function update(TagRequest $request, Tag $tag)
    {
        $this->authorize('update', Tag::class);

        $tag->update($request->only('name', 'slug', 'order'));

        return $this->withNoContent();
    }

    public function all()
    {
        $tags = Tag::query()->orderBy('order')->get();

        return TagResource::collection($tags);
    }
}
