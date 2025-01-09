<?php

declare(strict_types=1);

namespace Core\Assets;

use Cache\LocalStorage;
use Core\Assets\Exception\UndefinedAssetReferenceException;
use Core\Assets\Factory\Compiler\AssetReference;
use Core\Assets\Interface\{AssetManagerInterface, AssetManifestInterface};
use Core\Symfony\DependencyInjection\Autodiscover;
use Psr\Log\LoggerInterface;
use Support\PhpStormMeta;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Autodiscover(
    lazy   : true,   // lazy-load using ghost
    public : false,  // private
)]
class AssetManifest implements AssetManifestInterface
{
    protected readonly LocalStorage $storage;

    public function __construct(
        #[Autowire( param : 'path.asset_manifest' )] //
        string                    $storagePath,
        protected LoggerInterface $logger,
    ) {
        $this->storage = new LocalStorage(
            $storagePath,
            autosave : false,
        );
    }

    public function hasReference( AssetReference|string $asset ) : bool
    {
        if ( $asset instanceof AssetReference ) {
            $asset = $asset->name;
        }

        return $this->storage->has( $asset );
    }

    public function getReference( string $asset, ?callable $register = null ) : AssetReference
    {
        $reference = $this->storage->get( $asset, $register );

        if ( ! $reference ) {
            throw new UndefinedAssetReferenceException( $asset, $this->storage->getKeys() );
        }

        return $reference;
    }

    public function registerReference( AssetReference $reference ) : AssetManifestInterface
    {
        $this->storage->set( $reference->name, $reference );
        return $this;
    }

    public function hasChanges() : bool
    {
        return $this->storage->hasChanges();
    }

    public function commit() : void
    {
        $status = $this->storage->save();

        $this->logger->info( '{method} {status}', ['method' => __METHOD__, 'status' => $status] );
    }

    /**
     * @param ?string                                                           $projectDirectory
     * @param array{0: class-string, 1: string}|callable|callable-string|string ...$functionReference
     *
     * @return void
     */
    final public function updatePhpStormMeta(
        ?string                  $projectDirectory,
        array|string|callable ...$functionReference,
    ) : void {
        $meta = new PhpStormMeta( $projectDirectory );

        $meta->registerArgumentsSet(
            'asset_reference_keys',
            ...\array_keys( $this->storage->getKeys() ),
        );

        $generateReferences = \array_merge(
            [
                [AssetManifestInterface::class, 'hasReference'],
                [AssetManifestInterface::class, 'getReference'],
                [AssetManagerInterface::class, 'getAssetHtml'],
                [AssetManagerInterface::class, 'getAssetModel'],
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
