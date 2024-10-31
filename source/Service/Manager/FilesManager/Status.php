<?php
namespace Setka\Editor\Service\Manager\FilesManager;

use Korobochkin\WPKit\Options\OptionInterface;
use Setka\Editor\Admin\Service\WPQueryFactory;
use Setka\Editor\Service\PostStatuses;

class Status implements StatusInterface
{
    const KEY_STATUS = 'status';

    const KEY_COUNTER = 'counter';

    /**
     * @var array
     */
    private static $countersStatusTemplate = array(
        PostStatuses::ANY     => 0,
        PostStatuses::ARCHIVE => 0,
        PostStatuses::DRAFT   => 0,
        PostStatuses::PUBLISH => 0,
        PostStatuses::TRASH   => 0,
        PostStatuses::FUTURE  => 0,
        PostStatuses::PENDING => 0,
    );

    /**
     * @var array
     */
    private $types;

    /**
     * @var OptionInterface[]
     */
    private $options = array();

    /**
     * @var \wpdb
     */
    private $wpdb;

    /**
     * @param array $types
     * @param OptionInterface[] $options
     * @param \wpdb $wpdb
     */
    public function __construct(array $types, array $options, \wpdb $wpdb)
    {
        $this->types   = $types;
        $this->options = $options;
        $this->wpdb    = $wpdb;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentSettings()
    {
        $options = array();
        foreach ($this->options as $option) {
            $options[$option->getName()] = $option->get();
        }
        return $options;
    }

    /**
     * @inheritDoc
     */
    public function getPostList()
    {
        $query = WPQueryFactory::createWherePostTypes($this->types);
        $items = array();

        while ($query->have_posts()) {
            $post = $query->next_post();

            $items[] = array(
                'ID' => $post->ID,
                'post_name' => $post->post_name,
                'post_status' => $post->post_status,
                'post_type' => $post->post_type,
                'post_date_gmt' => $post->post_date_gmt,
                'setka_file_type' => $this->getCSSType($post),
            );
        }
        return $items;
    }

    /**
     * @param \WP_Post $post
     *
     * @return string
     */
    private function getCSSType(\WP_Post $post)
    {
        $result = array_search($post->post_type, $this->types, true);
        if (is_string($result)) {
            return $result;
        }
        return 'unknown';
    }

    /**
     * @inheritDoc
     */
    public function getCountersByType($human = true)
    {
        $counters = array();

        foreach ($this->types as $humanName => $dbName) {
            $counters[($human) ? $humanName : $dbName] = $this->getCounter($dbName);
        }

        return $counters;
    }

    /**
     * @param $type string
     *
     * @return \Exception|int
     */
    private function getCounter($type)
    {
        $result = $this->query(
            "SELECT
            COUNT(ID) AS %s
            FROM {$this->wpdb->posts}
            WHERE post_type = %s",
            array(
                self::KEY_COUNTER,
                $type
            )
        );

        if (isset($result[0]) && is_array($result[0])) {
            return $this->castQuerySingle($result[0]);
        }
        return $this->createUnknownException();
    }

    /**
     * @inheritDoc
     */
    public function getCountersByStatus()
    {
        $placeholders = array_fill(
            0,
            count($this->types),
            'post_type = %s'
        );

        $postType = implode(' OR ', $placeholders);

        $results = $this->castQueryResult($this->query(
            "SELECT
            post_status AS %s,
            COUNT(ID) AS %s
            FROM {$this->wpdb->posts}
            WHERE {$postType}
            GROUP BY post_status",
            array_merge(
                array(
                    self::KEY_STATUS,
                    self::KEY_COUNTER,
                ),
                $this->types
            )
        ));

        return array_merge(
            self::$countersStatusTemplate,
            $results,
            array(
                PostStatuses::ANY => $this->getTotalCounter(),
            )
        );
    }

    /**
     * @return int
     *
     * @throws \Exception|
     */
    private function getTotalCounter()
    {
        $placeholders = array_fill(
            0,
            count($this->types),
            'post_type = %s'
        );

        $postType = implode(' OR ', $placeholders);

        $results = $this->query(
            "SELECT COUNT(*) as %s
            FROM {$this->wpdb->posts}
            WHERE {$postType}",
            array_merge(
                array(
                    self::KEY_COUNTER,
                ),
                $this->types
            )
        );

        if (isset($results[0])) {
            return $this->castQuerySingle($results[0]);
        }
        throw $this->createUnknownException();
    }

    /**
     * @param string $query
     * @param array $replace
     *
     * @return array
     */
    private function query($query, array $replace)
    {
        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->get_results($this->wpdb->prepare($query, $replace), ARRAY_A);
        // phpcs:enable
    }

    /**
     * @param mixed $results
     *
     * @return array
     *
     * @throws \Exception
     */
    private function castQueryResult($results)
    {
        if (!is_array($results) || empty($results)) {
            throw $this->createUnknownException();
        }

        $casted = array();

        foreach ($results as &$result) {
            if (isset($result[self::KEY_STATUS])) {
                $casted[$result[self::KEY_STATUS]] = $this->castQuerySingle($result);
            } else {
                throw $this->createUnknownException();
            }
        }

        return $casted;
    }

    /**
     * @param array $result
     *
     * @return \Exception|int
     */
    private function castQuerySingle(array $result)
    {
        if (isset($result[self::KEY_COUNTER]) && is_numeric($result[self::KEY_COUNTER])) {
            return (int) $result[self::KEY_COUNTER];
        }
        return $this->createUnknownException();
    }

    /**
     * @return \Exception
     */
    private function createUnknownException()
    {
        return new \Exception('Unknown result from $wpdb->query().');
    }
}
