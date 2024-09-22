<?php

namespace Northrook\Assets\Asset;

use Northrook\Clerk;


abstract class InlineAsset extends AbstractAsset
{
    abstract protected function getInlineAssetHtml( bool $minify ) : string;

    final public function getInlineHtml( bool $minify = true ) : string
    {
        Clerk::event( static::class )->stop();
        return $this->getInlineAssetHtml( $minify );
    }
}