<?php

declare(strict_types=1);

namespace Core\Assets;

use Core\Assets\Factory\Compiler\AssetReference;
use Core\Assets\Exception\{InvalidAssetTypeException, UndefinedAssetReferenceException};
use Core\Assets\Factory\Asset\{ImageAsset, ScriptAsset, StyleAsset, Type};
use Core\Assets\Factory\AssetLocator;
use Core\Assets\Interface\{AssetHtmlInterface, AssetManifestInterface, AssetModelInterface};
use Core\PathfinderInterface;
use Core\Symfony\DependencyInjection\Autodiscover;
use Psr\Log\LoggerInterface;
use RuntimeException;

#[Autodiscover(
    lazy   : true,   // lazy-load using ghost
    public : false,  // private
)]
class AssetFactory
{
    private readonly AssetLocator $locator;

    /** @var array<string, callable(AssetModelInterface):AssetModelInterface> */
    protected array $assetModelCallback = [];

    /** @var array<string, callable(AssetModelInterface):AssetModelInterface> */
    protected array $assetTypeCallback = [];

    final public function __construct(
        public readonly AssetManifestInterface $manifest,
        protected readonly PathfinderInterface $pathfinder,
        protected readonly ?LoggerInterface    $logger = null,
        protected bool                         $lock = false,
    ) {}

    final public function locator() : AssetLocator
    {
        return $this->locator ??= new AssetLocator(
            $this->manifest,
            $this->pathfinder,
            $this->logger,
        );
    }

    final public function addAssetModelCallback( string $asset, callable $callback ) : self
    {
        if ( $this->lock ) {
            $message = "Unable to add assetModelCallback to '{$asset}', the AssetManager is locked.";
            throw new RuntimeException( $message );
        }
        $this->assetModelCallback[$asset] = $callback;
        return $this;
    }

    final public function addAssetTypeCallback( Type $type, callable $callback ) : self
    {
        if ( $this->lock ) {
            $message = "Unable to add assetTypeCallback to '{$type->name}', the AssetManager is locked.";
            throw new RuntimeException( $message );
        }
        $this->assetTypeCallback[$type->name] = $callback;
        return $this;
    }

    /**
     * @param AssetReference|string                     $asset
     * @param ?string                                   $assetID
     * @param array<string, null|bool|float|int|string> $attributes
     *
     * @return AssetHtmlInterface
     * @throws InvalidAssetTypeException
     * @throws UndefinedAssetReferenceException
     */
    final public function getAssetHtml(
        AssetReference|string $asset,
        ?string               $assetID = null,
        array                 $attributes = [],
    ) : AssetHtmlInterface {
        $assetModel = $this->getAssetModel( $asset, $assetID );

        $this->handleAssetCallback( $assetModel );

        return $assetModel->render( $attributes );
    }

    /**
     * @param AssetReference|string $asset
     * @param ?string               $assetID
     *
     * @return AssetModelInterface
     *
     * @throws InvalidAssetTypeException|UndefinedAssetReferenceException on failure
     */
    final public function getAssetModel(
        string|AssetReference $asset,
        ?string               $assetID = null,
    ) : AssetModelInterface {
        $reference = $this->resolveAssetReference( $asset );

        $model = match ( $reference->type ) {
            Type::STYLE  => StyleAsset::class,
            Type::SCRIPT => ScriptAsset::class,
            Type::IMAGE  => ImageAsset::class,
            default      => null,
        };

        if ( ! $model ) {
            throw new InvalidAssetTypeException( $reference->type );
        }

        $asset = $model::fromReference(
            $reference,
            $this->pathfinder,
        );

        $asset->build( $assetID );

        return $asset;
    }

    /**
     * @param AssetReference|string $asset
     *
     * @return AssetReference
     *
     * @throws UndefinedAssetReferenceException on failure
     */
    final public function resolveAssetReference(
        string|AssetReference $asset,
    ) : AssetReference {
        if ( $asset instanceof AssetReference ) {
            $asset = $asset->name;
        }

        try {
            return $this->manifest->getReference( $asset );
        }
        catch ( UndefinedAssetReferenceException $exception ) {
            $validType = Type::from( \strstr( $asset, '.', true ) ?: $asset );

            if ( $validType ) {
                $this->logger?->warning(
                    'Unable to resolve asset model for {asset} with type {type}. Autodiscover triggered.',
                    ['asset' => $asset, 'type' => $validType->name],
                );
                $this->locator()->discover( $validType );
            }
            else {
                $this->logger?->emergency(
                    $exception->getMessage(),
                    [
                        'asset'     => $asset,
                        'exception' => $exception,
                    ],
                );
                throw $exception;
            }
            return $this->manifest->getReference( $asset );
        }
    }

    /**
     * Handle registered pre-render `callback` functions.
     *
     * @param AssetModelInterface $assetModel
     *
     * @return void
     */
    private function handleAssetCallback( AssetModelInterface &$assetModel ) : void
    {
        if ( \array_key_exists(
            $assetModel->getType()->name,
            $this->assetTypeCallback,
        ) ) {
            $assetModel = ( $this->assetTypeCallback[$assetModel->getType()->name] )( $assetModel );
        }
        if ( \array_key_exists(
            $assetModel->getName(),
            $this->assetModelCallback,
        ) ) {
            $assetModel = ( $this->assetModelCallback[$assetModel->getName()] )( $assetModel );
        }
    }
}
