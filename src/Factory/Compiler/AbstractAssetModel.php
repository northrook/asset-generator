<?php

declare(strict_types=1);

namespace Core\Assets\Factory\Compiler;

use Core\Assets\Factory\Asset\Type;
use Core\Assets\Interface\AssetModelInterface;
use Core\PathfinderInterface;
use Support\{FileInfo, Normalize};
use function String\hashKey;
use const Support\AUTO;

abstract class AbstractAssetModel implements AssetModelInterface
{
    /** @var string `16` character alphanumeric */
    private readonly string $assetID;

    protected readonly FileInfo $publicPath;

    protected readonly string $publicUrl;

    final private function __construct(
        private readonly AssetReference     $reference,
        public readonly PathfinderInterface $pathfinder,
    ) {
        $this->publicPath = $pathfinder->getFileInfo( "dir.assets.public/{$reference->publicUrl}" );
        \assert( $this->publicPath instanceof FileInfo );
        $this->publicUrl = Normalize::url( $this->pathfinder->get( $this->publicPath, 'dir.public' ) );
    }

    public function build( ?string $assetID = null ) : AssetModelInterface
    {
        $this->setAssetID( $assetID );
        return $this;
    }

    final public static function fromReference(
        AssetReference      $reference,
        PathfinderInterface $pathfinder,
    ) : self {
        return new static( $reference, $pathfinder );
    }

    public function version() : string
    {
        $modified = $this->publicPath->getMTime() ?: $this->assetID();
        return "?v={$modified}";
    }

    /**
     * @return string `lower-case.dot.notated`
     */
    final public function getName() : string
    {
        return $this->reference->name;
    }

    final public function getType() : Type
    {
        return $this->reference->type;
    }

    final public function getPublicUrl() : string
    {
        return $this->publicUrl;
    }

    final public function getPublicPath( bool $relative = false ) : string
    {
        return $relative
                ? $this->pathfinder->get(
                    (string) $this->publicPath,
                    'dir.public',
                    true,
                )
                : (string) $this->publicPath;
    }

    final public function getReference() : AssetReference
    {
        return $this->reference;
    }

    public function getSources() : array
    {
        return $this->reference->getSources();
    }

    final protected function assetID() : string
    {
        return $this->setAssetID( AUTO );
    }

    /**
     * @param null|string $assetID
     *
     * @return string `16` character alphanumeric
     */
    final protected function setAssetID( ?string $assetID ) : string
    {
        $this->assetID ??= $assetID ?? hashKey(
            [
                $this::class,
                $this->reference->name,
                $this->reference->type->name,
                ...$this->reference->getSources(),
            ],
            'implode',
        );

        \assert(
            \strlen( $this->assetID ) === 16 && \ctype_alnum( $this->assetID ),
            'Asset ID must be 16 alphanumeric characters; ['.\strlen(
                $this->assetID,
            )."] `{$this->assetID}` given",
        );

        return $this->assetID;
    }
}
