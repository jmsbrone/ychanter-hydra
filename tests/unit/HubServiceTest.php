<?php

namespace tests\unit;

use app\common\models\SubsystemModule;
use app\common\models\SubsystemModuleVersion;
use app\core\api\HubAPI;
use app\core\services\HubControlService;
use app\core\spi\SubsystemManagerAPI;
use Codeception\Test\Unit;
use Exception;
use PharIo\Version\UnsupportedVersionConstraintException;
use PHPUnit\Framework\MockObject\MockObject;
use UnitTester;
use yii\helpers\ArrayHelper;

class HubServiceTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected HubAPI $service;

    public function testModuleListing()
    {
        $list = $this->service->getModules();

        $this->debugPrintSystemList($list);

        /**
         * 2 only client modules
         * 1 only server module
         * 1 module for both
         */
        $this->assertCount(4, $list);

        foreach ($list as $systemModule) {
            $this->assertNotEmpty($systemModule->moduleId);
            $this->assertNotEmpty($systemModule->subsystemModules);

            foreach ($systemModule->subsystemModules as $subsystemModule) {
                $this->assertEquals($subsystemModule->moduleId, $systemModule->moduleId);
                $this->assertNotEmpty($subsystemModule->subsystemId);
                if ($subsystemModule->installed) {
                    $this->assertNotEmpty($subsystemModule->version);
                }
            }
        }
    }

    /**
     * @param array $list
     * @return void
     */
    protected function debugPrintSystemList(array $list): void
    {
        if (empty($list)) {
            codecept_debug('>>>> list is empty <<<<<');
        }
        foreach ($list as $systemModule) {
            codecept_debug('---------------------------------------');
            codecept_debug("> System module - " . $systemModule->moduleId);

            foreach ($systemModule->subsystemModules as $subsystemModule) {
                codecept_debug('>> ' .
                    join(', ', [
                        'subsystem - ' . $subsystemModule->subsystemId,
                        'module ID - ' . $subsystemModule->moduleId,
                        $subsystemModule->installed ? ('installed (' . $subsystemModule->version . ')') : 'not installed',
                        $subsystemModule->active ? 'active' : 'not active',
                    ])
                );
            }
        }
    }

    public function testVersionListing()
    {
        $moduleId = "test.serverClientModule2";
        $moduleVersions = $this->service->getModuleVersions($moduleId);
        $this->assertNotEmpty($moduleVersions);

        codecept_debug('---------------------------------------');
        codecept_debug('Versions for module ' . $moduleId);
        foreach ($moduleVersions as $subsystemId => $subsystemModuleVersions) {
            codecept_debug('---------------------------------------');
            codecept_debug('> Subsystem - ' . $subsystemId);
            foreach ($subsystemModuleVersions as $subsystemModuleVersion) {
                codecept_debug('>> version - ' . $subsystemModuleVersion->version);
                if ($subsystemId === 'client-1') {
                    $this->assertNotEmpty($subsystemModuleVersion->subsystemDependencies);
                }
                foreach ($subsystemModuleVersion->subsystemDependencies as $dependencyId => $subsystemDependency) {
                    codecept_debug('>>> depends on: ' . $dependencyId . ', version: ' . $subsystemDependency);
                }
            }
        }
    }

    public function testActivate()
    {
        $moduleId = "test.serverClientModule2";
        $this->service->activateModule($moduleId);

        $list = $this->service->getModules();

        $this->debugPrintSystemList($list);

        foreach ($list as $systemModule) {
            if ($systemModule->moduleId == $moduleId) {
                $this->assertTrue($systemModule->isActive());
            }
        }

    }

    public function testDeactivate()
    {
        $moduleId = "test.clientModule3";
        $this->service->deactivateModule($moduleId);

        $list = $this->service->getModules();

        $this->debugPrintSystemList($list);

        foreach ($list as $systemModule) {
            if ($systemModule->moduleId == $moduleId) {
                $this->assertFalse($systemModule->isActive());
            }
        }
    }

    public function testInstall()
    {
        $this->service->installModule('test.serverModule1', ['server-1' => '~2.0']);
        $this->service->installModule('test.clientModule3', ['client-1' => '*']);

        $installedModules = $this->service->getInstalledModules();

        $this->debugPrintSystemList($installedModules);

        $installedIds = ArrayHelper::getColumn($installedModules, 'moduleId');
        $this->assertContains('test.serverModule1', $installedIds);
        $this->assertContains('test.clientModule3', $installedIds);

        $invalidVersionExceptionThrown = false;
        try {
            $this->service->installModule('test.serverModule1', ['server-1' => 'invalid-syntax']);
        } catch (UnsupportedVersionConstraintException) {
            $invalidVersionExceptionThrown = true;
        }

        $this->assertTrue($invalidVersionExceptionThrown);

        /**
         * -----------------------------
         * testing uninstall after installation because modules will definitely be installed
         * otherwise pre-installation is required in used mock subsystem
         * ----------------------------
         */

        $this->service->uninstallModule('test.serverModule1');
        $this->service->uninstallModule('test.clientModule3');

        $installedModules = $this->service->getInstalledModules();

        $this->debugPrintSystemList($installedModules);

        $installedIds = ArrayHelper::getColumn($installedModules, 'moduleId');
        $this->assertNotContains('test.serverModule1', $installedIds);
        $this->assertNotContains('test.clientModule3', $installedIds);
    }

    public function testUpgrade()
    {

    }

    public function testDowngrade()
    {

    }

    protected function _before()
    {
        $this->service = new HubControlService([
            $this->createSubsystemServer1(),
            $this->createSubsystemClient1(),
        ]);
    }

    protected function _after()
    {
    }

    /**
     * @return SubsystemManagerAPI|MockObject
     * @throws Exception
     */
    protected function createSubsystemServer1(): MockObject|SubsystemManagerAPI
    {
        $subsystemID = 'server-1';

        $module1 = $this->make(SubsystemModule::class, ['subsystemId' => $subsystemID]);
        $module1->moduleId = 'test.serverModule1';
        $module1->name = "serverModule1";
        $module1->vendor = "test";
        $module1->title = "Server module #1";
        $module1->description = "Test module #1, only meant for server service";

        $module2 = $this->make(SubsystemModule::class, ['subsystemId' => $subsystemID]);
        $module2->moduleId = 'test.serverClientModule2';
        $module2->name = "serverClientModule2";
        $module2->vendor = "test";
        $module2->title = "Server module #2";
        $module2->description = "Test module #2 (server and client) - server module";

        $modules = [$module1, $module2];

        $versionsMap = [
            'test.serverModule1' => [
                [
                    'systemVersion' => '*',
                    'version' => '1.0.0'
                ],
                [
                    'systemVersion' => '*',
                    'version' => '2.1.0'
                ],
            ],
            'test.serverClientModule2' => [
                [
                    'systemVersion' => '*',
                    'version' => '1.1.0'
                ],
                [
                    'systemVersion' => '~1.1.0',
                    'version' => '1.4.0'
                ],
                [
                    'systemVersion' => '~1.1.0',
                    'version' => '1.4.1'
                ],
                [
                    'systemVersion' => '~1.1.0',
                    'version' => '1.4.2'
                ],
                [
                    'systemVersion' => '^1.5',
                    'version' => '2.0.0'
                ],
                [
                    'systemVersion' => '^1.5',
                    'version' => '2.0.2'
                ]
            ]
        ];

        return $this->makeEmpty(SubsystemManagerAPI::class, $this->createMockManager($subsystemID, $modules, $versionsMap));
    }

    /**
     * @return SubsystemManagerAPI|MockObject
     * @throws Exception
     */
    protected function createSubsystemClient1(): MockObject|SubsystemManagerAPI
    {
        $subsystemID = 'client-1';

        $module1 = $this->make(SubsystemModule::class, ['subsystemId' => $subsystemID]);
        $module1->moduleId = 'test.clientModule1';
        $module1->name = "clientModule1";
        $module1->vendor = "test";
        $module1->title = "Client module #1";
        $module1->description = "Test module #1, only meant for client service";

        $module2 = $this->make(SubsystemModule::class, ['subsystemId' => $subsystemID]);
        $module2->moduleId = 'test.serverClientModule2';
        $module2->name = "serverClientModule2";
        $module2->vendor = "test";
        $module2->title = "Client module #2";
        $module2->description = "Test module #2 (server and client) - client module";

        $module3 = $this->make(SubsystemModule::class, ['subsystemId' => $subsystemID]);
        $module3->moduleId = 'test.clientModule3';
        $module3->name = "clientModule3";
        $module3->vendor = "test";
        $module3->title = "Client module #3";
        $module3->description = "Test module #3, pre-installed and active";
        $module3->active = true;
        $module3->installed = true;
        $module3->version = '1.2.9';

        $modules = [$module1, $module2, $module3];

        $versionsMap = [
            'test.clientModule3' => [
                [
                    'systemVersion' => '*',
                    'version' => '0.2.0'
                ],
                [
                    'systemVersion' => '*',
                    'version' => '1.0.0'
                ],
            ],
            'test.serverClientModule2' => [
                [
                    'systemVersion' => '*',
                    'version' => '1.0.0',
                    'subsystemDependencies' => [
                        'server-1' => '~1.0.0'
                    ]
                ],
                [
                    'systemVersion' => '~1.1.0',
                    'version' => '1.4.0',
                    'subsystemDependencies' => [
                        'server-1' => '~1.4.0'
                    ]
                ],
                [
                    'systemVersion' => '^1.5',
                    'version' => '2.0.0',
                    'subsystemDependencies' => [
                        'server-1' => '~1.5.0'
                    ]
                ],
                [
                    'systemVersion' => '^1.9',
                    'version' => '2.0.1',
                    'subsystemDependencies' => [
                        'server-1' => '~2.0.0'
                    ]
                ]
            ]
        ];

        return $this->makeEmpty(SubsystemManagerAPI::class, $this->createMockManager($subsystemID, $modules, $versionsMap));
    }

    /**
     * @param string $subsystemId
     * @param array $modules
     * @param array $versionsMap
     * @return array
     */
    protected function createMockManager(string $subsystemId, array $modules, array $versionsMap): array
    {
        return [
            'getSubsystemID' => $subsystemId,
            'getAvailableModules' => $modules,
            'getModuleVersions' => function ($moduleId) use ($subsystemId, $versionsMap) {
                if (!isset($versionsMap[$moduleId])) {
                    return [];
                }

                $versions = [];
                foreach ($versionsMap[$moduleId] as $versionInfo) {
                    $version = $this->make(SubsystemModuleVersion::class, [
                        'moduleId' => $moduleId,
                    ]);
                    foreach ($versionInfo as $attr => $value) {
                        $version->$attr = $value;
                    }
                    $versions[] = $version;
                }

                return $versions;
            },
            'activateModule' => function ($moduleId) use ($modules) {
                foreach ($modules as $module) {
                    if ($module->moduleId == $moduleId) {
                        $module->active = true;
                    }
                }
            },
            'deactivateModule' => function ($moduleId) use ($modules) {
                foreach ($modules as $module) {
                    if ($module->moduleId == $moduleId) {
                        $module->active = false;
                    }
                }
            },
            'getInstalledModules' => function () use ($modules) {
                $result = [];
                foreach ($modules as $module) {
                    if ($module->installed) {
                        $result[] = $module;
                    }
                }

                return $result;
            },
            'installModule' => function (string $moduleId, string $versionConstraint = '*') use ($modules) {
                foreach ($modules as $module) {
                    if ($module->moduleId == $moduleId) {
                        $module->installed = true;
                        $module->version = $versionConstraint;
                    }
                }
            },
            'uninstallModule' => function (string $moduleId) use ($modules) {
                foreach ($modules as $module) {
                    if ($module->moduleId == $moduleId) {
                        $module->installed = false;
                    }
                }
            },
            'getVersion' => '1.9.2',
        ];
    }
}
