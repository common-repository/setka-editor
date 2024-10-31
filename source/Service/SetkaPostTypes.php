<?php
namespace Setka\Editor\Service;

use Setka\Editor\Admin\Options\Styles\AbstractStylesAggregateOption;

class SetkaPostTypes
{
    const GROUP_FILES = 'files';

    const GROUP_AMP = 'amp';

    const GROUP_STANDALONE = 'standalone';

    const TYPE_CONFIG = 'config';

    const FILE_POST_NAME = 'setka_editor_file';

    /**
     * Posts with CSS styles which uses for all Setka Editor AMP posts pages.
     */
    const AMP_COMMON = 'setka_editor_001';

    /**
     * Posts with CSS styles which holds styles for each Setka Editor theme.
     */
    const AMP_THEME = 'setka_editor_050';

    /**
     * Posts with CSS styles which holds styles for each Setka Editor layout.
     */
    const AMP_LAYOUT = 'setka_editor_100';

    /**
     * Posts with AMP config (array with links and theme and layout ids).
     */
    const AMP_CONFIG = 'setka_editor_150';

    const STANDALONE_COMMON = 'setka_editor_201';

    const STANDALONE_COMMON_CRITICAL = 'setka_editor_202';

    const STANDALONE_COMMON_DEFERRED = 'setka_editor_203';

    const STANDALONE_THEME = 'setka_editor_250';

    const STANDALONE_THEME_CRITICAL = 'setka_editor_251';

    const STANDALONE_THEME_DEFERRED = 'setka_editor_252';

    const STANDALONE_LAYOUT = 'setka_editor_300';

    const STANDALONE_CONFIG = 'setka_editor_350';

    /**
     * @var array
     */
    private static $all = array(
        self::GROUP_AMP => array(
            self::TYPE_CONFIG => self::AMP_CONFIG,
            AbstractStylesAggregateOption::COMMON => self::AMP_COMMON,
            AbstractStylesAggregateOption::THEMES => self::AMP_THEME,
            AbstractStylesAggregateOption::LAYOUTS => self::AMP_LAYOUT,
        ),
        self::GROUP_STANDALONE => array(
            self::TYPE_CONFIG => self::STANDALONE_CONFIG,
            AbstractStylesAggregateOption::COMMON => self::STANDALONE_COMMON,
            AbstractStylesAggregateOption::COMMON_CRITICAL => self::STANDALONE_COMMON_CRITICAL,
            AbstractStylesAggregateOption::COMMON_DEFERRED => self::STANDALONE_COMMON_DEFERRED,
            AbstractStylesAggregateOption::THEMES => self::STANDALONE_THEME,
            AbstractStylesAggregateOption::THEMES_CRITICAL => self::STANDALONE_THEME_CRITICAL,
            AbstractStylesAggregateOption::THEMES_DEFERRED => self::STANDALONE_THEME_DEFERRED,
            AbstractStylesAggregateOption::LAYOUTS => self::STANDALONE_LAYOUT,
        ),
    );

    /**
     * @var array
     */
    private static $map = array(
        self::GROUP_FILES => array(
            'file' => self::FILE_POST_NAME,
        ),
        self::GROUP_AMP => array(
            AbstractStylesAggregateOption::COMMON => self::AMP_COMMON,
            AbstractStylesAggregateOption::THEMES => self::AMP_THEME,
            AbstractStylesAggregateOption::LAYOUTS => self::AMP_LAYOUT,
        ),
        self::GROUP_STANDALONE => array(
            AbstractStylesAggregateOption::COMMON => self::STANDALONE_COMMON,
            AbstractStylesAggregateOption::COMMON_CRITICAL => self::STANDALONE_COMMON_CRITICAL,
            AbstractStylesAggregateOption::COMMON_DEFERRED => self::STANDALONE_COMMON_DEFERRED,
            AbstractStylesAggregateOption::THEMES => self::STANDALONE_THEME,
            AbstractStylesAggregateOption::THEMES_CRITICAL => self::STANDALONE_THEME_CRITICAL,
            AbstractStylesAggregateOption::THEMES_DEFERRED => self::STANDALONE_THEME_DEFERRED,
            AbstractStylesAggregateOption::LAYOUTS => self::STANDALONE_LAYOUT,
        ),
    );

    /**
     * Returns post type name in WordPress DB.
     *
     * @param string $group AMP or standalone
     * @param string $section section name (common, theme, layout).
     *
     * @throws \LogicException If passed type not supported.
     *
     * @return string Post type name.
     */
    public static function getPostType($group, $section)
    {
        if (isset(self::$map[$group][$section])) {
            return self::$map[$group][$section];
        } else {
            throw new \LogicException('Not supported group or section.');
        }
    }

    /**
     * @param string $group
     *
     * @return array
     * @throws \LogicException
     */
    public static function getAllPostTypes($group)
    {
        if (isset(self::$all[$group])) {
            return self::$all[$group];
        } else {
            throw new \LogicException('Not supported group');
        }
    }

    /**
     * @param string $group
     *
     * @return array
     * @throws \LogicException
     */
    public static function getPostTypes($group)
    {
        if (isset(self::$map[$group])) {
            return self::$map[$group];
        } else {
            throw new \LogicException('Not supported group');
        }
    }
}
