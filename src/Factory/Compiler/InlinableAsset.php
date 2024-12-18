<?php

declare(strict_types=1);

namespace Core\Assets\Factory\Compiler;

/**
 * @phpstan-require-extends AbstractAssetModel
 */
trait InlinableAsset
{
    protected ?bool $prefersInline = null;

    public function prefersInline( ?bool $set = true ) : self
    {
        $this->prefersInline = $set;
        return $this;
    }
}
