<?php

namespace app\core\spi\prototypes;

use app\core\exceptions\ModuleActivationFailureException;
use app\core\exceptions\SubsystemNonResponsiveException;

/**
 * Generic API for any subsystem.
 * Contains all actions that can be performed with any subsystem.
 *
 * @template V of \app\common\models\SubsystemModuleVersionPrototype
 * @template M of \app\common\models\SubsystemModulePrototype
 */
interface SubsystemManagerAPI
{
    /**
     * Returns all available modules for the subsystem.
     *
     * @return M[]
     *
     * @throws SubsystemNonResponsiveException if subsystem does not respond to API request
     */
    public function getAvailableModules(): array;

    /**
     * Returns all available module version for the subsystem.
     *
     * @param string $moduleId Module ID to load versions for
     * @return V[]
     *
     * @throws SubsystemNonResponsiveException if subsystem does not respond to API request
     */
    public function getModuleVersions(string $moduleId): array;

    /**
     * Returns only installed modules list.
     *
     * @return M[]
     */
    public function getInstalledModules(): array;

    /**
     * Returns unique ID of the subsystem.
     *
     * @return string
     */
    public function getSubsystemID(): string;

    /**
     * Returns current subsystem version.
     *
     * @return string
     */
    public function getVersion(): string;

    /**
     * Installs given module version in the subsystem.
     *
     * @param string $moduleId Module ID to install
     * @param string $version Module version to install
     * @return void
     *
     * @throws SubsystemNonResponsiveException if subsystem does not respond to API request
     */
    public function installModule(string $moduleId, string $version): void;

    /**
     * Uninstalls module from the subsystem.
     *
     * @param string $moduleId Module ID to uninstall
     * @return void
     *
     * @throws SubsystemNonResponsiveException if subsystem does not respond to API request
     */
    public function uninstallModule(string $moduleId): void;

    /**
     * Activates module in the subsystem.
     *
     * @param string $moduleId Module ID to activate
     * @return void
     *
     * @throws SubsystemNonResponsiveException if subsystem does not respond to API request
     * @throws ModuleActivationFailureException if module failed to activate in the subsystem
     */
    public function activateModule(string $moduleId): void;

    /**
     * Deactivates module in the subsystem.
     *
     * @param string $moduleId Module ID to deactivate
     * @return void
     *
     * @throws SubsystemNonResponsiveException if subsystem does not respond to API request
     */
    public function deactivateModule(string $moduleId): void;
}
