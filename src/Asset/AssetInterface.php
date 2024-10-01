<?php

namespace Northrook\Assets\Asset;

/**
 * @property-read string $type
 * @property-read string $assetID
 */
interface AssetInterface
{
    public function getHtml() : string;

    public function sourceContent() : ?string;
}
