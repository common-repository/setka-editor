<?php
namespace Setka\Editor\Service\Manager;

use Setka\Editor\Service\Manager\Exceptions\JsonEncodeException;
use Setka\Editor\Service\Manager\Exceptions\PostException;

interface ConfigSupportManagerInterface extends ManagerInterface
{
    /**
     * Add new config.
     *
     * @param $config array New config which will be added into DB.
     *
     * @throws JsonEncodeException If wp_json_encode return bad result.
     * @throws PostException If post was not created.
     *
     * @return $this For chain calls.
     */
    public function addNewConfig(array $config);
}
