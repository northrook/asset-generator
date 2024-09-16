<?php

namespace Northrook\Assets;

use Northrook\Settings;
use Northrook\Trait\SingletonClass;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\CacheInterface;


final class AssetManager
{

    use SingletonClass;


    protected readonly string $projectStorage;
    private array             $enqueued = [];

    public function __construct(
        private readonly ?CacheInterface $cache = null,
        ?string                          $projectStorage = null,
    )
    {
        $this->instantiationCheck();

        $this->projectStorage = normalizePath( $projectStorage ?? Settings::get( 'dir.storage' ) );

        $this::$instance = $this;
    }

    // public static function mergeScripts( string ... $path) : string {
    //     $scripts = [];
    //
    //     foreach ( $path as $file ) {
    //
    //     }
    // }
}