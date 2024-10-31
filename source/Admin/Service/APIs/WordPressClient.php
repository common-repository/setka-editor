<?php
declare(strict_types=1);

namespace Setka\Editor\Admin\Service\APIs;

class WordPressClient implements ClientInterface
{
    public function request(string $url, array $details)
    {
        return wp_remote_request($url, $details);
    }
}
