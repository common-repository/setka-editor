<?php
namespace Setka\Editor\Service;

use Setka\Editor\Plugin;

/**
 * Class PostStatuses
 */
class PostStatuses
{
    const PUBLISH = 'publish';

    const DRAFT = 'draft';

    const PENDING = 'pending';

    const FUTURE = 'future';

    const ANY = 'any';

    const TRASH = 'trash';

    const ARCHIVE = 'archive';

    /**
     * @var array
     */
    private static $all = array(
        self::PUBLISH,
        self::DRAFT,
        self::PENDING,
        self::FUTURE,
        self::TRASH,
        self::ARCHIVE,
    );

    /**
     * @return array
     */
    public static function getAll()
    {
        return self::$all;
    }

    /**
     * Register additional post statuses.
     *
     * @return $this For chain calls.
     */
    public function register()
    {
        $this->registerArchive();
        return $this;
    }

    /**
     * Register Archive post status.
     *
     * @return $this For chain calls.
     */
    public function registerArchive()
    {
        register_post_status(
            self::ARCHIVE,
            array(
                'label' => __('Archive', Plugin::NAME),
                'internal' => true,
                'private' => true,
                'exclude_from_search' => true,
                'show_in_admin_all_list' => false,
                'show_in_admin_status_list' => false,
            )
        );
        return $this;
    }
}
