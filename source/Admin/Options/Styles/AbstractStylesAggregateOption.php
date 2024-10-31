<?php
namespace Setka\Editor\Admin\Options\Styles;

use Korobochkin\WPKit\Options\Special\AbstractAggregateOption;
use Symfony\Component\Validator\Constraints;

abstract class AbstractStylesAggregateOption extends AbstractAggregateOption implements ConfigOptionInterface
{
    const COMMON = 'common';

    const COMMON_CRITICAL = 'common_critical';

    const COMMON_DEFERRED = 'common_deferred';

    const THEMES = 'themes';

    const THEMES_CRITICAL = 'themes_critical';

    const THEMES_DEFERRED = 'themes_deferred';

    const LAYOUTS = 'layouts';

    const FILE_ID = 'id';

    const FILE_WP_ID = 'wp_id';

    const FILE_URL = 'url';

    const FILE_TYPE = 'filetype';

    const FILE_FONTS = 'fonts';

    const FILE_WP_PATH = 'wp_path';

    /**
     * @param $factoryForFileFields
     *
     * @return array
     */
    protected function buildConstraintForSection($factoryForFileFields)
    {
        return array(
            new Constraints\NotBlank(),
            new Constraints\All(array(
                'constraints' => array(
                    new Constraints\NotBlank(),
                    new Constraints\Collection(array(
                        'fields' => call_user_func(array($this, $factoryForFileFields)),
                        'allowExtraFields' => true,
                    )),
                ),
            )),
        );
    }

    /**
     * @return array
     */
    protected function buildFieldsForFile()
    {
        return array(
            'id' => array(
                new Constraints\NotBlank(),
            ),
            'url' => array(
                new Constraints\NotBlank(),
                new Constraints\Url(),
            ),
            'filetype' => array(
                new Constraints\NotBlank(),
                new Constraints\Choice(array(
                    'choices' => array('css'),
                    'strict' => true,
                )),
            ),
        );
    }

    /**
     * @return array
     */
    protected function buildFieldsForThemeFile()
    {
        $fields = $this->buildFieldsForFile();

        $fields['fonts'] = new Constraints\Optional(
            new Constraints\All(array(
                'constraints' => array(
                    new Constraints\NotBlank(),
                    new Constraints\Url(),
                ),
            ))
        );

        return $fields;
    }
}
