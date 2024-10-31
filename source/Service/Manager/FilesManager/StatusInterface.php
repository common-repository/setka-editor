<?php
namespace Setka\Editor\Service\Manager\FilesManager;

interface StatusInterface
{
    /**
     * @return array List of option names and its values.
     */
    public function getCurrentSettings();

    /**
     * @return array
     */
    public function getPostList();

    /**
     * @param $human bool
     * @return array
     */
    public function getCountersByType($human = true);

    /**
     * @return array
     * @throws \Exception
     */
    public function getCountersByStatus();
}
