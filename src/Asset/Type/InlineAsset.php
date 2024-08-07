<?php

namespace Northrook\Asset\Type;

final class InlineAsset implements \Stringable
{
    public function __construct(
        public readonly string $type,      // stylesheet, script, image, etc
        public readonly string $assetID,   // manual or using hashKey
        public readonly string $html,
    ) {}

    public function __toString() : string {
        return $this->html;
    }
}