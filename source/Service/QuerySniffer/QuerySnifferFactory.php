<?php
namespace Setka\Editor\Service\QuerySniffer;

use Setka\Editor\Admin\Service\WPQueryFactory;
use Setka\Editor\PostMetas\UseEditorPostMeta;
use Setka\Editor\Service\AMP\AMP;
use Setka\Editor\Service\DataFactory;
use Setka\Editor\Service\ScriptStyles;
use Setka\Editor\Service\Standalone\StandaloneStyles;

class QuerySnifferFactory
{
    /**
     * @param $gutenbergSupport bool
     * @param AMP $amp
     * @param StandaloneStyles $standaloneStyles
     * @param DataFactory $dataFactory
     * @param ScriptStyles $scriptStyles
     *
     * @return QuerySniffer
     */
    public static function create(
        bool $gutenbergSupport,
        AMP $amp,
        StandaloneStyles $standaloneStyles,
        DataFactory $dataFactory,
        ScriptStyles $scriptStyles
    ): QuerySniffer {
        return new QuerySniffer(
            $gutenbergSupport,
            WPQueryFactory::getGlobalOrEmpty(),
            $amp,
            $standaloneStyles,
            $dataFactory->create(UseEditorPostMeta::class),
            $scriptStyles
        );
    }
}
