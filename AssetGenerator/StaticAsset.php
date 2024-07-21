<?php

namespace Northrook\AssetGenerator;

use Northrook\HTML\Element;
use Northrook\Resource\Path;
use Northrook\Resource\URL;
use function Northrook\hashKey;
use function Northrook\isUrl;
use function Northrook\normalizeKey;
use function Northrook\normalizePath;

abstract class StaticAsset extends Asset
{
    protected const FILETYPE = null;

    protected Path $file;

    public function __construct(
        string      $type,
        string      $source,
        array       $attributes,
        public bool $inline,
    ) {
        $this->setAssetType( $type );
        $this->setAssetID( hashKey( [ $type, $source, ... $attributes, $inline ] ) );
        $this->generateStaticAssetFile( $source );
    }


    final public function fetchRemoteAsset() : bool {
        dump( 'Fetchin' );
        return $this->file->save( $this->source->fetch );
    }

    private function generateStaticAssetFile( string $source ) : void {
        $this->source = isUrl( $source ) ? new URL( $source ) : new Path( $source );


        if ( $this->source instanceof URL ) {
            $this->file = $this->getAssetPublicPath(
                new Path(
                    AssetContext::get()->publicAssetsDirectory . '/cached/'
                    . $this::generateFilenameKey( $this->source ) . '.' . $this::FILETYPE,
                ),
            );

            if ( !$this->file->exists ) {
                if ( !$this->source->exists ) {
                    throw new InvalidSourceException( 'The requested source "' . $source->path . '" does not exist.' );
                }
                $this->fetchRemoteAsset();
            }
        }
        else {
            if ( !$this->source->exists ) {
                throw new InvalidSourceException( 'The requested source "' . $source->path . '" does not exist.' );
            }
            // The 'public' path
            $this->file = $this->getAssetPublicPath( $this->source );

            if ( $this->file->extension != $this::FILETYPE ) {
                throw new InvalidSourceException(
                    "The static asset " . $this::class . " expected a filetype of '" . $this::FILETYPE
                    . "' but the source provided is a '{$this->file->extension}' file.",
                );
            }

            if ( $this->source->lastModified > $this->file->lastModified ) {
                $this->source->copy( $this->file->path );
            }
        }
    }

    final protected function getPublicURL() : string {
        if ( !isset( $this->file ) ) {
            throw new InvalidSourceException( 'The static asset source file has not yet been set.' );
        }

        $path = \substr( $this->file->path, \strlen( AssetContext::get()->publicDirectory ) );
        return '/' . \ltrim( \str_replace( '\\', '/', $path ), '/' . $this->publicAssetVersion() );
    }


    final protected function publicAssetVersion() : string {
        return "?v={$this->file->lastModified}";
    }

    final protected function getAssetPublicPath( Path $source ) : Path {

        $vendor = AssetContext::get()->projectDirectory . DIRECTORY_SEPARATOR . 'vendor';

        $asset = [
            'base'      => AssetContext::get()->publicAssetsDirectory,
            'directory' => $this->type,
        ];

        // Parse the bundle name if the $source is from a Composer package
        if ( \str_starts_with( $source->path, $vendor ) ) {
            // Remove until the /vendor/ directory
            $bundle = \substr( $source->path, \strlen( $vendor ) + 1 );
            // Remove the package vendor directory
            $bundle = \substr( $bundle, \strpos( $bundle, '\\' ) + 1 );
            // Retrieve only the package directory
            $bundle = \strstr( $bundle, '\\', true );
            // Remove superfluous naming from the package directory
            $bundle = \trim( \str_replace( [ 'symfony', 'bundle' ], '', $bundle ), '-' );

            // Add the package directory as a bundle subdirectory
            $asset[ 'bundle' ] = $bundle;
        }

        $asset[ 'filename' ] = $source->basename;

        return new Path( normalizePath( $asset ) );
    }


    final public function __toString() : string {
        return $this->getHtml();
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
    public static function generateFilenameKey( string $path ) : string {
        $trimmed = \preg_replace( '/^(?:\w*:\/\/)*(.*?)(\?.*)?$/m', '$1', $path );
        return normalizeKey( $trimmed ?? $path );
    }
}