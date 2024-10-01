<?php

namespace Northrook\Assets\Asset;

use Northrook\Clerk;

abstract class InlineAsset extends AbstractAssetInterface
{
    abstract protected function getInlineAssetHtml( bool $minify ) : string;

    /**
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    final protected function attributes( array $attributes = [] ) : array
    {
        return \array_merge( $this->attributes, $attributes, [
            'id'         => $this->assetID,
            'data-asset' => $this->label ?? $this->assetID,
        ] );
    }

    final public function getInlineHtml( bool $minify = true ) : string
    {
        Clerk::event( static::class )->stop();
        return $this->getInlineAssetHtml( $minify );
    }
}
