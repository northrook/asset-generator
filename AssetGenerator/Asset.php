<?php

declare( strict_types = 1 );

namespace Northrook\AssetGenerator;

use Northrook\HTML\Element;
use Northrook\Resource\Path;
use Northrook\Resource\URL;
use function Northrook\normalizeKey;
use function Northrook\normalizePath;

abstract class Asset implements \Stringable
{
    private static array $directories;

    protected Element    $element;
    protected URL | Path $source;

    public readonly string $type;    // stylesheet, script, image, etc
    public readonly string $assetID; // manual or using hashKey

    final protected function setAssetType( string $string ) : void {
        $this->type = $string;
    }

    final protected function setAssetID( string $string ) : void {
        $this->assetID = $string;
    }

    /**
     * Build the asset. Must return valid HTML.
     *
     * @return Element
     */
    abstract protected function build() : Element;

    final public function getElement() : Element {
        return $this->build();
    }

    final public function getHtml( bool $forceRecompile = false ) : string {
        return $this->build()->toString();
    }

    final protected function projectRoot( ?string $append = null ) : string {
        return normalizePath( [ static::$directories[ 'projectRoot' ], $append ] );
    }

    final protected function projectStorage( ?string $append = null ) : string {
        return normalizePath( [ static::$directories[ 'projectStorage' ], $append ] );
    }

    final protected function publicRoot( ?string $append = null ) : string {
        return normalizePath( [ static::$directories[ 'publicRoot' ], $append ] );
    }

    final protected function publicAssets( ?string $append = null ) : string {
        return normalizePath( [ static::$directories[ 'publicAssets' ], $append ] );
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
        $trimmed = \preg_replace( '/^(?:\w*:\/\/)*(.*?)(\?.*)?$/m', '$1', $path );
        return normalizeKey( $trimmed ?? $path );
    }

    final public static function setDirectories(
        string $projectRoot,
        string $projectStorage,
        string $publicRoot,
        string $publicAssets,
    ) : void {
        static::$directories = [
            'projectRoot'    => normalizePath( $projectRoot ),
            'projectStorage' => normalizePath( $projectStorage ),
            'publicRoot'     => normalizePath( $publicRoot ),
            'publicAssets'   => normalizePath( $publicAssets ),
        ];
    }
}