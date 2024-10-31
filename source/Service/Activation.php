<?php
namespace Setka\Editor\Service;

use Setka\Editor\Admin\Options;
use Setka\Editor\Admin\User\Capabilities;
use Setka\Editor\Plugin;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;
use Setka\Editor\Service\SetkaAccount\SignIn;
use Setka\Editor\Admin\Transients;

/**
 * Class Activation
 */
class Activation
{
    /**
     * @var SetkaEditorAccount
     */
    private $setkaEditorAccount;

    /**
     * @var SignIn
     */
    private $signIn;

    /**
     * Activation constructor.
     * @param SetkaEditorAccount $setkaEditorAccount
     * @param SignIn $signIn
     */
    public function __construct(SetkaEditorAccount $setkaEditorAccount, SignIn $signIn)
    {
        $this->setkaEditorAccount = $setkaEditorAccount;
        $this->signIn             = $signIn;
    }

    /**
     * Running on plugin activation.
     *
     * @since 0.0.1
     */
    public function run()
    {
        if ($this->isActivatedFirstTime()) {
            $this->setupCapabilities();

            $dbVersion = new Options\DBVersionOption();
            $dbVersion->updateValue(Plugin::DB_VERSION);
        }

        if (!$this->setkaEditorAccount->isLoggedIn()) {
            $this->signIn->signInAnonymous();
            $afterSignInTransient = new Transients\AfterSignInNoticeTransient();
            $afterSignInTransient->updateValue(true);
        }
    }

    /**
     * Setup required capabilities for User Roles. This settings can be changed on plugin
     * settings page or with Members plugin (https://wordpress.org/plugins/members/).
     *
     * @since 0.0.1
     */
    public function setupCapabilities()
    {
        $roles = get_editable_roles();

        if (!empty($roles)) {
            foreach ($roles as $roleKey => $roleValue) {
                if (in_array($roleKey, array('administrator', 'editor', 'author', 'contributor'), true)) {
                    $role = get_role($roleKey);
                    $role->add_cap(Capabilities\UseEditorCapability::NAME);
                }
            }
        }
    }

    /**
     * Checkout if plugin activated first time.
     *
     * @since 0.0.1
     *
     * @return bool true if activated first time, false if activated not first time (any plugin setting found in DB).
     */
    public function isActivatedFirstTime()
    {
        $roles = get_editable_roles();

        // Search capabilities
        if (!empty($roles)) {
            foreach ($roles as $roleKey => $roleValue) {
                if (isset($roleValue['capabilities'][Capabilities\UseEditorCapability::NAME])) {
                    return false;
                }
            }
        }

        unset($roles, $roleKey, $roleValue);

        // If any options (settings) find in DB then plugin activated previously
        if (Options\Common\Utilities::isOptionsExistsInDb()) {
            return false;
        }

        return true;
    }
}
