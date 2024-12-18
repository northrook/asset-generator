<?php

declare(strict_types=1);

namespace Core\Assets\Interface;

use Core\Assets\Factory\AssetReference;

interface AssetManifestInterface
{
    /**
     * Check if the Manifest has a given {@see AssetReference} by `name` or `object`.
     *
     * @param AssetReference|string $asset
     *
     * @return bool
     */
    public function has( string|AssetReference $asset ) : bool;

    /**
     * Retrieve a {@see AssetReference} by `name`.
     *
     * @param string $asset
     * @param bool   $nullable [false] throw by default
     *
     * @return null|AssetReference
     */
    public function get( string $asset, bool $nullable = false ) : ?AssetReference;

    /**
     * Register a provided {@eee \Core\Assets\Factory\AssetReference}.
     *
     * The `name` is derived from the `$reference->name`.
     *
     * @param AssetReference $reference
     *
     * @return self
     */
    public function register( AssetReference $reference ) : self;
}
