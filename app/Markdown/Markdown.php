<?php

namespace App\Markdown;

class Markdown
{
    protected $parser;

    /**
     * Markdown constructor.
     *
     * @param $parser
     */
    public function __construct(Parsedown $parser)
    {
        $this->parser = $parser;
    }

    public function toHtml($text)
    {
        $this->parser->setBreaksEnabled(true);
        $this->parser->setSafeMode(true);
        $this->parser->setMarkupEscaped(true);
        $html = $this->parser->text($text);

        return $html;
    }

}