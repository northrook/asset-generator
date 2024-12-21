<?php

declare(strict_types=1);

namespace Core\Assets\Factory\Compiler;

use Support\FileInfo;

trait BundlableAsset
{
    /** @var array{before: FileInfo[]|string[], source: FileInfo[]|string[], after: FileInfo[]|string[]} */
    protected array $sources = [
        'before' => [],
        'source' => [],
        'after'  => [],
    ];


    final public function getSources() : array
    {
        $this->sources['source'] = $this->getReference()->getSources( true );

        return [
            ...$this->sources['before'],
            ...$this->sources['source'],
            ...$this->sources['after'],
        ];
    }

    final public function addSource( string|FileInfo $source, bool $before = false ) : self
    {
        if ( $before ) {
            $this->sources['before'][] = $source;
        }
        else {
            $this->sources['after'][] = $source;
        }
        return $this;
    }
}
