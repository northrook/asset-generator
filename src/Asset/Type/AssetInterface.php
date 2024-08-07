<?php

namespace Northrook\Asset\Type;

/**
 * @property-read string $type
 * @property-read string $assetID
 */
interface AssetInterface
{
    public function getHtml() : string;
}