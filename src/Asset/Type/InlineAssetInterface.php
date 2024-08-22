<?php

namespace Northrook\Asset\Type;

/**
 * @property-read string $type
 * @property-read string $assetID
 */
interface InlineAssetInterface
{
    public function getInlineHtml( bool $minify ) : string;
}