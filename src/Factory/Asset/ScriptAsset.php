<?php

declare(strict_types=1);

namespace Core\Assets\Factory\Asset;

use Core\Assets\Factory\Compiler\{AbstractAssetModel, InlinableAsset, MinifyAssetCompiler};
use Core\Assets\Factory\AssetHtml;
use Core\Assets\Interface\AssetHtmlInterface;
use Northrook\HTML\Element;
use Northrook\{JavaScriptMinifier, MinifierInterface};
use ValueError;

final class ScriptAsset extends AbstractAssetModel
{
    use MinifyAssetCompiler, InlinableAsset;

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

    /**
     * @param null|MinifierInterface $compiler
     *
     * @return MinifierInterface
     */
    protected function compiler( ?MinifierInterface $compiler = null ) : MinifierInterface
    {
        return $this->compiler ??= $compiler ?? new JavaScriptMinifier( [] );
    }
}
