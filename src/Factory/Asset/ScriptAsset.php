<?php

declare(strict_types=1);

namespace Core\Assets\Factory\Asset;

use Core\Assets\Factory\Compiler\{AbstractAssetModel, BundlableAssetInterface, InlinableAsset, JavascriptAssetCompiler};
use Core\Assets\Factory\AssetHtml;
use Core\Assets\Interface\AssetHtmlInterface;
use Northrook\HTML\Element;
use Support\FileInfo;

final class ScriptAsset extends AbstractAssetModel implements BundlableAssetInterface
{
    use InlinableAsset;

    /** @var array{before: FileInfo[]|string[], import: FileInfo[], source: ?FileInfo, after: FileInfo[]|string[]} */
    protected array $sources = [
        'before' => [],
        'import' => [],
        'source' => null,
        'after'  => [],
    ];

    private array $data = [];

    protected function compile() : string
    {
        return ( new JavascriptAssetCompiler(
            $this->getReference()->getSource(),
        )
        )->compile( true );
    }

    public function render( ?array $attributes = null ) : AssetHtmlInterface
    {
        $compiledJS = $this->compile();

        $attributes['asset-name'] = $this->getName();
        $attributes['asset-id']   = $this->assetID();

        if ( $this->prefersInline ) {
            $html = (string) new Element(
                tag        : 'script',
                attributes : $attributes,
                content    : $compiledJS,
            );
        }
        else {
            $this->publicPath->save( $compiledJS );

            $attributes['src'] = $this->publicUrl.$this->version();

            $html = (string) new Element( 'script', $attributes );
        }

        return new AssetHtml(
            $this->getName(),
            $this->assetID(),
            $this->getType(),
            $html,
        );
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
