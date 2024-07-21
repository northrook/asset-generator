<?php

namespace Northrook\Asset;

use Northrook\AssetGenerator\MappedAsset;
use Northrook\AssetGenerator\StaticAsset;
use Northrook\HTML\Element;
use Northrook\Minify;

// The system might get renamed to just "Assets" , or merged with Asset Manager
// who knows at this point

// Generates a basic <img> by default
// We can then either extend this elsewhere, like the Asset Manager,
// or do some optimisation here, like we do with JS and CSS using Minify

class Image extends MappedAsset
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