<?php

declare( strict_types = 1 );

namespace Northrook\Assets;

use Northrook\Assets\Asset\InlineAsset;
use Northrook\Filesystem\Resource;
use Northrook\HTML\Element;
use Northrook\Minify;
use const Northrook\EMPTY_STRING;


class Style extends InlineAsset
{

    public function __construct(
            string | Resource $source,
            ?string           $assetID = null,
            protected array   $attributes = [],
            protected ?string $prefix = null,
    )
    {
        parent::__construct( $source, $assetID );

        $this->attributes[ 'data-asset' ] = $this->assetID;
    }

    protected function getAssetHtml( bool $minify ) : string
    {
        $this->attributes[ 'rel' ] = 'stylesheet';
        $this->attributes[ 'src' ] = $this->source()->path;
        return (string) new Element( 'link', $this->attributes );
    }

    protected function getInlineAssetHtml( bool $minify ) : string
    {
        if ( !$stylesheet = $this->sourceContent() ) {
            return EMPTY_STRING;
        }

        if ( $minify ) {
            $stylesheet = (string) Minify::CSS( $stylesheet );
        }

        return (string) new Element(
                tag        : 'style',
                attributes : $this->attributes,
                content    : $stylesheet,
        );
    }
}