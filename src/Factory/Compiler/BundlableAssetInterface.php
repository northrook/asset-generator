<?php

declare(strict_types=1);

namespace Core\Assets\Factory\Compiler;

use Support\FileInfo;

interface BundlableAssetInterface
{
    public function addSource( string|FileInfo $source, bool $before = false ) : self;
}
