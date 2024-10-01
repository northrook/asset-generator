<?php

namespace Northrook\Assets\Asset;

use Northrook\Assets\AssetManager\AssetResolver;
use Northrook\Clerk;
use Northrook\Filesystem\Resource;
use Northrook\Logger\Log;
use Northrook\Resource\{Path, URL};
use Support\ClassMethods;
use function Assert\isUrl;
use function String\{hashKey, sourceKey};
use const Support\EMPTY_STRING;

abstract class AbstractAssetInterface implements AssetInterface
{
    use ClassMethods;

    private URL|Path|string $source;

    /** @var array<string, mixed> */
    protected array $attributes = [];

    public readonly string $type;      // stylesheet, script, image, etc

    public readonly string $assetID;   // manual or using hashKey

    public function __construct(
        string|Resource $source,
        ?string         $assetID = null,
    ) {
        Clerk::event( static::class, 'document' );
        $this->type    = $this->assetType();
        $this->source  = $source;
        $this->assetID = $assetID ?? hashKey( $this );
    }

    public static function from(
        string|array|Path|AbstractAssetInterface $source,
        ?string                                  $id = null,
        array                                    $attributes = [],
    ) : static {
        trigger_deprecation( 'Assets', 'dev', __METHOD__ );
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
     * Lazily access the $source.
     *
     * @return Path|URL
     */
    final public function source() : URL|Path
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

        if ( ! $this->source()->exists ) {
            Log::error(
                '{assetClass} source {sourcePath} is not readable.',
                [
                    'assetClass' => $this->classBasename(),
                    'sourcePath' => $this->source()->path,
                ],
            );
            return EMPTY_STRING;
        }

        if ( $this->source() instanceof URL ) {
            return $this->source()->fetch;
        }

        return $this->source()->read;

    }

    protected function assetType() : string
    {
        return \strtolower( $this->classBasename() );
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
     * @param string $path
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