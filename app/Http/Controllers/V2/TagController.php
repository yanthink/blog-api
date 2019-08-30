<?php

namespace App\Http\Controllers\V2;

use App\Models\Tag;
use App\Transformers\V2\TagTransformer;
use App\Http\Requests\TagRequest;
use Illuminate\Database\Eloquent\Builder;

class TagController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth')->except(['all']);
        $this->middleware('authorize:App\\Policies\\V2')->except(['all']);
    }

    public function index()
    {
        $pageSize = min(request('pageSize', 10), 20);

        $tags = Tag::query()
            ->when(request('name'), function (Builder $builder, $name) {
                $builder->where('name', $name);
            })
            ->orderBy('order', 'asc')
            ->paginate($pageSize);

        return $this->response->paginator($tags, new TagTransformer);

    }

    public function store(TagRequest $request)
    {
        Tag::create($request->only('name', 'order'));
        $data = ['status' => true];
        return $this->response->created('', compact('data'));
    }

    public function update(TagRequest $request, Tag $tag)
    {
        $tag->update($request->only('name', 'order'));
        $data = ['status' => true];
        return compact('data');
    }

    public function all()
    {
        $tags = Tag::orderBy('order', 'asc')->get();
        return $this->response->collection($tags, new TagTransformer);
    }
}