<?php

namespace Northrook\Assets;

use Northrook\Logger\Log;
use Support\{ClassMethods, Str};
use Psr\Cache\InvalidArgumentException;
use Northrook\{Settings};
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Request;
use const Support\EMPTY_STRING;

/**
 * @author Martin Nielsen
 */
final class AssetManager
{
    use ClassMethods;

    /**
     * Will look for assets ascending.
     *
     * @var string[]
     */
    protected readonly array $assetDirectories;

    /**
     * A list of currently active assets, denoted by their `assetID`.
     *
     * - A request with the header `HX-Assets` contain a comma separated list of deployed assets.
     *
     * @var string[]
     */
    public readonly array $deployed;

    public readonly bool $enabled; // @phpstan-ignore-line

    public readonly bool $inline;

    public readonly bool $debug;

    public function __construct(
        private readonly Request          $request,
        private readonly AdapterInterface $cache,
    ) {
        if ( ! $this->shouldProcessRequest() ) {
            Log::notice(
                'The {class} is disabled, no {header} found.',
                [
                    'class'     => $this->classBasename(),
                    'header'    => 'HX-Assets',
                    'headerBag' => $this->request->headers->all(),
                ],
            );
        }

        $this->deployed = Str::explode( $this->request->headers->get( 'HX-Assets', EMPTY_STRING ) );
        $this->inline   = (bool) ( Settings::get( 'assets.inline' ) ?? true );
        $this->debug    = (bool) ( Settings::get( 'assets.debug' ) ?? true );
    }

    public function shouldProcessRequest() : bool
    {
        // If this is an ordinary request, enable
        if ( $this->request->headers->has( 'HX-Request' ) === false ) {
            return $this->enabled = true; // @phpstan-ignore-line
        }

        return $this->enabled = $this->request->headers->has( 'HX-Assets' ); // @phpstan-ignore-line
    }

    /**
     * @param string $assetId
     *
     * @return array<string, array<int,string>>
     */
    public function getAssets( string $assetId ) : ?array
    {

        if ( $this->isDeployed( $assetId ) ) {
            Log::notice( 'Asset {assetId} already deployed, {action}.', [
                'assetId' => $assetId,
                'action'  => 'skipped',
            ] );
            return [];
        }

        if ( ! $this->isRegistered( $assetId ) ) {

            Log::notice( 'Asset {assetId} already deployed, {action}.', [
                'assetId' => $assetId,
                'action'  => 'skipped',
            ] );
            return [];
        }

        return $this->getRegisteredAssets( $assetId );
    }

    private function isDeployed( string $assetId ) : bool
    {
        return \in_array( $assetId, $this->deployed, true );
    }

    private function isRegistered( string $assetId ) : bool
    {
        try {
            return $this->cache->hasItem( $assetId );
        }
        catch ( InvalidArgumentException $e ) {
            return false;
        }
    }

    /**
     * @param string $assetId
     *
     * @return array
     */
    private function getRegisteredAssets( string $assetId ) : array
    {
        try {
            $assets = $this->cache->getItem( $assetId );
        }
        catch ( InvalidArgumentException ) {
            return [];
        }

        return (array) $assets->get();
    }
}
