<?php
namespace Setka\Editor;

use Korobochkin\WPKit\Cron\CronEventInterface;
use Korobochkin\WPKit\Pages\Tabs\Tabs;
use Korobochkin\WPKit\Plugins\AbstractPlugin;
use Korobochkin\WPKit\Translations\PluginTranslations;
use Korobochkin\WPKit\Utils\RequestFactory;
use Korobochkin\WPKit\Utils\WordPressFeatures;
use Korobochkin\WPKit\Uninstall\Uninstall;
use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Ajax\AjaxRunner;
use Setka\Editor\Admin\Ajax\SetkaEditorAjaxStack;
use Setka\Editor\Admin\Cron\AMPStylesQueueCronEvent;
use Setka\Editor\Admin\Cron\Files\FilesManagerCronEvent;
use Setka\Editor\Admin\Cron\Files\FilesQueueCronEvent;
use Setka\Editor\Admin\Cron\Files\SendFilesStatCronEvent;
use Setka\Editor\Admin\Cron\AMPStylesCronEvent;
use Setka\Editor\Admin\Cron\SetkaPostCreatedCronEvent;
use Setka\Editor\Admin\Cron\StandaloneStylesCronEvent;
use Setka\Editor\Admin\Cron\StandaloneStylesQueueCronEvent;
use Setka\Editor\Admin\Cron\SyncAccountCronEvent;
use Setka\Editor\Admin\Cron\UpdateAnonymousAccountCronEvent;
use Setka\Editor\Admin\Cron\UserSignedUpCronEvent;
use Setka\Editor\Admin\Migrations\Configuration;
use Setka\Editor\Admin\Migrations\Versions\Version20170720130303;
use Setka\Editor\Admin\Migrations\Versions\Version20180102150532;
use Setka\Editor\Admin\Migrations\Versions\Version20200824154223;
use Setka\Editor\Admin\Notices\AfterSignInNotice;
use Setka\Editor\Admin\Notices\AMPSyncFailureNotice;
use Setka\Editor\Admin\Notices\AssetsLoadErrorNotice;
use Setka\Editor\Admin\Notices\NoticesStack;
use Setka\Editor\Admin\Notices\NoticesStackRunner;
use Setka\Editor\Admin\Notices\PaymentErrorNotice;
use Setka\Editor\Admin\Notices\SetkaEditorThemeDisabledNotice;
use Setka\Editor\Admin\Notices\SubscriptionBlockedNotice;
use Setka\Editor\Admin\Options;
use Setka\Editor\Admin\Options\AMP\AMPStylesIdOption;
use Setka\Editor\Admin\Options\AMP\AMPSyncAttemptsLimitFailureOption;
use Setka\Editor\Admin\Options\AMP\AMPSyncFailureNoticeOption;
use Setka\Editor\Admin\Options\AMP\AMPSyncFailureOption;
use Setka\Editor\Admin\Options\AMP\AMPSyncLastFailureNameOption;
use Setka\Editor\Admin\Options\AMP\AMPSyncOption;
use Setka\Editor\Admin\Options\AMP\AMPSyncStageOption;
use Setka\Editor\Admin\Options\AMP\UseAMPStylesOption;
use Setka\Editor\Admin\Options\DBVersionOption;
use Setka\Editor\Admin\Options\EditorAccessPostTypesOption;
use Setka\Editor\Admin\Options\EditorAccessRolesOption;
use Setka\Editor\Admin\Options\EditorCSSOption;
use Setka\Editor\Admin\Options\EditorJSOption;
use Setka\Editor\Admin\Options\EditorVersionOption;
use Setka\Editor\Admin\Options\Files\FilesOption;
use Setka\Editor\Admin\Options\Files\FileSyncFailureOption;
use Setka\Editor\Admin\Options\Files\ServiceSwitchOption;
use Setka\Editor\Admin\Options\Files\FileSyncStageOption;
use Setka\Editor\Admin\Options\Files\UseLocalFilesOption;
use Setka\Editor\Admin\Options\AMP\AMPCssOption;
use Setka\Editor\Admin\Options\AMP\AMPFontsOption;
use Setka\Editor\Admin\Options\AMP\AMPStylesOption;
use Setka\Editor\Admin\Options\PlanFeatures\PlanFeaturesOption;
use Setka\Editor\Admin\Options\PublicTokenOption;
use Setka\Editor\Admin\Options\SetkaPostCreatedOption;
use Setka\Editor\Admin\Options\SrcsetSizesOption;
use Setka\Editor\Admin\Options\SubscriptionActiveUntilOption;
use Setka\Editor\Admin\Options\SubscriptionPaymentStatusOption;
use Setka\Editor\Admin\Options\SubscriptionStatusOption;
use Setka\Editor\Admin\Options\ThemePluginsJSOption;
use Setka\Editor\Admin\Options\ThemeResourceCSSLocalOption;
use Setka\Editor\Admin\Options\ThemeResourceCSSOption;
use Setka\Editor\Admin\Options\ThemeResourceJSLocalOption;
use Setka\Editor\Admin\Options\ThemeResourceJSOption;
use Setka\Editor\Admin\Options\TokenOption;
use Setka\Editor\Admin\Options\WebhooksEndpointOption;
use Setka\Editor\Admin\Options\WhiteLabelOption;
use Setka\Editor\Admin\Pages\AdminPages;
use Setka\Editor\Admin\Pages\AdminPagesFormFactory;
use Setka\Editor\Admin\Pages\AdminPagesRunner;
use Setka\Editor\Admin\Pages\EditPost;
use Setka\Editor\Admin\Pages\PluginPagesFactory;
use Setka\Editor\Admin\Pages\Plugins;
use Setka\Editor\Admin\Pages\PluginsRunner;
use Setka\Editor\Admin\Pages\EditPostRunner;
use Setka\Editor\Admin\Pages\QuickLinks;
use Setka\Editor\Admin\Pages\QuickLinksRunner;
use Setka\Editor\Admin\Pages\Support\SupportPage;
use Setka\Editor\Admin\Pages\Tabs\SupportTab;
use Setka\Editor\Admin\Pages\Tabs\UninstallTab;
use Setka\Editor\Admin\Pages\TwigFactory;
use Setka\Editor\Admin\Pages\Uninstall\UninstallPage;
use Setka\Editor\Admin\Service\AdminScriptStyles;
use Setka\Editor\Admin\Service\AdminScriptStylesRunner;
use Setka\Editor\Admin\Service\APIs\WordPressClient;
use Setka\Editor\Admin\Service\AsyncUpload;
use Setka\Editor\Admin\Service\AsyncUploadRunner;
use Setka\Editor\Admin\Service\FilesManager\AssetsStatus;
use Setka\Editor\Admin\Service\FilesManager\FilesServiceManager;
use Setka\Editor\Admin\Service\GutenbergHandlePost;
use Setka\Editor\Admin\Service\GutenbergHandlePostRunner;
use Setka\Editor\Admin\Service\FilesManager\DownloadListOfFiles;
use Setka\Editor\Admin\Service\FilesManager\FilesManager;
use Setka\Editor\Admin\Service\FilesManager\FilesManagerFactory;
use Setka\Editor\Admin\Service\Js\EditorAdapterJsSettings;
use Setka\Editor\Admin\Service\Kses;
use Setka\Editor\Admin\Service\KsesRunner;
use Setka\Editor\Admin\Service\MigrationsRunner;
use Setka\Editor\Admin\Service\RichEdit;
use Setka\Editor\Admin\Service\RichEditRunner;
use Setka\Editor\Admin\Service\SavePost;
use Setka\Editor\Admin\Service\SavePostRunner;
use Setka\Editor\Admin\Service\Screen;
use Setka\Editor\Admin\Service\ScreenFactory;
use Setka\Editor\Admin\Service\SetkaEditorAPI\SetkaEditorAPI;
use Setka\Editor\Admin\Service\SetkaEditorAPI\SetkaEditorAPIFactory;
use Setka\Editor\Admin\Service\SetkaEditorAPI\Endpoints as SetkaEditorAPIEndpoints;
use Setka\Editor\Admin\Service\Upgrader;
use Setka\Editor\Admin\Service\UpgraderRunner;
use Setka\Editor\Admin\Transients\AfterSignInNoticeTransient;
use Setka\Editor\Admin\Transients\SettingsErrorsTransient;
use Setka\Editor\Admin\Transients\SettingsTokenTransient;
use Setka\Editor\API as WebHooks;
use Setka\Editor\API\V1\SetkaEditorPluginHttpStack;
use Setka\Editor\API\V1\SetkaEditorPluginHttpStackRunner;
use Setka\Editor\CLI\CliCommandsRunner;
use Setka\Editor\CLI\Commands\AccountCommand;
use Setka\Editor\CLI\Commands\AMPCommand;
use Setka\Editor\CLI\Commands\FilesCommand;
use Setka\Editor\CLI\Commands\StandaloneCommand;
use Setka\Editor\Entities\PostFactory;
use Setka\Editor\Entities\PostFactoryFactory;
use Setka\Editor\PostMetas\AttemptsToDownloadPostMeta;
use Setka\Editor\PostMetas\FileSubPathPostMeta;
use Setka\Editor\PostMetas\ImageAttachmentMetadataPostMeta;
use Setka\Editor\PostMetas\OriginUrlPostMeta;
use Setka\Editor\PostMetas\PostLayoutPostMeta;
use Setka\Editor\PostMetas\PostThemePostMeta;
use Setka\Editor\PostMetas\SetkaFileIDPostMeta;
use Setka\Editor\PostMetas\SetkaFileTypePostMeta;
use Setka\Editor\PostMetas\TypeKitIDPostMeta;
use Setka\Editor\PostMetas\UseEditorPostMeta;
use Setka\Editor\Service\Activation;
use Setka\Editor\Service\ActivationRunner;
use Setka\Editor\Admin\Pages\SetkaEditor\Account\AccountPage;
use Setka\Editor\Admin\Pages\SetkaEditor\SignUp\SignUpPage;
use Setka\Editor\Admin\Pages\Tabs\AccessTab;
use Setka\Editor\Admin\Pages\Tabs\AccountTab;
use Setka\Editor\Admin\Pages\Tabs\StartTab;
use Setka\Editor\Service\AMP\AMPServiceManager;
use Setka\Editor\Service\Config\AMPConfig;
use Setka\Editor\Service\AMP\AMPStatus;
use Setka\Editor\Service\Config\PluginConfig;
use Setka\Editor\Service\AMP\AMPFactory;
use Setka\Editor\Service\AMP\AMPStylesManagerFactory;
use Setka\Editor\Service\CronSchedules;
use Setka\Editor\Service\CronSchedulesRunner;
use Setka\Editor\Service\DataFactory;
use Setka\Editor\Service\Deactivation;
use Setka\Editor\Service\AMP\AMP;
use Setka\Editor\Service\AMP\AMPRunner;
use Setka\Editor\Service\AMP\AMPStylesManager;
use Setka\Editor\Service\Gutenberg\EditorGutenbergModule;
use Setka\Editor\Service\ImageSizes;
use Setka\Editor\Service\ImageSizesRunner;
use Setka\Editor\Service\LoggerFactory;
use Setka\Editor\Service\Config\FileSystemCache;
use Setka\Editor\Service\PostStatuses;
use Setka\Editor\Service\QuerySniffer\QuerySniffer;
use Setka\Editor\Service\QuerySniffer\QuerySnifferFactory;
use Setka\Editor\Service\QuerySniffer\QuerySnifferRunner;
use Setka\Editor\Service\ReactAndHTML;
use Setka\Editor\Service\ReactAndHTMLRunner;
use Setka\Editor\Service\ScriptStyles;
use Setka\Editor\Service\ScriptStylesRunner;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;
use Setka\Editor\Service\SetkaAccount\SignIn;
use Setka\Editor\Service\SetkaAccount\SignInFactory;
use Setka\Editor\Service\SetkaAccount\SignOut;
use Setka\Editor\Service\SetkaPostTypes;
use Setka\Editor\Service\Standalone\StandaloneServiceManager;
use Setka\Editor\Service\Standalone\StandaloneStatus;
use Setka\Editor\Service\Standalone\StandaloneStyles;
use Setka\Editor\Service\Standalone\StandaloneStylesFactory;
use Setka\Editor\Service\Standalone\StandaloneStylesManager;
use Setka\Editor\Service\Standalone\StandaloneStylesManagerFactory;
use Setka\Editor\Service\TheContent;
use Setka\Editor\Service\SystemReport\SystemReport;
use Setka\Editor\Service\SystemReport\SystemReportFactory;
use Setka\Editor\Service\WhiteLabel;
use Setka\Editor\Service\WhiteLabelFactory;
use Setka\Editor\Service\WhiteLabelRunner;
use Setka\Editor\Service\WPDBFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Validator\Validation;

