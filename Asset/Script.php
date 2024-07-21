<?php

namespace Northrook\Asset;

use Northrook\AssetGenerator\StaticAsset;
use Northrook\HTML\Element;
use Northrook\Minify;

class Script extends StaticAsset
{
    protected const FILETYPE = 'js';

    public function __construct(
        string $source,
        array  $attributes = [],
        bool   $inline = false,
    ) {
        parent::__construct( 'script', $source, $attributes, $inline, );
    }

    protected function build() : Element {
        if ( $this->inline ) {
            return new Element(
                'script',
                $this->attributes(),
                Minify::JS( $this->file->read ),
            );
        }

        return new Element(
            'script',
            $this->attributes( set : [ 'src' => $this->getPublicURL() ] ),
        );
    }
}