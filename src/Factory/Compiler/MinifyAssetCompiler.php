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
    protected readonly MinifierInterface $compiler;

    /** @var array{before: FileInfo[]|string[], source: FileInfo[]|string[], after: FileInfo[]|string[]} */
    private array $sources = [
        'before' => [],
        'source' => [],
        'after'  => [],
    ];

    final protected function compile() : string
    {
        $this->sources['source'] = $this->getReference()->getSources( true );

        $this->compiler()->addSource(
            ...$this->sources['before'],
            ...$this->sources['source'],
            ...$this->sources['after'],
        );

        return $this->compiler()->minify();
    }

    public function addSource( string|FileInfo $source, bool $before = false ) : self
    {
        if ( $before ) {
            $this->sources['before'][] = $source;
        }
        else {
            $this->sources['after'][] = $source;
        }
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
