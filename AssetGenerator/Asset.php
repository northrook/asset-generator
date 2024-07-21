<?php

declare( strict_types = 1 );

namespace Northrook\AssetGenerator;

use Northrook\HTML\Element;
use Northrook\Resource\Path;
use Northrook\Resource\URL;

abstract class Asset implements \Stringable
{
    private static array $directories = [];

    protected Element $element;
    protected URL | Path $source;

    public readonly string $type;    // stylesheet, script, image, etc
    public readonly string $assetID; // manual or using hashKey

    final protected function setAssetType( string $string ) : void {
        $this->type = $string;
    }

    /**
     * Build the asset. Must return valid HTML.
     *
     * @return Element
     */
    abstract protected function build() : Element;

    final public function getHtml( bool $forceRecompile = false ) : string {
        return $this->build()->toString();
    }

    final public function getElement() : Element {
        return $this->build();
    }

    final protected function setAssetID( string $string ) : void {
        $this->assetID = $string;
    }

    final protected function context() : AssetContext {
        return AssetContext::get();
    }

    protected function attributes(
        ?array $add = null,
        ?array $set = null,
    ) : array {

        $this->attributes[ 'id' ] = "asset-{$this->type}-" . pathinfo( $this->getPublicURL(), PATHINFO_FILENAME );

        if ( $add ) {
            $this->attributes += $add;
        }

        if ( $set ) {
            $this->attributes = array_merge( $this->attributes, $set );
        }

        return $this->attributes;
    }
}