<?php

namespace App\Http\Resources;

/**
 * Class ArticleResource
 * @property \App\Models\Article $resource
 * @package App\Http\Resources
 */
class ArticleResource extends Resource
{
    protected static $availableIncludes = ['user', 'tags'];

    public function toArray($request)
    {
        $data = parent::toArray($request);

        return array_merge($data, [
            'content' => new ContentResource($this->resource->content),
            'user' => new UserResource($this->whenLoaded('user')),
            'tags' => new TagResource($this->whenLoaded('tags')),
        ]);
    }
}
