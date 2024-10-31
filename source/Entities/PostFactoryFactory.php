<?php
namespace Setka\Editor\Entities;

use Setka\Editor\PostMetas\PostLayoutPostMeta;
use Setka\Editor\PostMetas\PostThemePostMeta;
use Setka\Editor\PostMetas\UseEditorPostMeta;
use Setka\Editor\Service\DataFactory;

class PostFactoryFactory
{
    /**
     * @var array
     */
    protected static $metaList = array(
        PostLayoutPostMeta::class,
        PostThemePostMeta::class,
        UseEditorPostMeta::class,
    );

    /**
     * @param DataFactory $dataFactory
     *
     * @return PostFactory
     */
    public static function create(DataFactory $dataFactory)
    {
        return new PostFactory(self::createMetaList($dataFactory));
    }

    /**
     * @param DataFactory $dataFactory
     *
     * @return array
     */
    public static function createMetaList(DataFactory $dataFactory)
    {
        $metas = array();
        foreach (self::$metaList as $meta) {
            $metas[$meta] = $dataFactory->create($meta);
        }
        return $metas;
    }
}
