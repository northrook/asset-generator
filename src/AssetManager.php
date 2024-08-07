<?php

namespace Northrook;

use Northrook\Asset\Type\InlineAsset;
use Northrook\Asset\Type\InlineAssetInterface;
use Northrook\Core\Trait\SingletonClass;
use Northrook\Logger\Log;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\CacheInterface;

final class AssetManager
{
    use SingletonClass;

    public readonly string $projectRoot;
    public readonly string $projectStorage;
    public readonly string $publicRoot;
    public readonly string $publicAssets;

    public function __construct(
        ?string                          $projectRoot,
        ?string                          $projectStorage,
        ?string                          $publicRoot,
        ?string                          $publicAssets,
        private readonly ?CacheInterface $cache = null,
    ) {
        $this->instantiationCheck();


        $this::$instance = $this;
    }

    public function inline( InlineAssetInterface $asset, ?int $persistence = HOUR_4 ) : InlineAsset {
        try {
            $inline = $this->cache?->get(
                $asset->assetID, static function ( CacheItem $item ) use ( $asset, $persistence ) {
                $item->expiresAfter( $persistence );
                return [
                    $asset->type,
                    $asset->assetID,
                    $asset->getInlineHtml(),
                ];
            },
            );
        }
        catch ( InvalidArgumentException $exception ) {
            Log::exception( $exception );
            $inline = [
                $asset->type,
                $asset->assetID,
                $asset->getInlineHtml(),
            ];
        }

        return new InlineAsset( ...$inline );
    }

    public static function get() : AssetManager {
        return AssetManager::$instance;
    }

    private function resolveInlineAsset( InlineAssetInterface $asset ) : array {
        return [
            $asset->type,
            $asset->assetID,
            $asset->getInlineHtml(),
        ];
    }
}