<?php

use app\common\models\SystemModule;
use app\core\services\HubControlService;
use app\infrastructure\SubsystemManager;
use Codeception\Test\Unit;
use yii\helpers\ArrayHelper;

/**
 * This is an integration test for subsystems.
 * This test allows to check subsystems behavior and compliance with Hydra requirements.
 *
 * Inside the test configuration file subsystems must be specified in the same manner
 * as for the main config (refer to the description in the example).
 * Application parameter 'testModules' must be set and specify which modules the test will run on.
 *
 * The test perform a complete cycle of working with modules:
 * - gets a list of modules
 * - finds specified module
 * - retrieves its versions for all subsystems
 * - installs the module with picked versions (first available version is picked for simplicity)
 * - get a list of installed modules
 * - activates the module
 * - deactivates the module
 * - uninstalls the module
 *
 * Passing this test means the tested subsystem can work with Hydra without issues.
 *
 * Note:
 * The test does not guarantee functionality of the installed modules and does not check dependencies
 * between versions that will be installed as it is not Hydra's responsibility due to choice of individual versions
 * coming from the user. The test simply ensures that subsystems implement API appropriately and behave as expected.
 */
class SubsystemsTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    public function testWorkflow()
    {
        // Creating the service
        // Will trigger constructor code for subsystem manager and attempt to connect to test subsystem
        Yii::$app->cache->flush();
        $subsystemsConfig = (include __DIR__ . '/../../config/subsystems-test.php') ?? [];
        $subsystems = array_map(function ($subsystemConfig) {
            return new SubsystemManager($subsystemConfig);
        }, $subsystemsConfig);

        $service = new HubControlService($subsystems);

        /**
         * Step 1: Retrieve module list.
         * Checks that list is not empty and returned array contains elements
         * of appropriate class.
         */
        codecept_debug('Step 1: Getting module list');
        $modules = $service->getModules();
        $this->assertNotEmpty($modules);
        $this->assertInstanceOf(SystemModule::class, current($modules));
        $moduleIds = array_keys($modules);

        $testModuleIds = Yii::$app->params['testModules'];
        foreach ($testModuleIds as $testModuleId) {
            codecept_debug('Checking test module: ' . $testModuleId);
            /**
             * Step 2: Check that test module exists for installation.
             */
            $this->assertContains($testModuleId, $moduleIds);

            /**
             * Step 3: Get versions for the module and pick the first one to install.
             * Checks that version list is not empty and that there is a version available for each subsystem.
             */
            codecept_debug('Getting version for the module');
            $testModule = $modules[$testModuleId];
            $testModuleVersions = $service->getModuleVersions($testModuleId);
            $this->assertNotEmpty($testModuleVersions);
            $subsystemIds = ArrayHelper::getColumn($testModule->subsystemModules, 'subsystemId');
            $subsystemVersionsToInstall = [];
            foreach ($subsystemIds as $subsystemId) {
                $this->assertContains($subsystemId, array_keys($testModuleVersions));
                $subsystemVersionsToInstall[$subsystemId] = $testModuleVersions[$subsystemId][0]->version;
            }

            /**
             * Step 4: Install collected versions in all subsystems.
             * Checks that installed module is within installed module list after installation, that
             * it was installed at request version for each subsystem and that it is inactive after installation.
             */
            codecept_debug('Attempting to install the module with versions: ');
            foreach ($subsystemVersionsToInstall as $subsystemId => $versionToInstall) {
                codecept_debug("> $subsystemId - v$versionToInstall");
            }
            $service->installModule($testModuleId, $subsystemVersionsToInstall);
            $installedModules = $service->getInstalledModules();
            $this->assertContains($testModuleId, array_keys($installedModules));
            $installedSubsystemModulesById = ArrayHelper::index($installedModules[$testModuleId]->subsystemModules, 'subsystemId');
            foreach ($subsystemIds as $subsystemId) {
                $this->assertContains($subsystemId, array_keys($installedSubsystemModulesById));
                $this->assertEquals($subsystemVersionsToInstall[$subsystemId], $installedSubsystemModulesById[$subsystemId]->version);
                $this->assertFalse($installedSubsystemModulesById[$subsystemId]->active);
            }

            /**
             * Step 5: Activates the module.
             * Checks that module is now active within the information about installed modules.
             */
            codecept_debug("Activating module");
            $service->activateModule($testModuleId);
            $installedModules = $service->getInstalledModules();
            $installedSubsystemModulesById = ArrayHelper::index($installedModules[$testModuleId]->subsystemModules, 'subsystemId');
            foreach ($subsystemIds as $subsystemId) {
                $this->assertTrue($installedSubsystemModulesById[$subsystemId]->active);
            }

            /**
             * Step 6: Deactivates the module.
             * Checks that module is now inactive within the information about installed modules.
             */
            codecept_debug("Deactivating module");
            $service->deactivateModule($testModuleId);
            $installedModules = $service->getInstalledModules();
            $installedSubsystemModulesById = ArrayHelper::index($installedModules[$testModuleId]->subsystemModules, 'subsystemId');
            foreach ($subsystemIds as $subsystemId) {
                $this->assertFalse($installedSubsystemModulesById[$subsystemId]->active);
            }

            /**
             * Step 7: Uninstalls the module.
             * Checks that module is now missing from the list of installed modules.
             */
            $service->uninstallModule($testModuleId);
            $installedModules = $service->getInstalledModules();
            $this->assertNotContains($testModuleId, array_keys($installedModules));
        }
    }

    protected function _before()
    {

    }

    // tests

    protected function _after()
    {
    }
}
