<?php

namespace Northrook\Assets\AssetManager;

use Northrook\Assets\Asset\AbstractAsset;
use Northrook\Assets\Script;
use Northrook\Assets\Style;
use Northrook\Exception\ValueError;
use Northrook\Get;
use Northrook\Logger\Log;
use Northrook\Resource\Path;
use Northrook\Trait\PropertyAccessor;


/**
 * @property-read  Script[]|Style[] $assets
 *
 * @author Martin Nielsen <mn@northrook.com>
 */
final class AssetResolver implements \Countable
{
    use PropertyAccessor;


    private readonly array $source;

    private array $array = [];

    public function __construct(
        string | array | Path | Style | Script $sources = [],
        private readonly string                $assetClass = AbstractAsset::class,
    )
    {
        $this->source = \is_array( $sources ) ? $sources : [ $sources ];
    }

    public function __get( string $property )
    {
        return match ( $property ) {
            'assets' => $this->array,
        };
    }

    /**
     *
     * @return AssetResolver
     */
    public function resolve() : AssetResolver
    {
        foreach ( $this->source as $asset ) {
            if ( $asset instanceof AbstractAsset ) {
                $this->array[] = $asset;
                continue;
            }

            if ( $asset instanceof Path ) {
                $this->array[] = $this->resolveAsset( $asset );
                continue;
            }
            
            if ( \str_starts_with( $asset, 'dir.' ) || \str_contains( $asset, '*' ) ) {
                $this->directoryParser( $asset );
                continue;
            }

            if ( $asset = $this->resolveAsset( $asset ) ) {
                $this->array[] = $asset;
            }
        }

        return $this;
    }

    public function merge() : ?AbstractAsset
    {
        if ( empty( $this->array ) ) {
            $this->resolve();
        }

        $type  = null;
        $merge = [];

        foreach ( $this->array as $path => $asset ) {
            $type           ??= $asset::class;
            $merge[ $path ] = $asset->sourceContent();
        }

        if ( !$type ) {
            Log::error( 'No assets available to merge.' );
            return null;
        }

        $merge = \implode( ' ', $merge );

        return new $type( $merge );
    }

    /**
     * @return Script[]|Style[]
     */
    public function assets() : array
    {
        return $this->array;
    }

    private function directoryParser( string $get ) : void
    {
        $directory = \strstr( $get, '/', true ) ?: $get;
        $glob      = \strstr( $get, '/' );

        // if ( !\str_contains( $glob, '*' ) ) {
        //     if ( !\str_ends_with( $glob, '/' ) ) {
        //         $glob .= '/';
        //     }
        //     $glob .= '*';
        // }

        $directory = Get::path( $directory, true );

        if ( !( $directory->isDir && $directory->exists ) ) {
            Log::exception( new ValueError( 'Unable to parse AbstractAsset Directory.' ) );
            return;
        }

        $directoryAssets = \glob( $directory->path . $glob );

        if ( empty( $directoryAssets ) ) {
            return;
        }

        foreach ( $directoryAssets as $directoryAsset ) {
            if ( $asset = $this->resolveAsset( $directoryAsset ) ) {
                $this->array[ $asset->source()->path ] = $asset;
            }
        }
    }

    private function resolveAsset( mixed $asset ) : Style | Script | null
    {
        if ( $asset instanceof Style || $asset instanceof Script ) {
            return $asset;
        }

        $path = $asset instanceof Path ? $asset : Get::path( $asset, true );

        if ( $path->isDir ) {
            return null;
        }

        $asset = match ( $path->extension ) {
            'css'   => new Style( $path->path ),
            'js'    => new Script( $path->path ),
            default => null,
        };

        if ( !$asset instanceof $this->assetClass ) {
            Log::notice(
                'Invalid AbstractAsset filetype {extension} has been skipped.',
                [
                    'extension' => $path->extension,
                ],
            );
            return null;
        }

        return $asset;
    }

    private function array( mixed $parse ) : array
    {
        return \is_array( $parse ) ? $parse : [ $parse ];
    }

    public function count() : int
    {
        return \count( $this->assets );
    }
}