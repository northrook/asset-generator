<?php

declare( strict_types = 1 );

namespace Northrook\Asset;

use Northrook\Asset\Type\Asset;
use Northrook\Asset\Type\InlineAssetInterface;
use Northrook\Filesystem\Resource;
use Northrook\HTML\Element;
use Northrook\Minify;
use const Northrook\EMPTY_STRING;

class Style extends Asset implements InlineAssetInterface
{
    protected const ?string TYPE = 'stylesheet';

    public function __construct(
        string | Resource $source,
        protected array   $attributes = [],
        protected ?string $prefix = null,
    ) {
        parent::__construct( source : $source );
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