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
        $this->html = __METHOD__;
        return $this;
    }


    public function getInlineHtml() : string {

        $script = $this->sourceContent();

        $this->attributes[ 'id' ] = ( $this->prefix ? "$this->prefix-" : '' ) . sourceKey( $this->source() );

        return (string) new Element(
            tag        : 'script',
            attributes : $this->attributes,
            content    :(string) Minify::JS( $script ),
        );
    }
}