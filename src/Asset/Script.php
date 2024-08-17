<?php

namespace Northrook\Asset;

use Northrook\Asset\Type\Asset;
use Northrook\Asset\Type\InlineAssetInterface;
use Northrook\HTML\Element;
use Northrook\Minify;
use Symfony\Component\Filesystem\Exception\IOException;
use function Northrook\sourceKey;

class Script extends Asset implements InlineAssetInterface
{
    public function __construct(
        string            $source,
        protected array   $attributes = [],
        protected ?string $prefix = null,
    ) {
        parent::__construct(
            type   : 'script',
            source : $source,
        );
    }

    public function build() : static {
        return $this;
    }

    public function getHtml() : string{
        return $this->html = __METHOD__;
    }

    public function getInlineHtml() : string {

        $script = $this->sourceContent();



        return $this->html = (string) new Element(
            tag        : 'script',
            attributes : $this->attributes,
            content    :(string) Minify::JS( $script ),
        );
    }
}