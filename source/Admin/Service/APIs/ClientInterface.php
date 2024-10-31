<?php
declare(strict_types=1);

namespace Setka\Editor\Admin\Service\APIs;

interface ClientInterface
{
    /**
     * @param string $url
     * @param array $details
     * @return array|\WP_Error
     */
    public function request(string $url, array $details);
}
