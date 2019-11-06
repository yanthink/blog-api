<?php

namespace App\Http\Resources;

use App\Html2wxml\ToWXML;
use App\Models\Article;
use Parsedown;

/**
 * Class ContentResource
 * @property \App\Models\Content $resource
 * @package App\Http\Resources
 */
class ContentResource extends Resource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);

        if ($request->has('htmltowxml') && $request->header('X-Client') == 'wechat') {
            do {
                if (
                    $this->resource->contentable_type == Article::class &&
                    !$request->routeIs('articles.show')
                ) {
                    $data['htmltowxml'] = [];
                    break;
                }

                $body = Parsedown::instance()->setBreaksEnabled(true)->text($this->resource->combine_markdown);

                $data['htmltowxml'] = app(ToWXML::class)->towxml($body, [
                    'type' => 'html',
                    'highlight' => true,
                    'linenums' => $request->has('htmltowxml_linenums'),
                    'imghost' => null,
                    'encode' => false,
                    'highlight_languages' => [
                        'php',
                        'javascript',
                        'typescript',
                        'java',
                        'css',
                        'less',
                        'bash',
                        'ini',
                        'json',
                        'sql',
                    ],
                ]);
            } while (false);
        }

        return $data;
    }
}
