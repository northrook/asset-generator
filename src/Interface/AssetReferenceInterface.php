<?php

declare(strict_types=1);

namespace Core\Assets\Interface;

use Core\Assets\Factory\Asset\Type;
use Stringable;

/**
 * Created by the {@see \Core\Assets\AssetFactory}, stored in the {@see AssetManifestInterface}.
 *
 * Can be used retrieve detailed information about the asset, or recreate it from source.
 *
 * @internal
 *
 * @property-read Type            $type
 * @property-read string          $name      `lower-case.dot.notated`
 * @property-read string          $publicUrl `relative`
 * @property-read string|string[] $source    `relative`
 *
 * @author Martin Nielsen
 */
interface AssetReferenceInterface
{
    /**
     * @param Type              $type
     * @param string            $name
     * @param string|Stringable $publicUrl Must be relative
     * @param string|string[]   $source
     */
    public function __construct(
        Type              $type,
        string            $name,
        string|Stringable $publicUrl,
        string|array      $source,
    );

    /**
     * @return array{name: string, publicUrl: string, source: string|string[], type: string}
     */
    public function __serialize() : array;

    /**
     * @param array{type: string, name: string, publicUrl: string, source: string|string[]} $data
     */
    public function __unserialize( array $data ) : void;
}
