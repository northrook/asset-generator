<?php

declare(strict_types=1);

namespace Core\Assets\Interface;

interface AssetManifestInterface
{
    /**
     * Check if the Manifest has a given {@see AssetReferenceInterface} by `name` or `object`.
     *
     * @param AssetReferenceInterface|string $asset
     *
     * @return bool
     */
    public function has( string|AssetReferenceInterface $asset ) : bool;

    /**
     * Retrieve a {@see AssetReferenceInterface} by `name`.
     *
     * @param string $asset
     * @param bool   $nullable [false] throw by default
     *
     * @return null|AssetReferenceInterface
     */
    public function get( string $asset, bool $nullable = false ) : ?AssetReferenceInterface;

    /**
     * Register a provided {@eee AssetReferenceInterface}.
     *
     * The `name` is derived from the `$reference->name`.
     *
     * @param AssetReferenceInterface $reference
     *
     * @return self
     */
    public function register( AssetReferenceInterface $reference ) : self;
}
