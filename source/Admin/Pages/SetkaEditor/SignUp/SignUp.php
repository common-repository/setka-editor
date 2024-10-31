<?php
namespace Setka\Editor\Admin\Pages\SetkaEditor\SignUp;

use Setka\Editor\Admin\Pages\BaseEntity;

class SignUp extends BaseEntity
{
    /**
     * @var ?string
     */
    private $token;

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param string|null $token
     */
    public function setToken(?string $token): void
    {
        $this->token = $token;
    }
}
