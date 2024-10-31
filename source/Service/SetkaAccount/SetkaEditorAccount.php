<?php
namespace Setka\Editor\Service\SetkaAccount;

use Korobochkin\WPKit\Options\OptionInterface;
use Setka\Editor\Admin\Options\EditorCSSOption;
use Setka\Editor\Admin\Options\EditorJSOption;
use Setka\Editor\Admin\Options\Files\UseLocalFilesOption;
use Setka\Editor\Admin\Options\ForceUseSetkaCDNOption;
use Setka\Editor\Admin\Options\PublicTokenOption;
use Setka\Editor\Admin\Options\SubscriptionActiveUntilOption;
use Setka\Editor\Admin\Options\SubscriptionPaymentStatusOption;
use Setka\Editor\Admin\Options\SubscriptionStatusOption;
use Setka\Editor\Admin\Options\ThemePluginsJSOption;
use Setka\Editor\Admin\Options\ThemeResourceCSSLocalOption;
use Setka\Editor\Admin\Options\ThemeResourceCSSOption;
use Setka\Editor\Admin\Options\ThemeResourceJSLocalOption;
use Setka\Editor\Admin\Options\ThemeResourceJSOption;
use Setka\Editor\Admin\Options\TokenOption;

class SetkaEditorAccount
{
    /**
     * @var TokenOption
     */
    protected $tokenOption;

    /**
     * @var SubscriptionStatusOption
     */
    protected $subscriptionStatusOption;

    /**
     * @var SubscriptionActiveUntilOption
     */
    protected $subscriptionActiveUntilOption;

    /**
     * @var SubscriptionPaymentStatusOption
     */
    protected $subscriptionPaymentStatusOption;

    /**
     * @var EditorJSOption
     */
    protected $editorJSOption;

    /**
     * @var EditorCSSOption
     */
    protected $editorCSSOption;

    /**
     * @var PublicTokenOption
     */
    protected $publicTokenOption;

    /**
     * @var ThemeResourceCSSOption
     */
    protected $themeResourceCSSOption;

    /**
     * @var ThemeResourceCSSLocalOption
     */
    protected $themeResourceCSSLocalOption;

    /**
     * @var ThemeResourceJSOption
     */
    protected $themeResourceJSOption;

    /**
     * @var ThemeResourceJSLocalOption
     */
    protected $themeResourceJSLocalOption;

    /**
     * @var ThemePluginsJSOption
     */
    protected $themePluginsJSOption;

    /**
     * @var UseLocalFilesOption
     */
    protected $useLocalFilesOption;

    /**
     * @var ForceUseSetkaCDNOption
     */
    private $forceUseSetkaCDNOption;

