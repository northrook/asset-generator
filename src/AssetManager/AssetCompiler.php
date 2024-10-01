<?php

declare(strict_types=1);

namespace Northrook\Assets\AssetManager;

use Northrook\Assets\Asset\InlineAsset;
use Northrook\Logger\Log;
use Northrook\Assets\{Script, Style};
use Northrook\Resource\Path;
use InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

// Run during Container compilation, this class creates an inventory of all prepared assets

final class AssetCompiler
{
    /** @var array<string, array<int, InlineAsset>> */
    private array $assets = [];

    /**
     * @param CacheInterface $cache
     */
    public function __construct(
        private readonly CacheInterface $cache,
    ) {}

    public function register( string $label, string|Path ...$source ) : self
    {
        foreach ( (array) $source as $source ) {
            $path = $source instanceof Path ? $source : new Path( $source );

            $this->assets[$label][] = match ( $path->mimeType ) {
                'text/css'        => new Style( $path, $label ),
                'text/javascript' => new Script( $path, $label ),
                default           => throw new InvalidArgumentException(),
            };
        }

        return $this;
    }

    /**
     * @param null|string $label
     *
     * @return null|array|InlineAsset[]|InlineAsset[][]
     */
    public function getRegistered( ?string $label = null ) : ?array
    {
        return $label ? ( $this->assets[$label] ?? [] ) : $this->assets;
    }

    public function recompile( ?string $label = null ) : void {}

    public function compile() : void
    {
        $html = [];

        foreach ( $this->assets as $label => $assets ) {
            foreach ( $assets as $asset ) {
                $html[$asset->type][] = $asset->getInlineHtml();
            }
            try {
                $this->cache->get( $label, static fn() => $html );
            }
            catch ( \Psr\Cache\InvalidArgumentException $e ) {
                Log::exception( $e );
            }
        }

    }
}