/**
 * Class Plugin
 */
class Plugin extends AbstractPlugin
{
    const NAME = 'setka-editor';

    const _NAME_ = 'setka_editor';

    const VERSION = '2.1.20';

    const DB_VERSION = 20200824154223;

    const PHP_VERSION_ID_MIN = 70130;

    const PHP_VERSION_MIN = '7.1.3';

    const WP_VERSION_MIN = '4.1';

    const WP_VERSION_TESTED_UP_TO = '6.2';

    public function runOnLoad()
    {
        ActivationRunner::setContainer($this->container);
        register_activation_hook($this->getFile(), array(ActivationRunner::class, 'run'));

        \Setka\Editor\Service\Uninstall::setContainer($this->container);

        register_deactivation_hook($this->getFile(), array($this->getContainer()->get('wp.plugins.setka_editor.deactivation'), 'run'));

        /**
         * Uninstall. WordPress call this action when user click "Delete" link.
         *
         * Freemius rewrite register_uninstall_hook() call and we can't use it.
         * And until we are using Freemius we can run un-installer by just adding this action.
         *
         * @since 0.0.2
         */
        add_action('uninstall_' . $this->getBasename(), array('\Setka\Editor\Service\Uninstall', 'run'));

        UpgraderRunner::setContainer($this->container);
    }

    public function runOnLoadAdmin()
    {
        Admin\Service\Freemius::run($this->getDir());
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->container->get('wp.plugins.setka_editor.translations')->loadTranslations();

        ImageSizesRunner::setContainer($this->container);
        add_filter('image_size_names_choose', array(ImageSizesRunner::class, 'sizes'));

        QuerySnifferRunner::setContainer($this->container);
        add_action('wp_enqueue_scripts', array(QuerySnifferRunner::class, 'run'));

        ScriptStylesRunner::setContainer($this->container);
        add_action('wp_enqueue_scripts', array(ScriptStylesRunner::class, 'register'));
        add_action('wp_enqueue_scripts', array(ScriptStylesRunner::class, 'registerThemeResources'), 1000);
        // Enqueue resources for post markup on frontend
        add_action('wp_enqueue_scripts', array(ScriptStylesRunner::class, 'enqueue'), 1100);
        add_filter('script_loader_tag', array(ScriptStylesRunner::class, 'scriptLoaderTag'), 10, 2);
        add_filter('style_loader_tag', array(ScriptStylesRunner::class, 'styleLoaderTag'), 10, 2);

        if ($this->container->getParameter('wp.plugins.setka_editor.gutenberg_support')) {
            $this->container->get(ScriptStyles::class)->registerGutenberg();

            GutenbergHandlePostRunner::setContainer($this->container);
            ReactAndHTMLRunner::setContainer($this->container);
            foreach ($this->container->get(EditorAccessPostTypesOption::class)->get() as $postType) {
                add_filter('rest_prepare_' . $postType, array(GutenbergHandlePostRunner::class, 'maybeConvertClassicEditorPost'), 10, 3);
                add_filter('rest_prepare_' . $postType, array(ReactAndHTMLRunner::class, 'normalizeHTML'), 10, 3);
            }
        }

        CronSchedulesRunner::setContainer($this->container);
        add_filter('cron_schedules', array(CronSchedulesRunner::class, 'addSchedules')); // phpcs:ignore WordPress.WP.CronInterval.ChangeDetected

        $this->container->get(PostStatuses::class)->register();

        if ($this->container->getParameter('wp.plugins.setka_editor.wp_cron')) {
            foreach ($this->container->getParameter('wp.plugins.setka_editor.all_cron_events') as $eventReference) {
                /**
                 * @var $eventReference Reference
                 * @var $event CronEventInterface
                 */
                $event = $this->container->get($eventReference);
                add_action($event->getName(), array($event, $event->getHook()));
            }
        }

        if ($this->container->getParameter('wp.plugins.setka_editor.wp_cli')) {
            CliCommandsRunner::setContainer($this->container);
            CliCommandsRunner::run();
        }

        if ($this->container->getParameter('wp.plugins.setka_editor.amp_support')) {
            AMPRunner::setContainer($this->container);
            // AMP (AMPFactory) should be called after_setup_theme action. It is not available early.
            if ($this->container->get(AMP::class)->getMode() === AMPConfig::MODE_READER) {
                add_filter('amp_post_template_data', array(AMPRunner::class, 'classicTemplateData'), 10, 2);
                add_action('amp_post_template_css', array(AMPRunner::class, 'classicTemplateCss'));
            }
            add_filter('amp_content_sanitizers', array(AMPRunner::class, 'addSanitizers'));
        }

        SavePostRunner::setContainer($this->container);
        add_action('save_post', array(SavePostRunner::class, 'postAction'), 10, 3); // POST request
        add_filter('heartbeat_received', array(SavePostRunner::class, 'heartbeatReceived'), 10, 2); // Auto save

        AsyncUploadRunner::setContainer($this->container);
        add_filter('wp_handle_upload_prefilter', array(AsyncUploadRunner::class, 'preFilter'));

        return $this;
    }

