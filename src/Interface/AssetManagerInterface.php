<?php

declare(strict_types=1);

namespace Core\Assets\Interface;

use Core\Assets\Factory\AssetReference;

/**
 * @author Martin Nielsen <mn@northrook.com>
 */
interface AssetManagerInterface
{
    /**
     * @param string                                    $asset
     * @param ?string                                   $assetID
     * @param array<string, null|bool|float|int|string> $attributes
     * @param bool                                      $nullable   [false] throw by default
     *
     * @return ($nullable is true ? null|AssetHtmlInterface : AssetHtmlInterface)
     */
    public function get(
        string  $asset,
        ?string $assetID = null,
        array   $attributes = [],
        bool    $nullable = false,
    ) : ?AssetHtmlInterface;

    /**
     * @param AssetReference|string $asset
     * @param ?string               $assetID
     * @param bool                  $nullable [false] throw by default
     *
     * @return ($nullable is true ? null|AssetModelInterface : AssetModelInterface )
     */
    public function getModel(
        string|AssetReference $asset,
        ?string               $assetID = null,
        bool                  $nullable = false,
    ) : ?AssetModelInterface;

    /**
     * @param string $asset
     * @param bool   $nullable [false] throw by default
     *
     * @return ($nullable is true ? null|AssetReference : AssetReference)
     */
    public function getReference(
        string $asset,
        bool   $nullable = false,
    ) : ?AssetReference;
}

// /**
// * Add one or more assets to be located when {@see self::getEnqueuedAssets} is called.
// *
// * @param string ...$name
// *
// * @return void
// */
// public function enqueueAsset( string ...$name ) : void;
//
// public function hasEnqueued( string $name ) : bool;
//
// /**
//  * Locate and return an {@see AssetInterface}.
//  *
//  * Implementing classes *must* ensure `null` returns on missing `assets` are logged using the provided {@see LoggerInterface}.
//  *
//  * @param string $name
//  *
//  * @param array<string, array<array-key|string>|string> $attributes
//  *
//  * @return ?AssetInterface
//  */
// public function renderAsset( string $name, array $attributes = [] ) : ?AssetInterface;
//
// /**
//  * Returns an array all `enqueued` assets as `HTML` strings.
//  *
//  * The resolved assets may be cached using the  provided {@see CacheInterface}.
//  *
//  * @param bool $cached
//  *
//  * @return array<string, AssetInterface>
//  */
// public function resolveEnqueuedAssets( bool $cached = true ) : array;
//
// /**
//  * Returns a list of all currently `enqueued` assets.
//  *
//  * @return string[]
//  */
// public function getEnqueuedAssets() : array;
