<?php

declare( strict_types = 1 );

namespace Northrook\Assets;

use Northrook\Assets\Asset\InlineAsset;
use Northrook\Filesystem\Resource;
use Northrook\HTML\Element;
use Northrook\Minify;
use const Northrook\EMPTY_STRING;

class Script extends InlineAsset
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
        $this->attributes[ 'defer' ]      ??= 'true';
    }

    protected function getAssetHtml( bool $minify ) : string
    {
        $this->attributes[ 'link' ] = $this->source()->path;
        return (string) new Element( 'script', $this->attributes );
    }

    protected function getInlineAssetHtml( bool $minify ) : string
    {
        if ( !$script = $this->sourceContent() ) {
            return EMPTY_STRING;
        }

        if ( $minify ) {
            $script = Minify::JS( $script );
        }

        return (string) new Element(
                tag        : 'script',
                attributes : $this->attributes,
                content    : $script,
        );
    }
}