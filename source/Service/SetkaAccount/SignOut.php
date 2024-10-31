<?php
namespace Setka\Editor\Service\SetkaAccount;

use Korobochkin\WPKit\Cron\CronEventInterface;
use Korobochkin\WPKit\Options\OptionInterface;
use Setka\Editor\Admin\Cron;
use Setka\Editor\Admin\Options;
use Setka\Editor\Admin\Service\FilesManager\FilesManager;
use Setka\Editor\Service\AMP\AMPServiceManager;
use Setka\Editor\Service\Standalone\StandaloneServiceManager;
use Setka\Editor\Service\Standalone\StandaloneStylesManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SignOut implements ContainerAwareInterface
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * SignOut constructor.
     * @param ContainerBuilder $container
     */
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * @throws \Exception
     * @return $this
     */
    public function signOutAction()
    {
        /**
         * @var $options OptionInterface[]
         */
        $options = array();

        $options[] = $this->get(Options\EditorCSSOption::class);
        $options[] = $this->get(Options\EditorJSOption::class);
        $options[] = $this->get(Options\EditorVersionOption::class);
        $options[] = $this->get(Options\Files\FilesOption::class);
        $options[] = $this->get(Options\Files\FileSyncFailureOption::class);
        $options[] = $this->get(Options\Files\FileSyncStageOption::class);
        $options[] = $this->get(Options\Files\UseLocalFilesOption::class);
        $options[] = $this->get(Options\PlanFeatures\PlanFeaturesOption::class);
        $options[] = $this->get(Options\PublicTokenOption::class);
        $options[] = $this->get(Options\SetkaPostCreatedOption::class);

        $options[] = $this->get(Options\SubscriptionActiveUntilOption::class);
        $options[] = $this->get(Options\SubscriptionPaymentStatusOption::class);
        $options[] = $this->get(Options\SubscriptionStatusOption::class);

        $options[] = $this->get(Options\ThemePluginsJSOption::class);
        $options[] = $this->get(Options\ThemeResourceCSSOption::class);
        $options[] = $this->get(Options\ThemeResourceCSSLocalOption::class);
        $options[] = $this->get(Options\ThemeResourceJSOption::class);
        $options[] = $this->get(Options\ThemeResourceJSLocalOption::class);

        $options[] = $this->get(Options\TokenOption::class);
        $options[] = $this->get(Options\WhiteLabelOption::class);

        foreach ($options as $option) {
            $option->delete();
        }

        /**
         * @var $tasks CronEventInterface[]
         */
        $tasks = array();

        $tasks[] = $this->get(Cron\Files\FilesManagerCronEvent::class);
        $tasks[] = $this->get(Cron\Files\FilesQueueCronEvent::class);
        $tasks[] = $this->get(Cron\Files\SendFilesStatCronEvent::class);

        $tasks[] = $this->get(Cron\SetkaPostCreatedCronEvent::class);

        $tasks[] = $this->get(Cron\SyncAccountCronEvent::class);

        $tasks[] = $this->get(Cron\UserSignedUpCronEvent::class);
        $tasks[] = $this->get(Cron\UpdateAnonymousAccountCronEvent::class);

        foreach ($tasks as $task) {
            $task->unScheduleAll();
        }

        /**
         * @var $filesManager FilesManager
         * @var $AMPServiceManager AMPServiceManager
         * @var $standaloneServiceManager StandaloneServiceManager
         */

        try {
            $filesManager = $this->get(FilesManager::class);
            $filesManager->markAllFilesAsArchived();
        } catch (\Exception $exception) {
        }

        try {
            $AMPServiceManager = $this->get(AMPServiceManager::class);
            $AMPServiceManager->disable(false);
            $AMPServiceManager->discardCurrentState();
        } catch (\Exception $exception) {
        }

        try {
            $standaloneServiceManager = $this->get(StandaloneServiceManager::class);
            $standaloneServiceManager->disable(false);
            $standaloneServiceManager->discardCurrentState();
        } catch (\Exception $exception) {
        }

        return $this;
    }

    /**
     * @param ContainerInterface|null $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Get service by name (id).
     *
     * @param $id string Name of service.
     * @throws \Exception
     * @return object Service instance.
     */
    public function get($id)
    {
        return $this->container->get($id);
    }
}
