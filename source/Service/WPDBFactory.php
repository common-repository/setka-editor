<?php
namespace Setka\Editor\Service;

class WPDBFactory
{
    /**
     * @return \wpdb
     */
    public static function create()
    {
        global $wpdb;
        return $wpdb;
    }
}
