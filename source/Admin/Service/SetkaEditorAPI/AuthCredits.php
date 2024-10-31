<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI;

class AuthCredits
{
    /**
     * @var string Token for Setka API server.
     */
    private $token;

    public function __construct(string $token)
    {
        $this->setToken($token);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getCreditsAsArray(): array
    {
        return array(
            'token' => $this->getToken(),
        );
    }
}
