<?php

namespace Northrook\AssetGenerator;

use Northrook\Core\Trait\SingletonClass;
use function Northrook\normalizePath;

final class AssetContext
{
    use SingletonClass;

    public readonly string $projectDirectory;
    public readonly string $storageDirectory;
    public readonly string $publicDirectory;
    public readonly string $publicAssetsDirectory;

    /**
     * @param string  $projectDirectory       /
     * @param string  $storageDirectory       /var/assets
     * @param string  $publicDirectory        /public
     * @param string  $publicAssetsDirectory  /public/assets
     */
    public function __construct(
        string $projectDirectory,
        string $storageDirectory,
        string $publicDirectory,
        string $publicAssetsDirectory,
    ) {
        $this->instantiationCheck();

        $this->projectDirectory      = normalizePath( $projectDirectory );
        $this->storageDirectory      = normalizePath( $storageDirectory );
        $this->publicDirectory       = normalizePath( $publicDirectory );
        $this->publicAssetsDirectory = normalizePath( $publicAssetsDirectory );

        $this::$instance = $this;
    }

    public static function get() : AssetContext {
        return AssetContext::getInstance();
    }

}