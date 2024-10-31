<?php
namespace Setka\Editor\Service\Standalone;

use Setka\Editor\Admin\Options\ForceUseSetkaCDNOption;
use Setka\Editor\Admin\Options\Standalone\StylesOption;
use Setka\Editor\Admin\Options\Standalone\UseAssetsAndUseStylesOption;
use Setka\Editor\Admin\Options\Standalone\UseCriticalOption;
use Setka\Editor\Plugin;
use Setka\Editor\PostMetas\PostLayoutPostMeta;
use Setka\Editor\PostMetas\PostThemePostMeta;
use Setka\Editor\PostMetas\UseEditorPostMeta;
use Setka\Editor\Service\DataFactory;
use Setka\Editor\Service\SetkaPostTypes;

class StandaloneStylesFactory
{
    /**
     * @param StylesOption $stylesOption
     * @param UseAssetsAndUseStylesOption $useStylesOption
     * @param DataFactory $dataFactory
     * @param ForceUseSetkaCDNOption $forceUseSetkaCDNOption
     * @param UseCriticalOption $useCriticalOption
     * @param callable $storageUrl
     * @param string $storageBasename
     *
     * @return StandaloneStyles
     */
    public static function create(
        StylesOption $stylesOption,
        UseAssetsAndUseStylesOption $useStylesOption,
        DataFactory $dataFactory,
        ForceUseSetkaCDNOption $forceUseSetkaCDNOption,
        UseCriticalOption $useCriticalOption,
        callable $storageUrl,
        string $storageBasename
    ) {
        return new StandaloneStyles(
            $stylesOption,
            $useStylesOption,
            $dataFactory->create(UseEditorPostMeta::class),
            $dataFactory->create(PostThemePostMeta::class),
            $dataFactory->create(PostLayoutPostMeta::class),
            SetkaPostTypes::getPostTypes(SetkaPostTypes::GROUP_STANDALONE),
            Plugin::NAME . '-standalone',
            $forceUseSetkaCDNOption,
            $useCriticalOption,
            path_join(call_user_func($storageUrl), $storageBasename)
        );
    }
}
