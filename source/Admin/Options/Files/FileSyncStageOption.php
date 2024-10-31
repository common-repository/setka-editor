<?php
namespace Setka\Editor\Admin\Options\Files;

use Korobochkin\WPKit\Options\AbstractOption;
use Setka\Editor\Admin\Options\Styles\AbstractSyncStageOption;
use Setka\Editor\Plugin;
use Symfony\Component\Validator\Constraints;

/**
 * Indicates a current state of files sync.
 *
 * There are multiple states (order matters):
 *
 * 1. download_files_list. Download list of files from API and save it.
 * 2. create_entries. Create entries in DB for each item in the list (see 1.).
 * 3. download_files. Download all files.
 * 4. generate_editor_config. Generate Editor config.
 * 5. switch_to_local_usage. Switch to local usage.
 *
 * If you switch to 1. we also need to disable local usage.
 */
class FileSyncStageOption extends AbstractSyncStageOption
{
    const DOWNLOAD_FILES_LIST = 'download_files_list';

    const CLEANUP = 'cleanup';

    const CREATE_ENTRIES = 'create_entries';

    const DOWNLOAD_FILES = 'download_files';

    const GENERATE_EDITOR_CONFIG = 'generate_editor_config';

    const OK = 'ok';

    /**
     * @var array
     */
    protected $stagesList = array(
        self::DOWNLOAD_FILES_LIST,
        self::CLEANUP,
        self::CREATE_ENTRIES,
        self::DOWNLOAD_FILES,
        self::GENERATE_EDITOR_CONFIG,
        self::OK,
    );

    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_file_sync_stage')
            ->setDefaultValue($this->stagesList[0]);
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
        return array(
            new Constraints\NotNull(),
            new Constraints\Type(array(
                'type' => 'string',
            )),
            new Constraints\Choice(array(
                'choices' => $this->stagesList,
                'multiple' => false,
                'strict' => true,
            )),
        );
    }
}