    /**
     * SetkaEditorAccount constructor.
     *
     * @param TokenOption $tokenOption
     * @param SubscriptionStatusOption $subscriptionStatusOption
     * @param SubscriptionActiveUntilOption $subscriptionActiveUntilOption
     * @param SubscriptionPaymentStatusOption $subscriptionPaymentStatusOption
     * @param EditorJSOption $editorJSOption
     * @param EditorCSSOption $editorCSSOption
     * @param PublicTokenOption $publicTokenOption
     * @param ThemeResourceCSSOption $themeResourceCSSOption
     * @param ThemeResourceCSSLocalOption $themeResourceCSSLocalOption
     * @param ThemeResourceJSOption $themeResourceJSOption
     * @param ThemeResourceJSLocalOption $themeResourceJSLocalOption
     * @param ThemePluginsJSOption $themePluginsJSOption
     * @param UseLocalFilesOption $useLocalFilesOption
     * @param ForceUseSetkaCDNOption $forceUseSetkaCDNOption
     */
    public function __construct(
        TokenOption $tokenOption,
        SubscriptionStatusOption $subscriptionStatusOption,
        SubscriptionActiveUntilOption $subscriptionActiveUntilOption,
        SubscriptionPaymentStatusOption $subscriptionPaymentStatusOption,
        EditorJSOption $editorJSOption,
        EditorCSSOption $editorCSSOption,
        PublicTokenOption $publicTokenOption,
        ThemeResourceCSSOption $themeResourceCSSOption,
        ThemeResourceCSSLocalOption $themeResourceCSSLocalOption,
        ThemeResourceJSOption $themeResourceJSOption,
        ThemeResourceJSLocalOption $themeResourceJSLocalOption,
        ThemePluginsJSOption $themePluginsJSOption,
        UseLocalFilesOption $useLocalFilesOption,
        ForceUseSetkaCDNOption $forceUseSetkaCDNOption
    ) {
        $this->tokenOption                     = $tokenOption;
        $this->subscriptionStatusOption        = $subscriptionStatusOption;
        $this->subscriptionActiveUntilOption   = $subscriptionActiveUntilOption;
        $this->subscriptionPaymentStatusOption = $subscriptionPaymentStatusOption;
        $this->editorJSOption                  = $editorJSOption;
        $this->editorCSSOption                 = $editorCSSOption;
        $this->publicTokenOption               = $publicTokenOption;
        $this->themeResourceCSSOption          = $themeResourceCSSOption;
        $this->themeResourceCSSLocalOption     = $themeResourceCSSLocalOption;
        $this->themeResourceJSOption           = $themeResourceJSOption;
        $this->themeResourceJSLocalOption      = $themeResourceJSLocalOption;
        $this->themePluginsJSOption            = $themePluginsJSOption;
        $this->useLocalFilesOption             = $useLocalFilesOption;
        $this->forceUseSetkaCDNOption          = $forceUseSetkaCDNOption;
    }

