<?php

declare( strict_types = 1 );

namespace Northrook\Asset;

use Northrook\Asset\Type\Asset;
use Northrook\Asset\Type\InlineAssetInterface;
use Northrook\Filesystem\Resource;
use Northrook\HTML\Element;
use Northrook\Minify;
use const Northrook\EMPTY_STRING;

class Script extends Asset implements InlineAssetInterface
{
    protected const ?string TYPE = 'script';

    public function __construct(
        string | Resource $source,
        protected array   $attributes = [],
        protected ?string $prefix = null,
    ) {
        parent::__construct( source : $source );
    }

    public function build() : static {
        return $this;
    }

    public function getHtml() : string {
        return $this->html = __METHOD__;
    }

    public function getInlineHtml( bool $minify = true ) : string {

        if ( !$script = $this->sourceContent() ) {
            return EMPTY_STRING;
        }

        if ( $minify ) {
            $script = (string) Minify::JS( $script );
        }

        $this->getId();

        return $this->html = (string) new Element(
            tag        : 'script',
            attributes : $this->attributes,
            content    : $script,
        );
    }
}