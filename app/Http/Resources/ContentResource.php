<?php

namespace App\Http\Resources;

use App\Html2wxml\ToWXML;
use Parsedown;

class ContentResource extends Resource
{
    public function toArray($request)
    {
        $data = parent::toArray($request);

        if ($request->has('htmltowxml') && $request->header('X-Client') == 'wechat') {
            $body = Parsedown::instance()->setBreaksEnabled(true)->text($this->markdown);

            $data['htmltowxml'] = app(ToWXML::class)->towxml($body, [
                'type' => 'html',
                'highlight' => true,
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
        }

        return $data;
    }
}
