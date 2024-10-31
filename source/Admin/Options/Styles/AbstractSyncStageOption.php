<?php
namespace Setka\Editor\Admin\Options\Styles;

use Korobochkin\WPKit\Options\AbstractOption;
use Symfony\Component\Validator\Constraints;

abstract class AbstractSyncStageOption extends AbstractOption
{
    // 1 //
    const PREPARE_CONFIG = 'prepare_config';

    // 2 //
    const RESET_PREVIOUS_STATE = 'reset_previous_state';

    // 3 //
    const CREATE_ENTRIES = 'create_entries';

    // 4 //
    const REMOVE_OLD_ENTRIES = 'remove_old_entries';

    // 5 //
    const DOWNLOAD_FILES = 'download_files';

    // 6 //
    const OK = 'ok';

    public function __construct()
    {
        $this->setDefaultValue(self::PREPARE_CONFIG);
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
        return array(
            new Constraints\Type(array(
                'type' => 'string',
            )),
            new Constraints\Choice(array(
                'choices' => array(
                    self::PREPARE_CONFIG,
                    self::RESET_PREVIOUS_STATE,
                    self::CREATE_ENTRIES,
                    self::REMOVE_OLD_ENTRIES,
                    self::DOWNLOAD_FILES,
                    self::OK,
                ),
                'multiple' => false,
                'strict' => true,
            )),
        );
    }
}