    /**
     * @return $this
     */
    public function runNonAdmin()
    {
        /**
         * If post created with Setka Editor when this post don't need preparation before outputting
         * content via the_content(). For example: we don't need wpautop(), shortcode_unautop()...
         * More info (documentation) in \Setka\Editor\Service\TheContent class.
         *
         * You can easily disable this stuff and manipulate this filters as you need by simply removing
         * this three filters below. Don't forget what posts created with Setka Editor not should be
         * parsed by wpautop().
         *
         * @see \Setka\Editor\Service\TheContent
         */
        add_filter('the_content', array(TheContent::class, 'checkTheContentFilters'), 1);
        add_filter('the_content', array(TheContent::class, 'checkTheContentFiltersAfter'), 999);
        WhiteLabelRunner::setContainer($this->container);
        add_filter('the_content', array(WhiteLabelRunner::class, 'addLabel'), 1100);

        return $this;
    }

    /**
     * Run plugin for WordPress admin area.
     */
    public function runAdmin()
    {
        RichEditRunner::setContainer($this->container);
        add_filter('user_can_richedit', array(RichEditRunner::class, 'userCanRichEdit'));

        MigrationsRunner::setContainer($this->container);
        add_action('admin_init', array(MigrationsRunner::class, 'run'));

        AjaxRunner::setContainer($this->container);
        add_action('admin_init', array(AjaxRunner::class, 'run'));

        SavePostRunner::setContainer($this->container);
        add_action('save_post', array(SavePostRunner::class, 'postAction'), 10, 3); // POST request
        add_filter('heartbeat_received', array(SavePostRunner::class, 'heartbeatReceived'), 10, 2); // Auto save

        AdminPagesRunner::setContainer($this->container);
        add_action('admin_menu', array(AdminPagesRunner::class, 'run'));

        ScriptStylesRunner::setContainer($this->container);
        add_action('admin_enqueue_scripts', array(ScriptStylesRunner::class, 'register'));
        add_action('admin_enqueue_scripts', array(ScriptStylesRunner::class, 'registerThemeResources'), 1000);

        AdminScriptStylesRunner::setContainer($this->container);
        add_action('admin_enqueue_scripts', array(AdminScriptStylesRunner::class, 'run'));
        add_action('admin_enqueue_scripts', array(AdminScriptStylesRunner::class, 'enqueue'), 1100);

        // New and edit post
        EditPostRunner::setContainer($this->container);
        add_action('load-post.php', array(EditPostRunner::class, 'run'));
        add_action('load-post-new.php', array(EditPostRunner::class, 'run'));

        // Action links on /wp-admin/plugins.php
        PluginsRunner::setContainer($this->container);
        add_filter('plugin_action_links_' . $this->getBasename(), array(PluginsRunner::class, 'addActionLinks'));

        NoticesStackRunner::setContainer($this->container);
        add_action('admin_notices', array(NoticesStackRunner::class, 'run'));

        // Setka API requests (webhooks).
        SetkaEditorPluginHttpStackRunner::setContainer($this->container);
        add_action('admin_init', array(SetkaEditorPluginHttpStackRunner::class, 'run'));

        KsesRunner::setContainer($this->container);
        add_filter('wp_kses_allowed_html', array(KsesRunner::class, 'allowedHTML'), 10, 2);

        QuickLinksRunner::setContainer($this->container);
        add_action('admin_init', array(QuickLinksRunner::class, 'run'));
    }

