<?php

namespace Northrook\Asset;

use Northrook\AssetGenerator\StaticAsset;
use Northrook\HTML\Element;
use Northrook\Minify;

class Stylesheet extends StaticAsset
{
    protected const FILETYPE = 'css';

    public function __construct(
        string  $source,
        array   $attributes = [],
        bool    $inline = false,
        ?string $prefix = null,
    ) {
        $this->element = new Element( 'link', $attributes );
        parent::__construct( 'stylesheet', $source, $attributes, $inline, $prefix );
    }


    protected function build() : Element {

        $this->element->id->add(
            pathinfo( $this->getPublicURL(), PATHINFO_FILENAME ) . "-{$this->type}" ,
        );

        if ( $this->inline ) {
            $this->element->tag( 'style' );
            $this->element->append(
                Minify::CSS( $this->file->read ),
            );
        }
        else {
            $this->element->set( 'href', $this->getPublicURL() )
                          ->set( 'rel', 'stylesheet' );
        }

        return $this->element;
    }
}