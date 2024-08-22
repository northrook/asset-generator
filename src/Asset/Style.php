<?php

declare( strict_types = 1 );

namespace Northrook\Asset;

use Northrook\Asset\Type\Asset;
use Northrook\Asset\Type\InlineAssetInterface;
use Northrook\HTML\Element;
use Northrook\Minify;
use const Northrook\EMPTY_STRING;

class Style extends Asset implements InlineAssetInterface
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

    public function getHtml() : string {
        return $this->html = __METHOD__;
    }

    public function getInlineHtml( bool $minify = true ) : string {

        if ( !$stylesheet = $this->sourceContent() ) {
            return EMPTY_STRING;
        }

        if ( $minify ) {
            $stylesheet = (string) Minify::CSS( $stylesheet );
        }

        $this->getId();

        return $this->html = (string) new Element(
            tag        : 'style',
            attributes : $this->attributes,
            content    : $stylesheet,
        );

    }
}