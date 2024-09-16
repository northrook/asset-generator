<?php

namespace Northrook\Assets\Asset\Interface;

/**
 * @property-read string $type
 * @property-read string $assetID
 */
interface InlineAsset
{
    public function getInlineHtml( bool $minify ) : string;
}