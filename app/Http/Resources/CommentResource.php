<?php

namespace App\Http\Resources;

class CommentResource extends Resource
{
    protected static $availableIncludes = ['user', 'children.user'];

    public function toArray($request)
    {
        $data = parent::toArray($request);

        return array_merge($data, [
            'content' => new ContentResource($this->content),
            'user' => new UserResource($this->whenLoaded('user')),
            'children' => CommentResource::collection($this->whenLoaded('children')),
        ]);
    }
}
