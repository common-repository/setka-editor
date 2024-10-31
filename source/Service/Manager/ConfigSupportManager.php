<?php
namespace Setka\Editor\Service\Manager;

use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Options\Styles\AbstractSyncStageOption;
use Setka\Editor\Admin\Options\Styles\ConfigIdOptionInterface;
use Setka\Editor\Admin\Options\Styles\ConfigOptionInterface;
use Setka\Editor\Admin\Service\ContinueExecution\OutOfTimeException;
use Setka\Editor\Exceptions\LogicException;
use Setka\Editor\Service\Manager\Exceptions\InvalidConfigException;
use Setka\Editor\Service\Manager\Exceptions\JsonDecodeException;
use Setka\Editor\Service\Manager\Exceptions\NoConfigException;
use Setka\Editor\Service\Manager\Exceptions\PostException;
use Setka\Editor\Service\Manager\Stacks\ConfigFactory;
use Setka\Editor\Service\Manager\Stacks\PreviousConfigsFactory;
use Setka\Editor\Service\PostStatuses;

class ConfigSupportManager extends Manager implements ConfigSupportManagerInterface
{
    use PostOperationsTrait;

    /**
     * @var ConfigIdOptionInterface
     */
    private $configIdOption;

    /**
     * @var ConfigOptionInterface
     */
    protected $configOption;

    /**
     * @var PostConfig
     */
    protected $config;

    /**
     * @var string
     */
    private $configPostType;

    /**
     * ConfigSupportManager constructor.
     *
     * @param AbstractSyncStageOption $currentStageOption
     * @param array $stagesMap
     * @param callable $continueExecution
     * @param LoggerInterface $logger
     * @param ConfigIdOptionInterface $configIdOption
     * @param ConfigOptionInterface $configOption
     * @param string $configPostType
     */
    public function __construct(
        AbstractSyncStageOption $currentStageOption,
        array $stagesMap,
        $continueExecution,
        LoggerInterface $logger,
        ConfigIdOptionInterface $configIdOption,
        ConfigOptionInterface $configOption,
        $configPostType
    ) {
        parent::__construct($currentStageOption, $stagesMap, $continueExecution, $logger);

        $this->configIdOption = $configIdOption;
        $this->configOption   = $configOption;
        $this->configPostType = $configPostType;
    }

    /**
     * @inheritDoc
     */
    public function reset()
    {
        $this->configIdOption->delete();
        $this->configOption->delete();
        return parent::reset();
    }

    /**
     * @throws NoConfigException
     * @throws InvalidConfigException
     * @throws JsonDecodeException
     */
    protected function beforeStages()
    {
        $this->config = $this->createLastConfig();
        if (!$this->isSynced()) {
            $this->config->decode();
            try {
                $results = $this->configOption->setLocalValue($this->config->get())->validate();
            } catch (\Exception $exception) {
                throw new InvalidConfigException(null, $exception);
            }

            if (0 !== count($results)) {
                throw new InvalidConfigException($results);
            }

            $this->reset()->syncConfigWithOptions();
        }
    }

    /**
     * @return bool
     */
    private function isSynced()
    {
        return $this->getConfigIdFromOption() === $this->config->getId();
    }

    /**
     * @return $this
     */
    private function syncConfigWithOptions()
    {
        if ($this->isSynced() || !$this->config->get()) {
            throw new LogicException();
        }

        $this->configOption->updateValue($this->config->get());
        $this->configIdOption->updateValue($this->config->getId());

        return $this;
    }

    /**
     * @return int
     */
    private function getConfigIdFromOption()
    {
        return (int) $this->configIdOption->get();
    }

    /**
     * @return Config
     * @throws NoConfigException
     * @throws InvalidConfigException
     */
    private function createLastConfig()
    {
        $configFactory = new ConfigFactory($this->configPostType);

        $query = $configFactory->createQuery();

        if ($query->have_posts()) {
            return new PostConfig($query->next_post());
        }

        throw new NoConfigException();
    }

    /**
     * @inheritDoc
     */
    public function addNewConfig(array $config)
    {
        $config = new Config($config);

        $configSerialized = $config->encode();

        $this->insertPost(array(
            'post_type' => $this->configPostType,
            'post_content' => $configSerialized,
            'post_status' => PostStatuses::PUBLISH,
        ));

        return $this;
    }

    /**
     * Remove older configs.
     *
     * @throws OutOfTimeException If current cron process obsolete and we need to break execution.
     *
     * @throws PostException If post was not deleted.
     *
     * @return $this For chain calls.
     */
    protected function removePreviousConfigs()
    {
        $previousConfigsFactory = new PreviousConfigsFactory($this->configPostType, $this->config->getDateGMT());
        do {
            $query = $previousConfigsFactory->createQuery();
            $this->continueExecution();

            $this->logger->debug('Made query for previous configs.');
            $context = array('ids' => array(), 'counter' => (int) $query->found_posts);

            while ($query->have_posts()) {
                $post = $query->next_post();
                $this->deletePost($post);
                $context['ids'][] = $post->ID;
            }

            $this->logger->debug('Bunch with configs were removed.', $context);
            $query->rewind_posts();
        } while ($query->have_posts());

        $this->logger->info('All previous configs were removed.');

        return $this;
    }
}