    /**
     * Some checks for Setka Account. User logged in if token provided
     * (other account stuff must also available in DB). This is not bullet proof
     * checks so manually editing any of plugin settings not recommended :)
     *
     * @return bool True if user logged in, false otherwise.
     */
    public function isLoggedIn()
    {
        if ($this->tokenOption->get()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool True if token valid.
     */
    public function isTokenValid()
    {
        return $this->tokenOption->isValid();
    }

    /**
     * Check if subscription running and not expired.
     *
     * @return bool true if subscription running, false otherwise.
     */
    public function isSubscriptionRunning()
    {
        if ($this->isSubscriptionStatusRunning()) {
            return $this->isSubscriptionNotExpire();
        }
        return false;
    }

    /**
     * If account currently active (user can create new posts with Editor).
     *
     * @return bool True if account status is "running", false otherwise.
     */
    public function isSubscriptionStatusRunning()
    {
        if ($this->subscriptionStatusOption->get() === 'running') {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isSubscriptionNotExpire()
    {
        $activeUntil = \DateTime::createFromFormat(
            \DateTime::ISO8601,
            $this->subscriptionActiveUntilOption->get()
        );

        if (!$activeUntil) {
            return false;
        }

        $now = new \DateTime('now', $activeUntil->getTimezone());

        if (!$now) {
            return false;
        }

        if ($activeUntil > $now) {
            return true;
        }

        return false;
    }

    /**
     * If account have trial period.
     *
     * @return bool True if account trialling, false otherwise.
     */
    public function isSubscriptionStatusTrialing()
    {
        if ($this->subscriptionPaymentStatusOption->get() === 'trialing') {
            return true;
        }
        return false;
    }

    /**
     * Editor resources is the Editor JS-CSS + Theme resources JS-CSS
     *
     * @return bool
     */
    public function isEditorResourcesAvailable()
    {
        if (!$this->isSubscriptionStatusRunning()) {
            return false;
        }

        if (!$this->isThemeResourcesAvailable()) {
            return false;
        }

        if (!$this->editorJSOption->get()) {
            return false;
        }

        if (!$this->editorCSSOption->get()) {
            return false;
        }

        return true;
    }

    /**
     * Resources is the Theme JS-CSS which always available if you're logged in (token provided).
     * Even if you canceled subscription your post shows perfectly.
     *
     * @return bool True if user logged in (resour
     */
    public function isThemeResourcesAvailable()
    {
        if (!$this->themeResourceCSSOption->get()) {
            return false;
        }

        if (!$this->themeResourceJSOption->get()) {
            return false;
        }

        if (!$this->themePluginsJSOption->get()) {
            return false;
        }

        return true;
    }

    /**
     * Check if trial ends in one day.
     *
     * @return bool True if trial ends in one day. False if not trial, or if more than one day of trial,
     * or if trial already expired.
     */
    public function isTrialEndsInOneDay()
    {
        if ($this->isSubscriptionStatusTrialing()) {
            $activeUntil = \DateTime::createFromFormat(
                \DateTime::ISO8601,
                $this->subscriptionActiveUntilOption->get()
            );

            if (!$activeUntil) {
                return false;
            }

            $now = new \DateTime('now', $activeUntil->getTimezone());

            if (!$now) {
                return false;
            }

            // Trial not over
            if ($activeUntil > $now) {
                $difference = $activeUntil->getTimestamp() - $now->getTimestamp();
                if ($difference < DAY_IN_SECONDS) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * If payment past due. User have 14 days to solve issue with payment (change card for example).
     * @return bool True if payment status is past due. False otherwise.
     */
    public function isSubscriptionPaymentPastDue()
    {
        if ($this->subscriptionPaymentStatusOption->get() === 'past_due') {
            return true;
        }
        return false;
    }

    /**
     * Should we use local files or not.
     *
     * @return bool True if we should use local files.
     */
    public function isLocalFilesUsage()
    {
        return $this->useLocalFilesOption->get() && !$this->forceUseSetkaCDNOption->get();
    }

    /**
     * @return OptionInterface
     */
    public function getTokenOption()
    {
        return $this->tokenOption;
    }

    /**
     * @return OptionInterface
     */
    public function getPublicTokenOption()
    {
        return $this->publicTokenOption;
    }

    /**
     * @return OptionInterface
     */
    public function getSubscriptionStatusOption()
    {
        return $this->subscriptionStatusOption;
    }

    /**
     * @return OptionInterface
     */
    public function getSubscriptionActiveUntilOption()
    {
        return $this->subscriptionActiveUntilOption;
    }

    /**
     * @return OptionInterface
     */
    public function getSubscriptionPaymentStatusOption()
    {
        return $this->subscriptionPaymentStatusOption;
    }

    /**
     * @return OptionInterface
     */
    public function getEditorJSOption()
    {
        return $this->editorJSOption;
    }

    /**
     * @return OptionInterface
     */
    public function getEditorCSSOption()
    {
        return $this->editorCSSOption;
    }

    /**
     * @return ThemeResourceCSSLocalOption|ThemeResourceCSSOption
     */
    public function getThemeResourceCSSOption()
    {
        return $this->isLocalFilesUsage() ? $this->themeResourceCSSLocalOption : $this->themeResourceCSSOption;
    }

    /**
     * @return OptionInterface
     */
    public function getThemeResourceCSSCDNOption()
    {
        return $this->themeResourceCSSOption;
    }

    /**
     * @return OptionInterface
     */
    public function getThemeResourceCSSLocalOption()
    {
        return $this->themeResourceCSSLocalOption;
    }

    /**
     * @return OptionInterface
     */
    public function getThemeResourceJSOption()
    {
        return $this->themeResourceJSOption;
    }

    /**
     * @return OptionInterface
     */
    public function getThemeResourceJSLocalOption()
    {
        return $this->themeResourceJSLocalOption;
    }

    /**
     * @return OptionInterface
     */
    public function getThemePluginsJSOption()
    {
        return $this->themePluginsJSOption;
    }

    /**
     * @return OptionInterface
     */
    public function getUseLocalFilesOption()
    {
        return $this->useLocalFilesOption;
    }
}
