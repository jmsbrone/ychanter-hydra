<?php

namespace app\core\api;

use app\common\models\SystemModule;
use app\core\exceptions\ModuleActivationFailureException;
use app\core\exceptions\ModuleNotInstalledException;
use app\core\exceptions\SubsystemModuleVersionNotFoundException;
use app\core\exceptions\SubsystemNonResponsiveException;

/**
 * Hub actions.
 */
interface HubAPI
{
    /**
     * Returns available system modules.
     *
     * @return SystemModule[]
     * @throws SubsystemNonResponsiveException
     */
    public function getModules(): array;

    /**
     * Returns available module versions for given module.
     * Returned array contains a map of module versions for each used subsystem.
     * Keys are subsystem IDs and values contain a list of versions for that subsystem.
     *
     * @param string $moduleId Module ID to get versions of
     * @return array
     *
     * @throws SubsystemNonResponsiveException if any subsystem does not respond
     */
    public function getModuleVersions(string $moduleId): array;

    /**
     * Returns only installed system modules.
     *
     * @return SystemModule[]
     * @throws SubsystemNonResponsiveException
     */
    public function getInstalledModules(): array;

    /**
     * Install given module with specified version constraints for each subsystem.
     *
     * @param string $moduleId Module ID to install
     * @param array $versionConstraintsMap Modules versions to use for each subsystem
     * @return void
     *
     * @throws SubsystemModuleVersionNotFoundException if suitable version of module cannot be found for a subsystem
     * @throws SubsystemNonResponsiveException if any subsystem does not respond
     */
    public function installModule(string $moduleId, array $versionConstraintsMap): void;

    /**
     * Uninstall given module completely from all subsystems.
     *
     * @param string $moduleId Module ID to uninstall
     * @return void
     *
     * @throws ModuleNotInstalledException if module is not installed
     * @throws SubsystemNonResponsiveException if any subsystem does not respond
     */
    public function uninstallModule(string $moduleId): void;

    /**
     * Upgrades given module to the latest version specified by given constraint.
     *
     * @param string $moduleId Module ID to upgrade
     * @param string $versionConstraint Optional version constraint to limit upgrade versions.
     * If not specified the latest version will be used.
     * @return void
     *
     * @throws ModuleNotInstalledException if module is not installed
     * @throws SubsystemNonResponsiveException if any subsystem does not respond
     */
    public function upgradeModule(string $moduleId, string $versionConstraint = '*'): void;

    /**
     * Downgrades given module to the latest version allowed by given constraint.
     *
     * @param string $moduleId Module ID to downgrade
     * @param string $versionConstraint Version constraint to downgrade to
     * @return void
     *
     * @throws ModuleNotInstalledException if module is not installed
     * @throws SubsystemNonResponsiveException if any subsystem does not respond
     */
    public function downgradeModule(string $moduleId, string $versionConstraint): void;

    /**
     * Activates given module in all subsystems.
     *
     * @param string $moduleId Module ID to activate
     * @return void
     *
     * @throws ModuleNotInstalledException if module is not installed
     * @throws SubsystemNonResponsiveException if any subsystem does not respond
     * @throws ModuleActivationFailureException if module activation failed for a subsystem
     */
    public function activateModule(string $moduleId): void;

    /**
     * Deactivates module from all subsystems.
     *
     * @param string $moduleId Module ID to deactivate
     * @return void
     *
     * @throws ModuleNotInstalledException if module is not installed
     * @throws SubsystemNonResponsiveException if any subsystem does not respond
     */
    public function deactivateModule(string $moduleId): void;
}
