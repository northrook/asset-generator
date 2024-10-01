<?php

namespace Northrook\Assets;

use Northrook\Assets\Asset\AbstractAssetInterface;
use Northrook\Filesystem\Resource;
use Northrook\HTML\Element;

class Link extends AbstractAssetInterface
{
    public function __construct(
        string|Resource $href,
        ?string         $assetID = null,
        protected array $attributes = [],
    ) {
        parent::__construct( $href, $assetID );
        $this->attributes['data-asset'] = $this->assetID;
    }

    protected function getAssetHtml( bool $minify ) : string
    {
        $href = $this->source()->path;

        if ( ! \array_key_exists( 'rel', $this->attributes ) ) {
            $this->attributes['rel'] = match ( true ) {
                \str_ends_with( $href, 'css' ) => 'stylesheet',
                \str_ends_with( $href, 'ico' ) => 'icon',
                default                        => null,
            };
        }

        return (string) Element::link( $href, $this->attributes );
    }
}
