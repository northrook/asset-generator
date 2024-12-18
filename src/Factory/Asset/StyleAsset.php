<?php

declare(strict_types=1);

namespace Core\Assets\Factory\Asset;

use Core\Assets\Factory\Compiler\{AbstractAssetModel, InlinableAsset, MinifyAssetCompiler};
use Core\Assets\Factory\AssetHtml;
use Core\Assets\Interface\AssetHtmlInterface;
use Northrook\HTML\Element;
use ValueError;
use Northrook\{MinifierInterface, StylesheetMinifier};

final class StyleAsset extends AbstractAssetModel
{
    use MinifyAssetCompiler, InlinableAsset;

    public function render( ?array $attributes = null ) : AssetHtmlInterface
    {
        $compiledCSS = $this->compile( $this->getSources() );

        if ( ! $compiledCSS ) {
            throw new ValueError();
        }

        $attributes['asset-name'] = $this->getName();
        $attributes['asset-id']   = $this->assetID();

        if ( $this->prefersInline ) {
            $html = (string) new Element(
                tag        : 'style',
                attributes : $attributes,
                content    : $compiledCSS,
            );
        }
        else {
            $this->publicPath->save( $compiledCSS );

            $attributes['rel'] = 'stylesheet';
            $attributes['src'] = $this->publicUrl.$this->version();

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
