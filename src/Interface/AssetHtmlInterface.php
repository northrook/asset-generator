<?php

declare(strict_types=1);

namespace Core\Assets\Interface;

use Core\Assets\Factory\Asset\Type;
use Stringable;

/**
 * Provides a fully resolved asset.
 *
 * @author  Martin Nielsen <mn@northrook.com>
 */
interface AssetHtmlInterface extends Stringable
{
    /**
     * @param string $name
     * @param string $assetID
     * @param Type   $type
     * @param string $html
     */
    public function __construct( string $name, string $assetID, Type $type, string $html );

    /**
     * @return string `dot.separated` lowercase
     */
    public function name() : string;

    /**
     * @return string `16` character alphanumeric hash
     */
    public function assetID() : string;

    /**
     * Returns the asset `type` by default.
     *
     * @param null|string|Type $is
     *
     * @return bool|Type
     */
    public function type( null|string|Type $is = null ) : Type|bool;

    /**
     * Returns fully resolved `HTML` of the asset.
     *
     * @return string|Stringable
     */
    public function getHTML() : string|Stringable;
}
