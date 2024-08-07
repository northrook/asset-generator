<?php

namespace Northrook\Asset;

use Northrook\Asset\Type\Asset;
use Northrook\Asset\Type\InlineAssetInterface;
use Northrook\HTML\Element;
use Northrook\Minify;
use Symfony\Component\Filesystem\Exception\IOException;
use function Northrook\sourceKey;

class Stylesheet extends Asset implements InlineAssetInterface
{

    public function __construct(
        string            $source,
        protected array   $attributes = [],
        protected ?string $prefix = null,
    ) {
        parent::__construct(
            type   : 'stylesheet',
            source : $source,
        );
    }

    public function build() : static {
        $this->html = __METHOD__;
        return $this;
    }

    public function getHtml() : string{
        return $this->html = __METHOD__;
    }

    public function getInlineHtml() : string {

        $stylesheet = $this->sourceContent();

        $this->attributes[ 'id' ] = ( $this->prefix ? "$this->prefix-" : '' ) . sourceKey( $this->source() );

        return $this->html = (string) new Element(
            tag        : 'style',
            attributes : $this->attributes,
            content    : (string) Minify::CSS( $stylesheet ),
        );

    }
}