<?php

declare(strict_types=1);

namespace Core\Assets\Factory\Asset;

use Core\Assets\Factory\Compiler\{AbstractAssetModel, BundlableAssetInterface, InlinableAsset, BundlableAsset};
use Core\Assets\Factory\AssetHtml;
use Core\Assets\Interface\AssetHtmlInterface;
use Core\View\Html\Element;
use Northrook\{MinifierInterface, StylesheetMinifier};

final class StyleAsset extends AbstractAssetModel implements BundlableAssetInterface
{
    use BundlableAsset, InlinableAsset;

    public function render( ?array $attributes = null ) : AssetHtmlInterface
    {
        $compiledCSS = ( new StylesheetMinifier(
            $this->getSources(),
        ) )->minify();

        // $this->prefersInline = true;
        $attributes['asset-name'] = $this->getName();
        $attributes['asset-id']   = $this->assetID();

        $this->publicPath->save( $compiledCSS );

        if ( $this->prefersInline ) {
            $html = (string) new Element(
                tag        : 'style',
                attributes : $attributes,
                content    : $compiledCSS,
            );
        }
        else {
            $attributes['rel']  = 'stylesheet';
            $attributes['href'] = $this->publicUrl.$this->version();

            $html = (string) new Element( 'link', $attributes );
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
        return $this->compiler ??= $compiler ?? new StylesheetMinifier();
    }
}
