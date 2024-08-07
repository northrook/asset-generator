<?php

namespace Northrook;

use Northrook\Asset\Type\AssetInterface;
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

    private array $enqueued = [];

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

    /**
     * @param 'all'|'script'|'style'  $type
     *
     * @return string[] of valid HTML
     */
    public function getEnqueued( string $type = 'all' ) : array {
        return match ( $type ) {
            'script'     => $this->enqueued[ 'script' ],
            'stylesheet' => $this->enqueued[ 'stylesheet' ],
            'all'        => \array_merge( ...\array_values( $this->enqueued ) ),
            default      => []
        };
    }

    public function enqueue(
        AssetInterface $asset,
        ?int           $cache = HOUR_4,
    ) : AssetManager {

        $this->enqueued[ $asset->type ][ $asset->assetID ] = $asset->getHtml();

        return $this;
    }

    public function inline(
        InlineAssetInterface $asset,
        ?int                 $cache = HOUR_4,
    ) : AssetManager {

        if ( isset( $this->enqueued[ $asset->type ][ $asset->assetID ] ) ) {
            throw new \LogicException( "Unable to enqueue this asset. It has already been enqueued." );
        }

        try {
            $inline = $this->cache?->get(
                $asset->assetID, static function ( CacheItem $item ) use ( $asset, $cache ) {
                $item->expiresAfter( $cache );
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


        $this->enqueued[ $asset->type ][ $asset->assetID ] = new InlineAsset( ...$inline );

        return $this;
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