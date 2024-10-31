<?php
namespace Setka\Editor\Admin\Migrations;

use Korobochkin\WPKit\Options\OptionInterface;
use Setka\Editor\Admin\Migrations\Exceptions\MigrationNameException;
use Setka\Editor\Admin\Migrations\Exceptions\MissedCurrentVersionException;
use Setka\Editor\Admin\Migrations\Exceptions\MissedRequiredVersionException;

class Configuration
{

    /**
     * @var array List of versions.
     */
    protected $versionClasses;

    /**
     * @var MigrationInterface[] List of classes with Migrations
     */
    protected $versions;

    /**
     * @var OptionInterface Current version option (loaded and saved).
     */
    protected $currentVersionOption;

    /**
     * @var integer Version which required by code.
     */
    protected $requiredVersion;

    /**
     * Configuration constructor.
     *
     * @param $currentVersion OptionInterface Current version from DB.
     * @param $requiredVersion int The version of current code.
     * @param $versionClasses array List of version classes.
     */
    public function __construct(OptionInterface $currentVersion, $requiredVersion, $versionClasses)
    {
        $this->currentVersionOption = $currentVersion;
        $this->requiredVersion      = $requiredVersion;
        $this->versionClasses       = $versionClasses;
    }

    /**
     * Migrations will run if necessary.
     *
     * @throws \Exception
     *
     * @return $this For chain calls.
     */
    public function migrateAsNecessary()
    {
        if (!$this->shouldWeRunMigrations()) {
            return $this;
        }

        $this->versions = $this->buildMigrationClasses();

        $this->validateVersions();

        $this->migrate();

        return $this;
    }

    /**
     * Execute each version update.
     *
     * After each update we update currentVersionOption.
     *
     * @return $this For chain calls.
     */
    public function migrate()
    {
        $currentVersion = $this->currentVersionOption->get();

        reset($this->versions);

        // Upgrade mode
        if ($currentVersion < $this->requiredVersion) {
            foreach ($this->versions as $versionIndex => $versionClass) {
                if ($versionIndex <= $currentVersion) {
                    continue;
                }

                if (!is_object($versionClass)) {
                    $this->versions[$versionIndex] = new $versionClass();
                }

                $this->versions[$versionIndex]->up();

                // Save migration index after execute it.
                $this->currentVersionOption->updateValue($versionIndex);
            }
        }

        return $this;
    }

    /**
     * Checks if current version differs from required.
     *
     * @return bool True if versions is not equals, false otherwise.
     */
    public function shouldWeRunMigrations()
    {
        $currentVersion = $this->currentVersionOption->get();

        if ($currentVersion !== $this->requiredVersion) {
            return true;
        }

        return false;
    }

    /**
     * Builds an array with names of Migration classes.
     *
     * @return array Names of Migration classes.
     *
     * @throws MigrationNameException if Migration class have invalid name.
     */
    protected function buildMigrationClasses()
    {
        $migrations = array();

        foreach ($this->versionClasses as $version) {
            $result = preg_match('/Version(\d{1,})$/', get_class($version), $matches);
            if (1 === $result) {
                $migrations[$matches[1]] = $version;
            } else {
                throw new MigrationNameException();
            }
        }

        return $migrations;
    }

    /**
     * Check for version existing in list of versions.
     *
     * @return $this For chain calls.
     *
     * @throws MissedCurrentVersionException If current version not found in versions list.
     * @throws MissedRequiredVersionException If required version not found in versions list.
     */
    public function validateVersions()
    {
        $currentVersion = (int) $this->currentVersionOption->get();

        if (0 === $currentVersion) {
            return $this;
        }

        // Unknown current version
        if (!isset($this->versions[$currentVersion])) {
            throw new MissedCurrentVersionException();
        }

        // Unknown required version
        if (!isset($this->versions[$this->requiredVersion])) {
            throw new MissedRequiredVersionException();
        }

        return $this;
    }
}
