<?php

namespace Northrook\Assets\Asset;

use Northrook\Assets\Asset\Interface\Asset;
use Northrook\Assets\AssetManager\AssetResolver;
use Northrook\Clerk;
use Northrook\Filesystem\Resource;
use Northrook\Logger\Log;
use Northrook\Resource\Path;
use Northrook\Resource\URL;
use function Northrook\classBasename;
use function Northrook\hashKey;
use function Northrook\isUrl;
use function Northrook\sourceKey;
use const Northrook\EMPTY_STRING;


abstract class AbstractAsset implements Asset
{
    private URL | Path | string $source;

    protected array $attributes = [];

    public readonly string $type;      // stylesheet, script, image, etc
    public readonly string $assetID;   // manual or using hashKey

    public function __construct(
            string | Resource $source,
            mixed             $assetID = null,
    )
    {
        Clerk::event( static::class, 'document' );
        // dd( $source, $assetID );
        $this->type    = $this->assetType();
        $this->source  = $source;
        $this->assetID = $assetID ?? hashKey( \get_defined_vars() );
    }

    public static function from(
            string | array | Path | AbstractAsset $source,
            ?string                               $id = null,
    ) : static
    {
        $resolver = new AssetResolver( $source, static::class );

        return new static( $resolver->merge()->sourceContent(), $id );
    }

    abstract protected function getAssetHtml( bool $minify ) : string;

    final public function getHtml( bool $minify = true ) : string
    {
        Clerk::event( static::class )->stop();
        return $this->getAssetHtml( $minify );
    }

    /**
     * Lazily access the $source
     *
     * @return URL|Path
     */
    final public function source() : URL | Path
    {
        // Evaluate string sources
        if ( \is_string( $this->source ) ) {
            $this->source = isUrl( $this->source )
                    ? new URL( $this->source )
                    : new Path( $this->source );
        }

        return $this->source;
    }

    final public function sourceContent() : ?string
    {
        if ( \is_string( $this->source ) ) {
            return $this->source;
        }

        if ( !$this->source()->exists ) {
            Log::error(
                    '{assetClass} source {sourcePath} is not readable.',
                    [
                            'assetClass' => classBasename( $this::class ),
                            'sourcePath' => $this->source()->path,
                    ],
            );
            return EMPTY_STRING;
        }

        if ( $this->source() instanceof URL ) {
            return $this->source()->fetch;
        }
        else {
            return $this->source()->read;
        }
    }

    protected function assetType() : string
    {
        return \strtolower( classBasename( $this::class ) );
    }

    final public function getId( ?string $prefix = null ) : string
    {
        return $this->attributes[ 'id' ] ??= ( function() use ( $prefix )
        {
            $id = sourceKey( $this->source(), '-' );

            if ( \str_starts_with( $id, 'vendor-' ) ) {
                $id = \substr( $id, 7 );
            }

            $id = \str_replace( [ 'northrook-', '-src-' ], [ '', '-' ], $id );

            $id = \implode( '-', \array_flip( \array_flip( explode( '-', $id ) ) ) );

            return $prefix ? "$prefix-$id" : $id;
        } )();
    }

    /**
     * Generate a key based on the {@see $path}.
     *
     * - Strips URI schema and parameters
     *
     * ```
     *        |      matched     |
     * https://unpkg.com/htmx.org?v=1720704985
     *
     * ```
     *
     * @param string  $path
     *
     * @return string
     */
    final public static function generateFilenameKey( string $path ) : string
    {
        $trimmed = \preg_replace(
                '/^(?:\w *:\/\/)*(.*?)( \?.*)?$/m',
                '$1',
                $path,
        );
        return sourceKey( $trimmed ?? $path );
    }
}