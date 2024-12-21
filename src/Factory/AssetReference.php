<?php

declare(strict_types=1);

namespace Core\Assets\Factory;

use Core\Assets\Factory\Asset\Type;
use Support\{FileInfo, Normalize};
use Stringable;

/**
 * Created by the {@see \Core\Assets\AssetFactory}, stored in the {@see AssetManifestInterface}.
 *
 * Can be used retrieve detailed information about the asset, or recreate it from source.
 *
 * @internal
 *
 * @author Martin Nielsen
 */
final readonly class AssetReference
{
    /** @var string `lower-case.dot.notated` */
    public string $name;

    /** @var string `relative` */
    public string $publicUrl;

    /** @var string `file` or `directory` */
    public string $source;

    /**
     * @param Type              $type
     * @param string            $name
     * @param string|Stringable $publicUrl Must be relative
     * @param string|Stringable $source
     */
    public function __construct(
        public Type       $type,
        string            $name,
        string|Stringable $publicUrl,
        string|Stringable $source,
    ) {
        \assert(
            \ctype_alpha( \str_replace( ['.', '-'], '', $name ) ),
            "Asset names must only contain ASCII characters, underscores and dashes. {$name} provided.",
        );

        $type = \strtolower( $this->type->name );
        $name = \strtolower( \trim( $name, '.' ) );

        $fragments = \explode( '.', $name );

        if ( ! ( $fragments[0] === $type || $fragments[0] === "{$type}s" ) ) {
            \array_unshift( $fragments, $type );
        }

        $this->name = \implode( '.', \array_filter( $fragments ) );

        $this->publicUrl = Normalize::url( (string) $publicUrl );

        \assert(
            '/' === $this->publicUrl[0],
            'The public url must be relative.',
        );

        $this->source = (string) $source;
    }

    /**
     * @return array{name: string, publicUrl: string, source: string|string[], type: string}
     */
    public function __serialize() : array
    {
        return [
            'type'      => $this->type->name,
            'name'      => $this->name,
            'publicUrl' => $this->publicUrl,
            'source'    => $this->source,
        ];
    }

    /**
     * @param array{type: string, name: string, publicUrl: string, source: string} $data
     */
    public function __unserialize( array $data ) : void
    {
        $this->type      = Type::from( $data['type'], true );
        $this->name      = $data['name'];
        $this->publicUrl = $data['publicUrl'];
        $this->source    = $data['source'];
    }

    public function getSource() : FileInfo
    {
        return new FileInfo( $this->source );
    }

    /**
     * @param bool $asFileInfo
     *
     * @return ($asFileInfo is true ? FileInfo[] : string[])
     */
    public function getSources( bool $asFileInfo = false ) : array
    {
        $sources    = [];
        $extensions = $this->type->extensions();
        $source     = $this->getSource();
        if ( $source->isDir() ) {
            foreach ( $source->glob( '/*.*', asFileInfo : true ) as $path ) {
                if ( \in_array( $path->getExtension(), $extensions ) ) {
                    $sources[] = $asFileInfo ? new FileInfo( $path ) : $path->getPathname();
                }
            }
        } else {
            $sources[] = $source;
        }
        return $sources;
    }
}
