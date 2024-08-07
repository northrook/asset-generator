<?php

namespace Northrook\Asset\Type;

use Northrook\HTML\Element;
use Northrook\Resource\Path;
use Northrook\Resource\URL;
use Symfony\Component\Filesystem\Exception\IOException;
use function Northrook\hashKey;
use function Northrook\isUrl;
use function Northrook\normalizeKey;
use function Northrook\sourceKey;

abstract class Asset implements \Stringable
{
    private URL | Path | string $source;

    protected array $attributes = [];

    protected ?string $html = null;

    public readonly string $type;      // stylesheet, script, image, etc
    public readonly string $assetID;   // manual or using hashKey

    public function __construct(
        string $type,
        string $source,
        mixed  $assetID = null,
    ) {
        $this->type    = normalizeKey( $type );
        $this->source  = $source;
        $this->assetID = hashKey( $assetID ?? get_defined_vars() );
    }


    /**
     * Build the asset. Must return valid HTML.
     *
     * @return $this
     */
    abstract public function build() : static;

    public function __toString() : string {
        return $this->html ??= $this->build()->html;
    }

    /**
     * Lazily access the $source
     *
     * @return URL|Path
     */
    final protected function source() : URL | Path {

        if ( \is_string( $this->source ) ) {
            $this->source = isUrl( $this->source )
                ? new URL( $this->source )
                : new Path( $this->source );
        }

        return $this->source;
    }

    final protected function sourceContent() : ?string {

        if ( !$this->source()->exists ) {
            throw new IOException( 'Source is not readable.' );
        }

        if ( $this->source() instanceof URL ) {
            return $this->source()->fetch;
        }
        else {
            return $this->source()->read;
        }
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
    final public static function generateFilenameKey( string $path ) : string {
        $trimmed = \preg_replace(
            '/^(?:\w *:\/\/)*(.*?)( \?.*)?$/m',
            '$1',
            $path,
        );
        return sourceKey( $trimmed ?? $path );
    }
}