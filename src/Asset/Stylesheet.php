<?php

declare( strict_types = 1 );

namespace Northrook\Asset;

use Northrook\Asset\Type\Asset;
use Northrook\Asset\Type\InlineAssetInterface;
use Northrook\HTML\Element;
use Northrook\Minify;

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

        $this->getId();

        return $this->html = (string) new Element(
            tag        : 'style',
            attributes : $this->attributes,
            content    : (string) Minify::CSS( $stylesheet ),
        );

    }
}