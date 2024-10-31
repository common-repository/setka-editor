<?php
namespace Setka\Editor\Service\SystemReport;

use Korobochkin\WPKit\Plugins\PluginInterface;
use Setka\Editor\Admin\Service\FilesManager\AssetsStatus;
use Setka\Editor\Service\AMP\AMPStatus;
use Setka\Editor\Service\Standalone\StandaloneStatus;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class SystemReportFactory
{
    /**
     * @var array
     */
    private static $converters = array(
        VarExportTypeConverter::class,
        PrintTypeConverter::class,
    );

    public static function create(
        PluginInterface $plugin,
        AMPStatus $AMPStatus,
        StandaloneStatus $standaloneStatus,
        AssetsStatus $assetsStatus,
        ParameterBag $parameterBag,
        Request $request
    ): SystemReport {
        return new SystemReport(
            array(
                new PluginSection($plugin),
                new CurrentUserSection(),
                new ActiveThemeSection(),
                new InstalledPluginsSection(),
                new AMPStatisticsSection($AMPStatus),
                new StandaloneStatisticsSection($standaloneStatus),
                new AssetsStatisticsSection($assetsStatus),
                new FilesystemSection(),
                new OptionsSection(),
                new ParameterBagSection($parameterBag),
                new HooksAndFiltersSection(),
                new GlobalVariablesSection(),
                new ServerSuperGlobalSection($request),
                new PHPConstantsSection(),
                new IniSection(),
                new PHPInfoSection(),
            ),
            self::createConverter()
        );
    }

    private static function createConverter(): TypeConverterInterface
    {
        foreach (self::$converters as $converter) {
            try {
                self::checkConvertMethods($converter);
                return new $converter();
            } catch (\Exception $exception) {
                return new EmptyTypeConverter();
            }
        }
    }

    /**
     * @param string $converter
     *
     * @throws \Exception
     */
    private static function checkConvertMethods(string $converter): void
    {
        foreach ($converter::$methods as $method) {
            if (!function_exists($method)) {
                throw new \Exception();
            }
        }
    }
}
