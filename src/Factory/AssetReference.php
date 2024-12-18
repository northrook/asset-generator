<?php

declare(strict_types=1);

namespace Core\Assets\Factory;

use Core\Assets\AssetManifest;
use Core\Assets\Factory\Asset\Type;
use Support\Interface\DataObject;
use Support\Normalize;
use Stringable;
use ValueError;

/**
 * Created by the {@see \Core\Assets\AssetFactory}, stored in the {@see AssetManifest}.
 *
 * Can be used retrieve detailed information about the asset, or recreate it from source.
 *
 * @internal
 *
 * @author Martin Nielsen
 */
final readonly class AssetReference extends DataObject
{
    public string $name;

    /** @var string `relative` */
    public string $publicUrl;

    /** @var string|string[] `relative` */
    public string|array $source;

    /**
     * @param Type              $type
     * @param string            $name
     * @param string|Stringable $publicUrl Must be relative
     * @param string|string[]   $source
     */
    public function __construct(
        public Type       $type,
        string            $name,
        string|Stringable $publicUrl,
        string|array      $source,
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

        foreach ( (array) $source as $assetPath ) {
            \assert(
                \is_string( $assetPath ),
                $this::class.'$source must be string|string[], '.\gettype( $assetPath ).' provided.',
            );

            if ( DIRECTORY_SEPARATOR !== $assetPath[0] ) {
                $message = $this::class.'$source must be a relative path.';
                throw new ValueError( $message );
            }
        }

        $this->source = $source;
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
     * @param array{type: string, name: string, publicUrl: string, source: string|string[]} $data
     */
    public function __unserialize( array $data ) : void
    {
        $this->type      = Type::from( $data['type'], true );
        $this->name      = $data['name'];
        $this->publicUrl = $data['publicUrl'];
        $this->source    = $data['source'];
    }
}
