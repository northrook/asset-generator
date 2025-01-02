<?php

declare(strict_types=1);

namespace Core\Assets;

// ? Intended to be extended by the Framework

use Core\Assets\Factory\{AssetReference};
use Core\Assets\Interface\{AssetHtmlInterface, AssetManagerInterface, AssetModelInterface};
use Core\Assets\Exception\{InvalidAssetTypeException, UndefinedAssetReferenceException};
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Provides the Asset Manager Service to the Framework.
 *
 * Get Asset:
 * - HtmlData `get`
 * - FactoryModel `getModel`
 * - ManifestReference `getReference`
 *
 * Public access:
 * - Locator
 * - Factory
 */
#[Autoconfigure(
    lazy   : true,   // lazy-load using ghost
    public : false,  // private
)]
abstract class AssetManager implements AssetManagerInterface
{
    public function __construct(
        public readonly AssetFactory        $factory, // internal
        protected readonly ?CacheInterface  $cache = null,
        protected readonly ?LoggerInterface $logger = null,
    ) {
    }

    final public function get(
        string  $asset,
        ?string $assetID = null,
        array   $attributes = [],
        bool    $nullable = false,
    ) : ?AssetHtmlInterface {
        try {
            return $this->factory->getAssetHtml( $asset, $assetID, $attributes );
        }
        catch ( InvalidAssetTypeException $exception ) {
            $this->logger?->critical(
                $exception->getMessage(),
                ['exception' => $exception],
            );
        }
        return $nullable ? null : throw $exception;
    }

    final public function getModel(
        AssetReference|string $asset,
        ?string               $assetID = null,
        bool                  $nullable = false,
    ) : ?AssetModelInterface {
        try {
            return $this->factory->getAssetModel( $asset, $assetID );
        }
        catch ( InvalidAssetTypeException $exception ) {
            $this->logger?->critical(
                $exception->getMessage(),
                ['exception' => $exception],
            );
        }
        return $nullable ? null : throw $exception;
    }

    final public function getReference( string $asset, bool $nullable = false ) : ?AssetReference
    {
        try {
            return $this->factory->resolveAssetReference( $asset );
        }
        catch ( UndefinedAssetReferenceException $exception ) {
            $this->logger?->critical(
                $exception->getMessage(),
                ['exception' => $exception],
            );
        }
        return $nullable ? null : throw $exception;
    }
}
