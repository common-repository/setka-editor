<?php
namespace Setka\Editor\Admin\Service\EditorConfigGenerator;

use Setka\Editor\Admin\Options\ThemeResourceCSSLocalOption;
use Setka\Editor\Admin\Options\ThemeResourceJSLocalOption;
use Setka\Editor\Admin\Service\EditorConfigGenerator\Exceptions\ConfigFileEntryException;
use Setka\Editor\Admin\Service\Filesystem\FilesystemInterface;
use Setka\Editor\PostMetas\FileSubPathPostMeta;

class EditorConfigGeneratorFactory
{
    public static function create(
        FilesystemInterface $filesystem,
        string $rootPath,
        string $rootUrl,
        \WP_Query $queryJSON,
        \WP_Query $queryCSS,
        FileSubPathPostMeta $fileSubPathMeta,
        ThemeResourceJSLocalOption $jsLocalOption,
        ThemeResourceCSSLocalOption $cssLocalOption
    ): EditorConfigGenerator {
        if (!$queryJSON->have_posts() || !$queryCSS->have_posts()) {
            throw new ConfigFileEntryException();
        }

        $post = $queryJSON->next_post();

        $jsonFileInfo = new FileInfo(
            $rootPath,
            $rootUrl,
            $fileSubPathMeta->setPostId($post->ID)->get()
        );

        $post = $queryCSS->next_post();

        $cssFileInfo = new FileInfo(
            $rootPath,
            $rootUrl,
            $fileSubPathMeta->setPostId($post->ID)->get()
        );

        return new EditorConfigGenerator(
            $filesystem,
            $rootPath,
            $rootUrl,
            $jsonFileInfo,
            $cssFileInfo,
            $jsLocalOption,
            $cssLocalOption
        );
    }
}
