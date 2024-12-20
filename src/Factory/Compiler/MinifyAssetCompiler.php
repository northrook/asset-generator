<?php

declare(strict_types=1);

namespace Core\Assets\Factory\Compiler;

use Northrook\MinifierInterface;
use Support\FileInfo;

/**
 * @property-read \Core\PathfinderInterface $pathfinder
 * @property-read FileInfo                  $publicPath
 *
 * @phpstan-require-extends AbstractAssetModel
 */
trait MinifyAssetCompiler
{
    protected bool $alwaysCompile = true;

    protected readonly MinifierInterface $compiler;

    final protected function compile( array $addSources ) : string
    {
        $lastModified = 0;

        foreach ( $addSources as $sourcePath ) {
            $source = $this->pathfinder->getFileInfo(
                path      : "dir.assets/{$sourcePath}",
                assertive : true,
            );

            if ( $source->getMTime() > $lastModified ) {
                $lastModified = $source->getMTime();
            }

            $this->addSource( $source );
        }

        $localBuildFile = $this->pathfinder->getFileInfo(
            "dir.assets.build/{$this->publicPath->getBasename()}",
        );

        if ( $this->alwaysCompile ) {
            $compiledString = $this->compiler()->minify();
            $localBuildFile->save( $compiledString );
        }
        else {
            if (
                $localBuildFile->exists()
                && ( $localBuildFile->getMTime() > $lastModified )
            ) {
                $compiledString = $localBuildFile->getContents();
            }
            else {
                $compiledString = $this->compiler()->minify();
                $localBuildFile->save( $compiledString );
            }
        }

        return $compiledString;
    }

    public function alwaysCompileAsset( bool $set = true ) : void
    {
        $this->alwaysCompile = $set;
    }

    public function addSource( string|FileInfo $source ) : self
    {
        // TODO : MinifierInterface needs to globally accept FileInfo|SplFileInfo|Stringable
        $this->compiler()->addSource( (string) $source );
        return $this;
    }

    /**
     * Compile this {@see AssetModel} using a {@see MinifierInterface}.
     *
     * @param ?MinifierInterface $compiler
     *
     * @return MinifierInterface
     */
    abstract protected function compiler( ?MinifierInterface $compiler = null ) : MinifierInterface;
}