    /**
     * @return $this
     */
    public function configureDependencies() // phpcs:disable Generic.Metrics.CyclomaticComplexity.MaxExceeded
    {
        /**
         * @var $container ContainerBuilder
         */
        $container = $this->getContainer();

        foreach (array(
            'plugin_basename' => array($this, 'getBasename'),
            'sync_files' => array(PluginConfig::class, 'isSyncFiles'),
            'sync_files.service_switch_env' => array(PluginConfig::class, 'serviceSwitchRegularFiles'),
            'wp_debug' => array(PluginConfig::class, 'isDebug'),
            'wp_cron' => array(PluginConfig::class, 'isCron'),
            'wp_cli' => array(PluginConfig::class, 'isCli'),
            'vip' => array(PluginConfig::class, 'isVIP'),
            'doing_ajax' => array(PluginConfig::class, 'isDoingAJAX'),
            'log_status' => array(PluginConfig::class, 'isLog'),
            'gutenberg_support' => array(PluginConfig::class, 'isGutenberg'),
            'continue_execution' => array(PluginConfig::class, 'getContinueExecution'),
            'storage_url' => array(PluginConfig::class, 'getStorageUrl'),
            'storage_path' => array(PluginConfig::class, 'getStoragePath'),
            'download_attempts' => array(PluginConfig::class, 'getFileDownloadAttempts'),
            'amp.file_max_size' => array(PluginConfig::class, 'getAMPMaxCSSSize'),
            'amp.sync_files' => array(PluginConfig::class, 'isSyncAMPFiles'),
            'amp.service_switch_env' => array(PluginConfig::class, 'serviceSwitchAMPFiles'),
            'api.endpoint' => array(PluginConfig::class, 'getSetkaAPIEndpoint'),
            'api.basic_auth_login' => array(PluginConfig::class, 'getSetkaAPIBasicAuthLogin'),
            'api.basic_auth_password' => array(PluginConfig::class, 'getSetkaAPIBasicAuthPassword'),
            'wp_version' => array(PluginConfig::class, 'getWPVersion'),
            'standalone.sync_files' => array(PluginConfig::class, 'isSyncStandaloneFiles'),
            'standalone.service_switch_env' => array(PluginConfig::class, 'serviceSwitchStandaloneFiles'),
            'standalone.self_hosted_files' => array(PluginConfig::class, 'isStandaloneSelfHostedFiles')
        ) as $parameterName => $parameterValue) {
            $parameterName = 'wp.plugins.setka_editor.' . $parameterName;
            if (!$container->hasParameter($parameterName)) {
                $container->setParameter($parameterName, call_user_func($parameterValue));
            }
        }

        foreach (array(
            'amp_support' => true,
            'amp_mode' => null, // We detect this parameter later on after_setup_theme WordPress action in AMPFactory::create.
            'manage_type_kit' => true,
            'templates_path' => path_join($this->getDir(), 'twig-templates'),
            'languages_path' => dirname($this->getBasename()) . '/languages',
            'storage_basename' => self::NAME,
            'dynamic.storage_path' => array(PluginConfig::class, 'getStoragePath'),
            'dynamic.storage_url' => array(PluginConfig::class, 'getStorageUrl'),
        ) as $parameterName => $parameterValue) {
            $parameterName = 'wp.plugins.setka_editor.' . $parameterName;
            if (!$container->hasParameter($parameterName)) {
                $container->setParameter($parameterName, $parameterValue);
            }
        }

        // Folder with cached files (templates + translations).
        if (!$container->hasParameter('wp.plugins.setka_editor.cache_dir')) {
            if (defined('SETKA_EDITOR_CACHE_DIR')) {
                $container->setParameter(
                    'wp.plugins.setka_editor.cache_dir',
                    SETKA_EDITOR_CACHE_DIR
                );
            } elseif (is_admin()) {
                $container->setParameter(
                    'wp.plugins.setka_editor.cache_dir',
                    FileSystemCache::getDirPath($this->getDir()) // Require WordPress FS stuff only on wp-admin.
                );
            } else {
                $container->setParameter(
                    'wp.plugins.setka_editor.cache_dir',
                    false
                );
            }
        }

        $container
            ->register('wp.plugins.setka_editor.translations', PluginTranslations::class)
            ->addArgument(self::NAME)
            ->addArgument('%wp.plugins.setka_editor.languages_path%');

        $container
            ->register(Activation::class, Activation::class)
            ->addArgument(new Reference(SetkaEditorAccount::class))
            ->addArgument(new Reference(SignIn::class));

        // Twig itself prepared for rendering Symfony Forms.
        $container
            ->register('wp.plugins.setka_editor.twig')
            ->setFactory(array(TwigFactory::class, 'create'))
            ->addArgument('%wp.plugins.setka_editor.cache_dir%')
            ->addArgument('%wp.plugins.setka_editor.templates_path%')
            ->setLazy(true);

        // Symfony Validator.
        $container
            ->register('wp.plugins.setka_editor.validator')
            ->setFactory(array(Validation::class, 'createValidator'))
            ->setLazy(true);

        // Symfony Form Factory for factory %).
        $container
            ->register('wp.plugins.setka_editor.form_factory_for_factory', AdminPagesFormFactory::class)
            ->addArgument(new Reference('wp.plugins.setka_editor.validator'));

        // Symfony Form Factory.
        $container
            ->register('wp.plugins.setka_editor.form_factory')
            ->setFactory(array(new Reference('wp.plugins.setka_editor.form_factory_for_factory'), 'create'))
            ->setLazy(true);

        // Logger Factory
        $container
            ->register('wp.plugins.setka_editor.logger_factory', LoggerFactory::class)
            ->addArgument($this->getDir())
            ->addArgument('%wp.plugins.setka_editor.log_status%')
            ->addArgument('%wp.plugins.setka_editor.wp_debug%')
            ->addArgument('%wp.plugins.setka_editor.wp_cli%')
            ->addArgument('%wp.plugins.setka_editor.vip%');

        // Logger for files sync.
        $container
            ->register('wp.plugins.setka_editor.logger.main', LoggerInterface::class)
            ->setFactory(array(new Reference('wp.plugins.setka_editor.logger_factory'), 'create'))
            ->addArgument('main');

        $container
            ->register('wp.plugins.setka_editor.logger.amp', LoggerInterface::class)
            ->setFactory(array(new Reference('wp.plugins.setka_editor.logger_factory'), 'createForStyleManager'))
            ->addArgument('amp');

        $container
            ->register('wp.plugins.setka_editor.logger.standalone', LoggerInterface::class)
            ->setFactory(array(new Reference('wp.plugins.setka_editor.logger_factory'), 'createForStyleManager'))
            ->addArgument('standalone');

        $container
            ->register('wp.plugins.setka_editor.request')
            ->setFactory(array(RequestFactory::class, 'create'));

        $container->register('wp.plugins.setka_editor.wpdb', \wpdb::class)
                  ->setFactory(array(WPDBFactory::class, 'create'));

        //--------------------------------------------------------------------------------------------------------------

        $container
            ->register(FilesManagerCronEvent::class, FilesManagerCronEvent::class)
            ->addMethodCall('setFilesManager', array(new Reference(FilesManager::class)));

        $container
            ->register(FilesQueueCronEvent::class, FilesQueueCronEvent::class)
            ->addMethodCall('setFilesManager', array(new Reference(FilesManager::class)));

        $container
            ->register(SendFilesStatCronEvent::class, SendFilesStatCronEvent::class)
            ->addMethodCall('setSetkaEditorAccount', array(new Reference(SetkaEditorAccount::class)))
            ->addMethodCall('setSetkaEditorAPI', array(new Reference(SetkaEditorAPI::class)))
            ->addMethodCall('setUseLocalFilesOption', array(new Reference(UseLocalFilesOption::class)))
            ->addMethodCall('setAssetsStatus', array(new Reference(AssetsStatus::class)));

        $container
            ->register(AMPStylesCronEvent::class, AMPStylesCronEvent::class)
            ->addMethodCall('setFilesManager', array(new Reference(AMPStylesManager::class)));

        $container
            ->register(AMPStylesQueueCronEvent::class, AMPStylesQueueCronEvent::class)
            ->addMethodCall('setFilesManager', array(new Reference(AMPStylesManager::class)));

        $container
            ->register(SetkaPostCreatedCronEvent::class, SetkaPostCreatedCronEvent::class)
            ->addMethodCall('setSetkaEditorAccount', array(new Reference(SetkaEditorAccount::class)))
            ->addMethodCall('setSetkaEditorAPI', array(new Reference(SetkaEditorAPI::class)))
            ->addMethodCall('setSetkaPostCreatedOption', array(new Reference(SetkaPostCreatedOption::class)));

        $container
            ->register(StandaloneStylesCronEvent::class, StandaloneStylesCronEvent::class)
            ->addMethodCall('setFilesManager', array(new Reference(StandaloneStylesManager::class)));

        $container
            ->register(StandaloneStylesQueueCronEvent::class, StandaloneStylesQueueCronEvent::class)
            ->addMethodCall('setFilesManager', array(new Reference(StandaloneStylesManager::class)));

        $container
            ->register(SyncAccountCronEvent::class, SyncAccountCronEvent::class)
            ->addMethodCall('setSetkaEditorAccount', array(new Reference(SetkaEditorAccount::class)))
            ->addMethodCall('setSignIn', array(new Reference(SignIn::class)));

        $container
            ->register(UpdateAnonymousAccountCronEvent::class, UpdateAnonymousAccountCronEvent::class)
            ->addMethodCall('setSetkaEditorAccount', array(new Reference(SetkaEditorAccount::class)))
            ->addMethodCall('setSignIn', array(new Reference(SignIn::class)));

        $container
            ->register(UserSignedUpCronEvent::class, UserSignedUpCronEvent::class)
            ->addMethodCall('setSetkaEditorAccount', array(new Reference(SetkaEditorAccount::class)))
            ->addMethodCall('setSetkaEditorAPI', array(new Reference(SetkaEditorAPI::class)));

        $container->setParameter(
            'wp.plugins.setka_editor.all_cron_events',
            array(
                new Reference(AMPStylesCronEvent::class),
                new Reference(AMPStylesQueueCronEvent::class),
                new Reference(FilesManagerCronEvent::class),
                new Reference(FilesQueueCronEvent::class),
                new Reference(SendFilesStatCronEvent::class),
                new Reference(SetkaPostCreatedCronEvent::class),
                new Reference(StandaloneStylesCronEvent::class),
                new Reference(StandaloneStylesQueueCronEvent::class),
                new Reference(SyncAccountCronEvent::class),
                new Reference(UpdateAnonymousAccountCronEvent::class),
                new Reference(UserSignedUpCronEvent::class),
            )
        );

        //--------------------------------------------------------------------------------------------------------------

        // Admin pages.
        $container
            ->register(AdminPages::class, AdminPages::class)
            ->addArgument(new Reference('wp.plugins.setka_editor.twig'))
            ->addArgument(new Reference('wp.plugins.setka_editor.form_factory'))
            ->addArgument(array(
                new Reference(Admin\Pages\SetkaEditor\SetkaEditorPage::class),
                new Reference(Admin\Pages\Settings\SettingsPage::class),
                new Reference(Admin\Pages\Files\FilesPage::class),
                new Reference(SupportPage::class),
                new Reference(Admin\Pages\Upgrade\Upgrade::class),
                new Reference(Admin\Pages\AMP\AMPPage::class),
                new Reference(Admin\Pages\Uninstall\UninstallPage::class),
            ));


        // Files
        $container
            ->register(Admin\Pages\Files\FilesPage::class, Admin\Pages\Files\FilesPage::class)
            ->addMethodCall('setAssetsStatus', array(new Reference(AssetsStatus::class)));

        $container
            ->register(SupportPage::class, SupportPage::class)
            ->setFactory(array(PluginPagesFactory::class, 'create'))
            ->addArgument(SupportPage::class)
            ->addArgument($container)
            ->addMethodCall('setSystemReport', array(new Reference(SystemReport::class)))
            ->addMethodCall('setFormFactory', array(new Reference('wp.plugins.setka_editor.form_factory')))
            ->addMethodCall('setRequest', array(new Reference('wp.plugins.setka_editor.request')));

        // Root
        $container
            ->register(Admin\Pages\SetkaEditor\SetkaEditorPage::class, Admin\Pages\SetkaEditor\SetkaEditorPage::class)
            ->addArgument(new Reference(AccountPage::class))
            ->addArgument(new Reference(SignUpPage::class))
            ->addArgument(new Reference(SetkaEditorAccount::class));

        $container
            ->register(AccountPage::class, AccountPage::class)
            ->addMethodCall('setTabs', array(new Reference('wp.plugins.setka_editor.admin.account_tabs')))
            ->addMethodCall('setSetkaEditorAccount', array(new Reference(SetkaEditorAccount::class)))
            ->addMethodCall('setSignIn', array(new Reference(SignIn::class)))
            ->addMethodCall('setSignOut', array(new Reference(SignOut::class)))
            ->addMethodCall('setFormFactory', array(new Reference('wp.plugins.setka_editor.form_factory')))
            ->addMethodCall('setRequest', array(new Reference('wp.plugins.setka_editor.request')));

        $container
            ->register(SignUpPage::class, SignUpPage::class)
            ->addArgument(new Reference(SignIn::class))
            ->addMethodCall('setTabs', array(new Reference('wp.plugins.setka_editor.admin.sign_up_tabs')))
            ->addMethodCall('setFormFactory', array(new Reference('wp.plugins.setka_editor.form_factory')))
            ->addMethodCall('setRequest', array(new Reference('wp.plugins.setka_editor.request')));

        $container
            ->register(Admin\Pages\Settings\SettingsPage::class)
            ->setFactory(array(PluginPagesFactory::class, 'create'))
            ->addArgument(Admin\Pages\Settings\SettingsPage::class)
            ->addArgument($container)
            ->addMethodCall('setNoticesStack', array(new Reference('wp.plugins.setka_editor.notices_stack')))
            ->addMethodCall('setFormFactory', array(new Reference('wp.plugins.setka_editor.form_factory')))
            ->addMethodCall('setRequest', array(new Reference('wp.plugins.setka_editor.request')))
            ->addMethodCall('setVip', array('%wp.plugins.setka_editor.vip%'))
            ->addMethodCall('setDataComponents', array(
                new Reference(SrcsetSizesOption::class),
                new Reference(EditorAccessPostTypesOption::class),
                new Reference(WebhooksEndpointOption::class),
                new Reference(Options\ForceUseSetkaCDNOption::class),
                new Reference(WhiteLabelOption::class),
            ))
            ->addMethodCall('setStandaloneServiceManager', array(new Reference(StandaloneServiceManager::class)));

        $container
            ->register(UninstallPage::class)
            ->setFactory(array(PluginPagesFactory::class, 'create'))
            ->addArgument(UninstallPage::class)
            ->addArgument($container)
            ->addMethodCall('setSetkaEditorAccount', array(new Reference(SetkaEditorAccount::class)))
            ->addMethodCall('setUseEditorPostMeta', array(new Reference(UseEditorPostMeta::class)));

        // Upgrade
        $container
            ->register(Admin\Pages\Upgrade\Upgrade::class, Admin\Pages\Upgrade\Upgrade::class);

        $container
            ->register(Admin\Pages\AMP\AMPPage::class)
            ->setFactory(array(PluginPagesFactory::class, 'create'))
            ->addArgument(Admin\Pages\AMP\AMPPage::class)
            ->addArgument($container)
            ->addMethodCall('setRequest', array(new Reference('wp.plugins.setka_editor.request')))
            ->addMethodCall('setAMPCssOption', array(new Reference(AMPCssOption::class)))
            ->addMethodCall('setAMPFontsOption', array(new Reference(AMPFontsOption::class)))
            ->addMethodCall('setFormFactory', array(new Reference('wp.plugins.setka_editor.form_factory')));

        // WordPress plugins
        $container
            ->register(Plugins::class, Plugins::class)
            ->addArgument(new Reference(Admin\Pages\SetkaEditor\SetkaEditorPage::class))
            ->addArgument(new Reference(SetkaEditorAccount::class));

        // Edit and New post
        $container
            ->register(EditPost::class, EditPost::class)
            ->addArgument('%wp.plugins.setka_editor.gutenberg_support%')
            ->addArgument(new Reference(ScriptStyles::class))
            ->addArgument(new Reference(AdminScriptStyles::class))
            ->addArgument(new Reference(SetkaEditorAccount::class))
            ->addArgument(new Reference(EditorAccessPostTypesOption::class))
            ->addArgument(new Reference(Screen::class))
            ->addArgument(new Reference(EditorGutenbergModule::class))
            ->addArgument(new Reference(PostFactory::class));

        $container
            ->register(EditorAdapterJsSettings::class, EditorAdapterJsSettings::class)
            ->addArgument(new Reference(SetkaEditorAccount::class))
            ->addArgument(new Reference(SrcsetSizesOption::class));

        $container
            ->register(AdminScriptStyles::class, AdminScriptStyles::class)
            ->addArgument($this->getUrl())
            ->addArgument(WordPressFeatures::isScriptDebug())
            ->addMethodCall('setEditorAdapterJsSettings', array(new Reference(EditorAdapterJsSettings::class)))
            ->addMethodCall('setScreen', array(new Reference(Screen::class)));

        $container
            ->register(CronSchedules::class, CronSchedules::class);

        $container
            ->register(QuerySniffer::class, QuerySniffer::class)
            ->setFactory(array(QuerySnifferFactory::class, 'create'))
            ->addArgument('%wp.plugins.setka_editor.gutenberg_support%')
            ->addArgument(new Reference(AMP::class))
            ->addArgument(new Reference(StandaloneStyles::class))
            ->addArgument(new Reference(DataFactory::class))
            ->addArgument(new Reference(ScriptStyles::class));

        $container->register(ReactAndHTML::class, ReactAndHTML::class);

        $container
            ->register(ScriptStyles::class, ScriptStyles::class)
            ->addArgument($this->getUrl())
            ->addArgument(WordPressFeatures::isScriptDebug())
            ->addMethodCall('setSetkaEditorAccount', array(new Reference(SetkaEditorAccount::class)))
            ->addMethodCall('setPluginSettingsPage', array(new Reference(Admin\Pages\SetkaEditor\SetkaEditorPage::class)))
            ->addMethodCall('setEditorGutenbergModule', array(new Reference(EditorGutenbergModule::class)))
            ->addMethodCall('setEditorAdapterJsSettings', array(new Reference(EditorAdapterJsSettings::class)))
            ->addMethodCall('setNoticesStack', array(new Reference('wp.plugins.setka_editor.notices_stack')));

        $container
            ->register(PostStatuses::class, PostStatuses::class);

        //--------------------------------------------------------------------------------------------------------------

        $container
            ->register('wp.plugins.setka_editor.uninstall', Uninstall::class)
            ->addMethodCall('setCronEvents', array('%wp.plugins.setka_editor.all_cron_events%'))
            ->addMethodCall('setSuppressExceptions', array(true));

        $container
            ->register('wp.plugins.setka_editor.deactivation', Deactivation::class)
            ->addArgument($this->getFile());

        $container
            ->register(WhiteLabel::class, WhiteLabel::class)
            ->setFactory(array(WhiteLabelFactory::class, 'create'))
            ->addArgument(new Reference(WhiteLabelOption::class))
            ->addArgument(new Reference(DataFactory::class))
            ->addArgument(new Reference(PlanFeaturesOption::class));

        $container
            ->register(AccessTab::class, AccessTab::class);

        $container
            ->register(AccountTab::class, AccountTab::class);

        $container
            ->register(StartTab::class, StartTab::class);

        $container
            ->register(SupportTab::class, SupportTab::class);

        $container
            ->register(UninstallTab::class, UninstallTab::class);

        $container
            ->register('wp.plugins.setka_editor.admin.account_tabs', Tabs::class)
            ->addMethodCall('addTab', array(new Reference(AccountTab::class)))
            ->addMethodCall('addTab', array(new Reference(AccessTab::class)))
            ->addMethodCall('addTab', array(new Reference(SupportTab::class)))
            ->addMethodCall('addTab', array(new Reference(UninstallTab::class)));

        $container
            ->register('wp.plugins.setka_editor.admin.sign_up_tabs', Tabs::class)
            ->addMethodCall('addTab', array(new Reference(StartTab::class)))
            ->addMethodCall('addTab', array(new Reference(AccessTab::class)))
            ->addMethodCall('addTab', array(new Reference(SupportTab::class)))
            ->addMethodCall('addTab', array(new Reference(UninstallTab::class)));

        //--------------------------------------------------------------------------------------------------------------

        $container
            ->register(AfterSignInNotice::class, AfterSignInNotice::class);

        $container
            ->register(AMPSyncFailureNotice::class, AMPSyncFailureNotice::class)
            ->addArgument(new Reference(AMPSyncFailureNoticeOption::class))
            ->addArgument(new Reference(AMPSyncFailureOption::class))
            ->addArgument(new Reference(AMPSyncLastFailureNameOption::class));

        $container
            ->register(AssetsLoadErrorNotice::class, AssetsLoadErrorNotice::class)
            ->addArgument(new Reference(Screen::class));

        $container
            ->register(PaymentErrorNotice::class, PaymentErrorNotice::class)
            ->addArgument(new Reference(SetkaEditorAccount::class));

        $container
            ->register(SetkaEditorThemeDisabledNotice::class, SetkaEditorThemeDisabledNotice::class)
            ->addArgument(new Reference(Screen::class));

        $container
            ->register(SubscriptionBlockedNotice::class, SubscriptionBlockedNotice::class)
            ->addArgument(new Reference(SetkaEditorAccount::class));

        $container->setParameter(
            'wp.plugins.setka_editor.all_notices',
            array(
                new Reference(AfterSignInNotice::class),
                new Reference(AMPSyncFailureNotice::class),
                new Reference(AssetsLoadErrorNotice::class),
                new Reference(PaymentErrorNotice::class),
                new Reference(SetkaEditorThemeDisabledNotice::class),
                new Reference(SubscriptionBlockedNotice::class),
            )
        );

        $container
            ->register('wp.plugins.setka_editor.notices_stack', NoticesStack::class)
            ->addArgument('%wp.plugins.setka_editor.gutenberg_support%')
            ->addArgument(new Reference(Screen::class))
            ->addMethodCall('setNotices', array('%wp.plugins.setka_editor.all_notices%'));

        //--------------------------------------------------------------------------------------------------------------

        $container
            ->register(DataFactory::class, DataFactory::class)
            ->addArgument(new Reference('wp.plugins.setka_editor.validator'));

        //--------------------------------------------------------------------------------------------------------------

        $dataFactoryReference = new Reference(DataFactory::class);

        $container->setParameter('wp.plugins.setka_editor.all_options', $this->getReferences(array(
            AMPCssOption::class => null,
            AMPFontsOption::class => null,
            AMPStylesIdOption::class => null,
            AMPStylesOption::class => null,
            AMPSyncAttemptsLimitFailureOption::class => null,
            AMPSyncFailureNoticeOption::class => null,
            AMPSyncFailureOption::class => null,
            AMPSyncLastFailureNameOption::class => null,
            AMPSyncOption::class => null,
            AMPSyncStageOption::class => null,
            Options\AMP\ServiceSwitchOption::class => null,
            UseAMPStylesOption::class => null,
            DBVersionOption::class => null,
            EditorAccessPostTypesOption::class => null,
            EditorAccessRolesOption::class => null,
            EditorCSSOption::class => null,
            EditorJSOption::class => null,
            EditorVersionOption::class => null,
            FilesOption::class => null,
            FileSyncFailureOption::class => null,
            ServiceSwitchOption::class => null,
            FileSyncStageOption::class => null,
            UseLocalFilesOption::class => null,
            Options\ForceUseSetkaCDNOption::class => null,
            PlanFeaturesOption::class => null,
            Options\Standalone\ServiceSwitchOption::class => null,
            Options\Standalone\StylesIdOption::class => null,
            Options\Standalone\StylesOption::class => null,
            Options\Standalone\SyncAttemptsLimitFailureOption::class => null,
            Options\Standalone\SyncFailureNoticeOption::class => null,
            Options\Standalone\SyncFailureOption::class => null,
            Options\Standalone\SyncLastFailureNameOption::class => null,
            Options\Standalone\SyncOption::class => null,
            Options\Standalone\SyncStageOption::class => null,
            Options\Standalone\UseCriticalOption::class => null,
            Options\Standalone\UseStylesOption::class => null,
            PublicTokenOption::class => null,
            SetkaPostCreatedOption::class => null,
            SrcsetSizesOption::class => null,
            SubscriptionActiveUntilOption::class => null,
            SubscriptionPaymentStatusOption::class => null,
            SubscriptionStatusOption::class => null,
            ThemePluginsJSOption::class => null,
            ThemeResourceCSSOption::class => null,
            ThemeResourceCSSLocalOption::class => null,
            ThemeResourceJSOption::class => null,
            ThemeResourceJSLocalOption::class => null,
            TokenOption::class => null,
            WebhooksEndpointOption::class => null,
            WhiteLabelOption::class => null,
        ), $dataFactoryReference));

        $container->setParameter('wp.plugins.setka_editor.all_transients', $this->getReferences(array(
            AfterSignInNoticeTransient::class => null,
            SettingsErrorsTransient::class => null,
            SettingsTokenTransient::class => null,
        ), $dataFactoryReference));

        $container->setParameter('wp.plugins.setka_editor.all_post_metas', $this->getReferences(array(
            ImageAttachmentMetadataPostMeta::class => null,

            AttemptsToDownloadPostMeta::class => null,
            FileSubPathPostMeta::class => null,
            OriginUrlPostMeta::class => null,
            PostLayoutPostMeta::class => null,
            PostThemePostMeta::class => null,
            SetkaFileIDPostMeta::class => null,
            SetkaFileTypePostMeta::class => null,
            TypeKitIDPostMeta::class => null,
            UseEditorPostMeta::class => null,
        ), $dataFactoryReference));

        // Set of composite components (not stored in DB, composed from multiple other components).
        // The reason of a separated list that this components doesn't require delete on uninstall.
        $this->registerDataComponents(array(
            Options\Standalone\UseAssetsAndUseStylesOption::class => array(
                new Reference(Options\Standalone\UseStylesOption::class),
                new Reference(UseLocalFilesOption::class),
            ),
        ), $dataFactoryReference);

        //--------------------------------------------------------------------------------------------------------------

        $container
            ->register(Version20170720130303::class, Version20170720130303::class)
            ->addArgument(new Reference(SetkaEditorAccount::class))
            ->addArgument(new Reference(FilesServiceManager::class));

        $container
            ->register(Version20180102150532::class, Version20180102150532::class)
            ->addArgument(new Reference(SetkaEditorAccount::class))
            ->addArgument(new Reference(SignIn::class));

        $container
            ->register(Version20200824154223::class, Version20200824154223::class)
            ->addArgument(new Reference(StandaloneServiceManager::class));

        $container->setParameter(
            'wp.plugins.setka_editor.migration_versions',
            array(
                new Reference(Version20170720130303::class),
                new Reference(Version20180102150532::class),
                new Reference(Version20200824154223::class),
            )
        );

        $container
            ->register('wp.plugins.setka_editor.migrations', Configuration::class)
            ->addArgument(new Reference(DBVersionOption::class))
            ->addArgument(self::DB_VERSION)
            ->addArgument('%wp.plugins.setka_editor.migration_versions%');

        //--------------------------------------------------------------------------------------------------------------

        $container
            ->register(SetkaEditorAccount::class, SetkaEditorAccount::class)
            ->addArgument(new Reference(TokenOption::class))
            ->addArgument(new Reference(SubscriptionStatusOption::class))
            ->addArgument(new Reference(SubscriptionActiveUntilOption::class))
            ->addArgument(new Reference(SubscriptionPaymentStatusOption::class))
            ->addArgument(new Reference(EditorJSOption::class))
            ->addArgument(new Reference(EditorCSSOption::class))
            ->addArgument(new Reference(PublicTokenOption::class))
            ->addArgument(new Reference(ThemeResourceCSSOption::class))
            ->addArgument(new Reference(ThemeResourceCSSLocalOption::class))
            ->addArgument(new Reference(ThemeResourceJSOption::class))
            ->addArgument(new Reference(ThemeResourceJSLocalOption::class))
            ->addArgument(new Reference(ThemePluginsJSOption::class))
            ->addArgument(new Reference(UseLocalFilesOption::class))
            ->addArgument(new Reference(Options\ForceUseSetkaCDNOption::class));

        $container
            ->register(SignIn::class, SignIn::class)
            ->setFactory(array(SignInFactory::class, 'create'))
            ->addArgument(new Reference(SetkaEditorAPI::class))
            ->addArgument(new Reference(FilesServiceManager::class))
            ->addArgument(new Reference(AMPServiceManager::class))
            ->addArgument(new Reference(StandaloneServiceManager::class))
            ->addArgument(new Reference(PlanFeaturesOption::class))
            ->addArgument(new Reference(EditorCSSOption::class))
            ->addArgument(new Reference(EditorJSOption::class))
            ->addArgument(new Reference(EditorVersionOption::class))
            ->addArgument(new Reference(PublicTokenOption::class))
            ->addArgument(new Reference(SetkaPostCreatedOption::class))
            ->addArgument(new Reference(SubscriptionActiveUntilOption::class))
            ->addArgument(new Reference(SubscriptionPaymentStatusOption::class))
            ->addArgument(new Reference(SubscriptionStatusOption::class))
            ->addArgument(new Reference(ThemePluginsJSOption::class))
            ->addArgument(new Reference(ThemeResourceCSSOption::class))
            ->addArgument(new Reference(ThemeResourceJSOption::class))
            ->addArgument(new Reference(TokenOption::class));

        $container
            ->register(SignOut::class, SignOut::class)
            ->addArgument($container);

        $container
            ->register(EditorGutenbergModule::class, EditorGutenbergModule::class)
            ->addArgument(new Reference(ScriptStyles::class))
            ->addArgument(new Reference(DataFactory::class))
            ->addArgument(new Reference(AMP::class))
            ->addArgument(new Reference(StandaloneStyles::class));

        //--------------------------------------------------------------------------------------------------------------

        $container
            ->register(AccountCommand::class, AccountCommand::class)
            ->addArgument(new Reference(DataFactory::class))
            ->addArgument(new Reference(SignIn::class))
            ->addArgument(new Reference(SignOut::class));

        $container
            ->register(AMPCommand::class, AMPCommand::class)
            ->addArgument(new Reference(AMPServiceManager::class))
            ->addArgument(new Reference(AMPStatus::class));

        $container
            ->register(StandaloneCommand::class, StandaloneCommand::class)
            ->addArgument(new Reference(StandaloneServiceManager::class))
            ->addArgument(new Reference(StandaloneStatus::class));

        $container
            ->register(FilesCommand::class, FilesCommand::class)
            ->addArgument(new Reference(FilesServiceManager::class))
            ->addArgument(new Reference(AssetsStatus::class));

        //--------------------------------------------------------------------------------------------------------------

        $container
            ->register(PostFactory::class, PostFactory::class)
            ->setFactory(array(PostFactoryFactory::class, 'create'))
            ->addArgument(new Reference(DataFactory::class));

        //--------------------------------------------------------------------------------------------------------------

        $container
            ->register(WordPressClient::class, WordPressClient::class);

        //--------------------------------------------------------------------------------------------------------------

        $container
            ->register(SetkaEditorAPI::class)
            ->setFactory(array(SetkaEditorAPIFactory::class, 'create'))
            ->addArgument(new Reference('wp.plugins.setka_editor.validator'))
            ->addArgument('%wp.plugins.setka_editor.wp_version%')
            ->addArgument(self::VERSION)
            ->addArgument(new Reference(WordPressClient::class))
            ->addArgument('%wp.plugins.setka_editor.api.endpoint%')
            ->addArgument('%wp.plugins.setka_editor.api.basic_auth_login%')
            ->addArgument('%wp.plugins.setka_editor.api.basic_auth_password%');

        //--------------------------------------------------------------------------------------------------------------

        $container
            ->register('wp.plugins.setka_editor.web_hooks', SetkaEditorPluginHttpStack::class)
            ->addArgument(array(
                '/webhook/setka-editor/v1/company_status/update' => WebHooks\V1\Actions\CompanyStatusUpdateAction::class,
                '/webhook/setka-editor/v1/resources/update' => WebHooks\V1\Actions\ResourcesUpdateAction::class,
                '/webhook/setka-editor/v1/token/check' => WebHooks\V1\Actions\TokenCheckAction::class,
                '/webhook/setka-editor/v1/files/update' => WebHooks\V1\Actions\UpdateFilesAction::class,
            ))
            ->addArgument(self::NAME)
            ->addMethodCall('setContainer', array($container))
            ->addMethodCall('setWebhooksEndpointOption', array(new Reference(WebhooksEndpointOption::class)));

        $container
            ->register('wp.plugins.setka_editor.ajax', SetkaEditorAjaxStack::class)
            ->addArgument(array(
                Admin\Ajax\DismissNoticesAction::class => Admin\Ajax\DismissNoticesAction::class,
            ))
            ->addArgument(self::NAME)
            ->addMethodCall('setContainer', array($container));

        //--------------------------------------------------------------------------------------------------------------

        $container
            ->register(DownloadListOfFiles::class, DownloadListOfFiles::class)
            ->addArgument(new Reference(SetkaEditorAPI::class))
            ->addArgument(new Reference(TokenOption::class));

        $container
            ->register(FilesManager::class, FilesManager::class)
            ->setFactory(array(FilesManagerFactory::class, 'create'))
            ->addArgument(new Reference(FileSyncStageOption::class))
            ->addArgument('%wp.plugins.setka_editor.continue_execution%')
            ->addArgument(new Reference('wp.plugins.setka_editor.logger.main'))
            ->addArgument('%wp.plugins.setka_editor.download_attempts%')
            ->addArgument('%wp.plugins.setka_editor.sync_files.service_switch_env%')
            ->addArgument(new Reference(FileSyncFailureOption::class))
            ->addArgument(new Reference(ServiceSwitchOption::class))
            ->addArgument(new Reference(UseLocalFilesOption::class))
            ->addArgument(new Reference(DownloadListOfFiles::class))
            ->addArgument(new Reference(FilesOption::class))
            ->addArgument('%wp.plugins.setka_editor.dynamic.storage_path%')
            ->addArgument('%wp.plugins.setka_editor.storage_basename%')
            ->addArgument('%wp.plugins.setka_editor.dynamic.storage_url%')
            ->addArgument(new Reference(ThemeResourceJSOption::class))
            ->addArgument(new Reference(ThemeResourceCSSOption::class))
            ->addArgument(new Reference(ThemeResourceJSLocalOption::class))
            ->addArgument(new Reference(ThemeResourceCSSLocalOption::class))
            ->addArgument(new Reference(DataFactory::class));

        $container->register(FilesServiceManager::class, FilesServiceManager::class)
            ->addArgument(new Reference(ServiceSwitchOption::class))
            ->addArgument('%wp.plugins.setka_editor.sync_files.service_switch_env%')
            ->addArgument(new Reference(FilesManagerCronEvent::class))
            ->addArgument(new Reference(FilesQueueCronEvent::class))
            ->addArgument(new Reference(FilesManager::class));

        $container->register(AssetsStatus::class, AssetsStatus::class)
            ->addArgument(SetkaPostTypes::getPostTypes(SetkaPostTypes::GROUP_FILES))
            ->addArgument(array(
                new Reference(FilesOption::class),
                new Reference(FileSyncFailureOption::class),
                new Reference(FileSyncStageOption::class),
                new Reference(ServiceSwitchOption::class),
                new Reference(UseLocalFilesOption::class),
            ))
            ->addArgument(new Reference('wp.plugins.setka_editor.wpdb'));

        //--------------------------------------------------------------------------------------------------------------

        $container->register(AsyncUpload::class, AsyncUpload::class)
            ->addArgument(new Reference('wp.plugins.setka_editor.request'));

        $container
            ->register(Kses::class, Kses::class);

        $container
            ->register(RichEdit::class, RichEdit::class)
            ->setFactory(array(RichEditRunner::class, 'create'))
            ->addArgument(new Reference(DataFactory::class))
            ->addArgument(new Reference(Screen::class));

        $container
            ->register(SavePost::class, SavePost::class)
            ->addArgument('%wp.plugins.setka_editor.doing_ajax%')
            ->addArgument(new Reference(SetkaPostCreatedOption::class))
            ->addArgument(new Reference(SetkaPostCreatedCronEvent::class))
            ->addArgument(new Reference(UseEditorPostMeta::class))
            ->addArgument(new Reference(PostThemePostMeta::class))
            ->addArgument(new Reference(PostLayoutPostMeta::class))
            ->addArgument('%wp.plugins.setka_editor.gutenberg_support%');

        $container
            ->register(Screen::class, Screen::class)
            ->addArgument(array(ScreenFactory::class, 'createWPScreen'));

        $container
            ->register(GutenbergHandlePost::class, GutenbergHandlePost::class)
            ->addArgument(new Reference(Screen::class))
            ->addArgument(new Reference(EditorGutenbergModule::class));

        $container->register(Upgrader::class, Upgrader::class);

        //--------------------------------------------------------------------------------------------------------------

        $container
            ->register(AMP::class, AMP::class)
            ->setFactory(array(AMPFactory::class, 'create'))
            ->addArgument('%wp.plugins.setka_editor.amp_support%')
            ->addArgument('%wp.plugins.setka_editor.amp_mode%')
            ->addArgument(new Reference(AMPCssOption::class))
            ->addArgument(new Reference(AMPFontsOption::class))
            ->addArgument(new Reference(AMPStylesOption::class))
            ->addArgument(new Reference(UseAMPStylesOption::class))
            ->addArgument(new Reference(DataFactory::class));

        $container
            ->register(AMPStylesManager::class, AMPStylesManager::class)
            ->setFactory(array(AMPStylesManagerFactory::class, 'create'))
            ->addArgument(new Reference(AMPSyncStageOption::class))
            ->addArgument('%wp.plugins.setka_editor.continue_execution%')
            ->addArgument(new Reference('wp.plugins.setka_editor.logger.amp'))

            ->addArgument(new Reference(AMPStylesIdOption::class))
            ->addArgument(new Reference(AMPStylesOption::class))
            ->addArgument(new Reference(AMPSyncFailureOption::class))
            ->addArgument(new Reference(AMPSyncLastFailureNameOption::class))
            ->addArgument(new Reference(AMPSyncFailureNoticeOption::class))
            ->addArgument(new Reference(AMPSyncAttemptsLimitFailureOption::class))
            ->addArgument(new Reference(UseAMPStylesOption::class))
            ->addArgument(new Reference(AMPSyncOption::class))
            ->addArgument(new Reference(DataFactory::class))

            ->addArgument('%wp.plugins.setka_editor.download_attempts%')
        ;

        $container->register(AMPServiceManager::class, AMPServiceManager::class)
            ->addArgument(new Reference(Options\AMP\ServiceSwitchOption::class))
            ->addArgument('%wp.plugins.setka_editor.amp.service_switch_env%')
            ->addArgument(new Reference(AMPStylesCronEvent::class))
            ->addArgument(new Reference(AMPStylesQueueCronEvent::class))
            ->addArgument(new Reference(AMPStylesManager::class));

        $container
            ->register(AMPStatus::class, AMPStatus::class)
            ->addArgument(SetkaPostTypes::getAllPostTypes(SetkaPostTypes::GROUP_AMP))
            ->addArgument(array(
                new Reference(AMPStylesIdOption::class),
                new Reference(AMPStylesOption::class),
                new Reference(AMPSyncAttemptsLimitFailureOption::class),
                new Reference(AMPSyncFailureNoticeOption::class),
                new Reference(AMPSyncFailureOption::class),
                new Reference(AMPSyncLastFailureNameOption::class),
                new Reference(AMPSyncOption::class),
                new Reference(AMPSyncStageOption::class),
                new Reference(UseAMPStylesOption::class),
            ))
            ->addArgument(new Reference('wp.plugins.setka_editor.wpdb'));

        //--------------------------------------------------------------------------------------------------------------

        $container->register(StandaloneStylesManager::class, StandaloneStylesManager::class)
            ->setFactory(array(StandaloneStylesManagerFactory::class, 'create'))
            ->addArgument(new Reference(Options\Standalone\SyncStageOption::class))
            ->addArgument('%wp.plugins.setka_editor.continue_execution%')
            ->addArgument(new Reference('wp.plugins.setka_editor.logger.standalone'))
            ->addArgument(new Reference(Options\Standalone\StylesIdOption::class))
            ->addArgument(new Reference(Options\Standalone\StylesOption::class))
            ->addArgument(new Reference(Options\Standalone\SyncFailureOption::class))
            ->addArgument(new Reference(Options\Standalone\SyncLastFailureNameOption::class))
            ->addArgument(new Reference(Options\Standalone\SyncFailureNoticeOption::class))
            ->addArgument(new Reference(Options\Standalone\SyncAttemptsLimitFailureOption::class))
            ->addArgument(new Reference(Options\Standalone\UseStylesOption::class))
            ->addArgument(new Reference(Options\Standalone\SyncOption::class))
            ->addArgument(new Reference(DataFactory::class))
            ->addArgument('%wp.plugins.setka_editor.download_attempts%')
            ->addArgument('%wp.plugins.setka_editor.standalone.self_hosted_files%')
            ->addArgument('%wp.plugins.setka_editor.dynamic.storage_path%')
            ->addArgument('%wp.plugins.setka_editor.dynamic.storage_url%')
            ->addArgument('%wp.plugins.setka_editor.storage_basename%');

        $container->register(StandaloneServiceManager::class, StandaloneServiceManager::class)
            ->addArgument(new Reference(Options\Standalone\ServiceSwitchOption::class))
            ->addArgument('%wp.plugins.setka_editor.standalone.service_switch_env%')
            ->addArgument(new Reference(StandaloneStylesCronEvent::class))
            ->addArgument(new Reference(StandaloneStylesQueueCronEvent::class))
            ->addArgument(new Reference(StandaloneStylesManager::class))
            ->addArgument(new Reference(Options\Standalone\UseCriticalOption::class));

        $container
            ->register(StandaloneStatus::class, StandaloneStatus::class)
            ->addArgument(SetkaPostTypes::getAllPostTypes(SetkaPostTypes::GROUP_STANDALONE))
            ->addArgument(array(
                new Reference(Options\Standalone\StylesIdOption::class),
                new Reference(Options\Standalone\StylesOption::class),
                new Reference(Options\Standalone\SyncAttemptsLimitFailureOption::class),
                new Reference(Options\Standalone\SyncFailureNoticeOption::class),
                new Reference(Options\Standalone\SyncFailureOption::class),
                new Reference(Options\Standalone\SyncLastFailureNameOption::class),
                new Reference(Options\Standalone\SyncOption::class),
                new Reference(Options\Standalone\SyncStageOption::class),
                new Reference(Options\Standalone\UseStylesOption::class),
            ))
            ->addArgument(new Reference('wp.plugins.setka_editor.wpdb'));

        $container->register(StandaloneStyles::class, StandaloneStyles::class)
            ->setFactory(array(StandaloneStylesFactory::class, 'create'))
            ->addArgument(new Reference(Options\Standalone\StylesOption::class))
            ->addArgument(new Reference(Options\Standalone\UseAssetsAndUseStylesOption::class))
            ->addArgument(new Reference(DataFactory::class))
            ->addArgument(new Reference(Options\ForceUseSetkaCDNOption::class))
            ->addArgument(new Reference(Options\Standalone\UseCriticalOption::class))
            ->addArgument('%wp.plugins.setka_editor.dynamic.storage_url%')
            ->addArgument('%wp.plugins.setka_editor.storage_basename%');

        //--------------------------------------------------------------------------------------------------------------

        $container
            ->register(QuickLinks::class, QuickLinks::class)
            ->addArgument(new Reference(UseEditorPostMeta::class))
            ->addArgument(new Reference(EditorAccessPostTypesOption::class));


        //--------------------------------------------------------------------------------------------------------------

        $container
            ->register(ImageSizes::class, ImageSizes::class)
            ->addArgument(new Reference('wp.plugins.setka_editor.request'))
            ->addArgument(new Reference(SrcsetSizesOption::class));

        $container
            ->register(SystemReport::class, SystemReport::class)
            ->setFactory(array(SystemReportFactory::class, 'create'))
            ->addArgument($this)
            ->addArgument(new Reference(AMPStatus::class))
            ->addArgument(new Reference(StandaloneStatus::class))
            ->addArgument(new Reference(AssetsStatus::class))
            ->addArgument($this->container->getParameterBag())
            ->addArgument(new Reference('wp.plugins.setka_editor.request'));

        return $this;
    }

    /**
     * @param $className string PHP Class name (NodeInterface).
     * @param $factory Reference
     * @param $classArguments ?array
     */
    private function registerDataComponent($className, Reference $factory, array $classArguments = null)
    {
        $component = $this->container->register($className, $className)
                        ->setFactory(array($factory, ($classArguments) ? 'createWithArgs' : 'create'))
                        ->addArgument($className);

        if ($classArguments) {
            $component->addArgument($classArguments);
        }
    }

    /**
     * @param array $classes
     * @param Reference $dataFactoryReference
     *
     * @return Reference[]
     */
    private function getReferences(array $classes, Reference $dataFactoryReference)
    {
        $references = array();
        foreach ($classes as $className => $classArguments) {
            $this->registerDataComponent($className, $dataFactoryReference, $classArguments);
            $references[] = new Reference($className);
        }
        return $references;
    }

    /**
     * @param array $classes
     * @param Reference $dataFactoryReference
     */
    private function registerDataComponents(array $classes, Reference $dataFactoryReference)
    {
        foreach ($classes as $className => $classArguments) {
            $this->registerDataComponent($className, $dataFactoryReference, $classArguments);
        }
    }

    /**
     * @inheritdoc
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return self::NAME;
    }
}
