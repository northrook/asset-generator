<?php

namespace Northrook\Assets\Asset\Interface;

/**
 * @property-read string $type
 * @property-read string $assetID
 */
interface Asset
{
    public function getHtml() : string;

    public function sourceContent() : ?string;
}