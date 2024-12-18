<?php

declare(strict_types=1);

namespace Core\Assets;

use Core\Assets\Exception\UndefinedAssetReferenceException;
use Core\Assets\Interface\{AssetManagerInterface, AssetManifestInterface};
use Core\Assets\Factory\AssetReference;
use Northrook\ArrayStore;
use Psr\Log\LoggerInterface;
use Support\{PhpStormMeta};
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * @noinspection PhpClassCanBeReadonlyInspection
 */
#[Autoconfigure(
    lazy     : true,   // lazy-load using ghost
    public   : false,  // private
    autowire : false,  // manual injection only
)]
class AssetManifest implements AssetManifestInterface
{
    /** @var ArrayStore<string, string> */
    private readonly ArrayStore $manifest;

    final public function __construct(
        string           $storagePath,
        ?LoggerInterface $logger = null,
        string           $name = 'AssetManifest',
        bool             $readonly = false,
        bool             $autosave = true,
    ) {
        $this->manifest = new ArrayStore( $storagePath, $name, $readonly, $autosave, $logger );

        if ( ! \file_exists( $storagePath ) ) {
            $this->manifest->save();
        }
    }

    final public function has( AssetReference|string $asset ) : bool
    {
        if ( $asset instanceof AssetReference ) {
            $asset = $asset->name;
        }
        return $this->manifest->has( $asset );
    }

    /**
     * @param string $asset
     * @param bool   $nullable [false] throw by default
     *
     * @return ($nullable is true ? null|AssetReference : AssetReference)
     */
    final public function get( string $asset, bool $nullable = false ) : ?AssetReference
    {
        $reference = $this->manifest->get( $asset );

        if ( ! $reference ) {
            if ( $nullable ) {
                return null;
            }
            throw new UndefinedAssetReferenceException( $asset, \array_keys( $this->manifest->flatten() ) );
        }

        return \unserialize( $reference );
    }

    /**
     * @param AssetReference $reference
     *
     * @return $this
     */
    final public function register( AssetReference $reference ) : self
    {
        $this->manifest->set( $reference->name, \serialize( $reference ) );
        return $this;
    }

    /**
     * @param string                                                            $projectDirectory
     * @param array{0: class-string, 1: string}|callable|callable-string|string ...$functionReference
     *
     * @return void
     */
    final public function updatePhpStormMeta(
        string                   $projectDirectory,
        array|string|callable ...$functionReference,
    ) : void {
        $meta = new PhpStormMeta( $projectDirectory );
        $meta->registerArgumentsSet(
            'asset_reference_keys',
            \array_keys( $this->manifest->flatten() ),
        );

        $generateReferences = \array_merge(
            [
                [AssetManifestInterface::class, 'has'],
                [AssetManifestInterface::class, 'get'],
                [AssetManagerInterface::class, 'get'],
                [AssetManagerInterface::class, 'getModel'],
                [AssetManagerInterface::class, 'getReference'],
            ],
            $functionReference,
        );

        foreach ( $generateReferences as $generateReference ) {
            $meta->expectedArguments( $generateReference, [0 => 'asset_reference_keys'] );
        }

        $meta->save( 'asset_manifest' );
    }
}
