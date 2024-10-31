<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI\Prototypes;

interface ActionInterface extends \Setka\Editor\Admin\Service\APIs\ActionInterface
{
    /**
     * @return bool True if request needs AuthCredits.
     */
    public function isAuthenticationRequired(): bool;

    public function setAuthenticationRequired(bool $authenticationRequired): void;
}
