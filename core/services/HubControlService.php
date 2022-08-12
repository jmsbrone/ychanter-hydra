<?php

namespace app\core\services;

use app\common\helpers\VersionHelper;
use app\common\models\SubsystemModuleVersion;
use app\common\models\SystemModule;
use app\common\traits\GenericModuleInfo;
use app\core\api\HubAPI;
use app\core\exceptions\SubsystemModuleVersionNotFoundException;
use app\core\exceptions\SubsystemNonResponsiveException;
use app\core\spi\SubsystemManagerAPI;
use Exception;
use yii\helpers\ArrayHelper;

class HubControlService implements HubAPI
{
    /**
     * @param SubsystemManagerAPI[] $subsystems Controlled subsystems
     * Must be a map between subsystem ID and its API instance
     */
    public function __construct(
        protected array $subsystems
    ) {
        $this->subsystems = ArrayHelper::index($this->subsystems, fn($subsystem) => $subsystem->getSubsystemId());
    }

    /**
     * @inheritDoc
     */
    public function getModules(): array
    {
        $modules = [];
        foreach ($this->subsystems as $subsystem) {
            $subsystemModules = $subsystem->getAvailableModules();
            $modules = $this->collectSubsystemModulesToList($subsystemModules, $modules);
        }

        return $modules;
    }

    /**
     * @inheritDoc
     */
    public function installModule(string $moduleId, array $versionConstraintsMap): void
    {
        $moduleVersions = $this->determineVersionsForInstallation($moduleId, $versionConstraintsMap);
        $this->installModuleVersions($moduleVersions);
    }

    /**
     * @inheritDoc
     */
    public function uninstallModule(string $moduleId): void
    {
        foreach ($this->subsystems as $subsystem) {
            $subsystem->uninstallModule($moduleId);
        }
    }

    /**
     * @inheritDoc
     */
    public function activateModule(string $moduleId): void
    {
        foreach ($this->subsystems as $subsystem) {
            $subsystem->activateModule($moduleId);
        }
    }

    /**
     * @inheritDoc
     */
    public function deactivateModule(string $moduleId): void
    {
        foreach ($this->subsystems as $subsystem) {
            $subsystem->deactivateModule($moduleId);
        }
    }

    /**
     * @inheritDoc
     */
    public function upgradeModule(string $moduleId, string $versionConstraint = '*'): void
    {
        // TODO implement module upgrade
        throw new Exception('Not implemented');
    }

    /**
     * @inheritDoc
     */
    public function downgradeModule(string $moduleId, string $versionConstraint): void
    {
        // TODO implement module downgrade
        throw new Exception('Not implemented');
    }

    /**
     * @inheritDoc
     */
    public function getModuleVersions(string $moduleId): array
    {
        $subsystemVersionsMap = [];
        foreach ($this->subsystems as $subsystem) {
            $subsystemModuleVersions = $subsystem->getModuleVersions($moduleId);
            $subsystemVersion = $subsystem->getVersion();
            $availableVersions = [];
            foreach ($subsystemModuleVersions as $subsystemModuleVersion) {
                if (VersionHelper::checkVersion($subsystemVersion, $subsystemModuleVersion->systemVersion)) {
                    $availableVersions[] = $subsystemModuleVersion;
                }
            }
            $subsystemVersionsMap[$subsystem->getSubsystemId()] = $availableVersions;
        }

        return $subsystemVersionsMap;
    }

    /**
     * @inheritDoc
     */
    public function getInstalledModules(): array
    {
        $modules = [];
        foreach ($this->subsystems as $subsystem) {
            $subsystemModules = $subsystem->getInstalledModules();
            $modules = $this->collectSubsystemModulesToList($subsystemModules, $modules);
        }

        return $modules;
    }

    /**
     * Adds subsystem modules to the list of system modules.
     * Either creates or appends subsystem module to appropriate system module.
     *
     * @param array $subsystemModules List of subsystem modules
     * @param array $systemModules Current list of system modules
     * @return array
     */
    protected function collectSubsystemModulesToList(array $subsystemModules, array $systemModules): array
    {
        /** @var GenericModuleInfo $subsystemModule */
        foreach ($subsystemModules as $subsystemModule) {
            $moduleId = $subsystemModule->moduleId;
            if (empty($systemModules[$moduleId])) {
                $systemModule = new SystemModule();
                $systemModule->moduleId = $subsystemModule->moduleId;
                $systemModule->vendor = $subsystemModule->vendor;
                $systemModule->name = $subsystemModule->name;
                $systemModule->title = $subsystemModule->title;
                $systemModule->description = $subsystemModule->description;

                $systemModules[$moduleId] = $systemModule;
            }
            $systemModules[$moduleId]->subsystemModules[] = $subsystemModule;
        }

        return $systemModules;
    }

    /**
     * Collects which versions of the module need to be installed for which subsystem.
     *
     * @param string $moduleId Module ID to check
     * @param array $versionConstraintsMap Subsystem version constraints map
     * @return SubsystemModuleVersion[]
     * @throws SubsystemModuleVersionNotFoundException
     * @throws SubsystemNonResponsiveException
     */
    protected function determineVersionsForInstallation(string $moduleId, array $versionConstraintsMap): array
    {
        $subsystemModuleVersionsToInstall = [];
        foreach ($this->subsystems as $subsystem) {
            $subsystemID = $subsystem->getSubsystemId();
            if (!isset($versionConstraintsMap[$subsystemID])) {
                continue;
            }

            $versionConstraint = $versionConstraintsMap[$subsystemID];

            /** @var SubsystemModuleVersion[] $moduleVersions */
            $moduleVersions = $subsystem->getModuleVersions($moduleId);

            ArrayHelper::multisort($moduleVersions, 'version', SORT_DESC);

            $matchedVersion = null;
            foreach ($moduleVersions as $moduleVersion) {
                if (VersionHelper::checkVersion($moduleVersion->version, $versionConstraint)) {
                    $matchedVersion = $moduleVersion;
                    break;
                }
            }

            if (!$matchedVersion) {
                throw new SubsystemModuleVersionNotFoundException($moduleId, $subsystemID);
            }
            $subsystemModuleVersionsToInstall[$subsystemID] = $matchedVersion;
        }

        return $subsystemModuleVersionsToInstall;
    }

    /**
     * Installs module versions in respective subsystems.
     *
     * @param SubsystemModuleVersion[] $moduleVersions Module versions to install.
     * Keys must contain subsystem ID, values contain specific module version instance to use.
     * @return void
     * @throws SubsystemNonResponsiveException
     */
    protected function installModuleVersions(array $moduleVersions): void
    {
        foreach ($moduleVersions as $subsystemID => $moduleVersion) {
            $this->subsystems[$subsystemID]->installModule($moduleVersion->moduleId, $moduleVersion->version);
        }
    }
}
