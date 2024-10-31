<?php
namespace Setka\Editor\Admin\Pages\SetkaEditor\Account;

use Setka\Editor\Admin\Pages\BaseEntity;

class SignIn extends BaseEntity
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }
}
