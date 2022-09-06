<?php

namespace app\infrastructure;

use app\common\helpers\VersionHelper;
use app\common\models\SubsystemModule;
use app\common\models\SubsystemModuleVersion;
use app\core\spi\LoggerSPI;
use app\core\spi\SubsystemManagerAPI;
use app\infrastructure\requests\CurlRequest;
use app\infrastructure\requests\subsystem\ModuleActivateRequest;
use app\infrastructure\requests\subsystem\ModuleDeactivateRequest;
use app\infrastructure\requests\subsystem\ModuleInstallRequest;
use app\infrastructure\requests\subsystem\ModuleListRequest;
use app\infrastructure\requests\subsystem\ModuleUninstallRequest;
use app\infrastructure\requests\subsystem\ModuleVersionsRequest;
use Exception;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;

class SubsystemManager extends BaseObject implements SubsystemManagerAPI
{
    /** @var string Subsystem base URL for making API calls */
    public string $subsystemUrl;
    /** @var string Login for the subsystem */
    public string $login;
    /** @var string Password for the subsystem */
    public string $password;
    /** @var string Subsystem ID */
    protected string $subsystemId;
    /** @var string Current subsystem version */
    protected string $version;
    /** @var string Access token for requests to the subsystem */
    protected string $accessToken;

    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function __construct($config)
    {
        parent::__construct($config);

        $logger = Yii::$container->get(LoggerSPI::class);
        $logger->info('Attaching to subsystem at ' . $this->subsystemUrl);

        $cachedTokenKey = 'token_' . $this->subsystemUrl;
        if (Yii::$app->cache->exists($cachedTokenKey)) {
            $this->accessToken = Yii::$app->cache->get($cachedTokenKey);
            $logger->info('Access token retrieved from cache');
        } else {
            $logger->info('Attempting to login');
            $tokenRequest = new CurlRequest($this->subsystemUrl . '/hydra/login');
            $tokenRequest->usePost();
            $tokenRequest->setHeader('Content-Type', 'application/json');
            $tokenRequest->setData([
                'login' => $this->login,
                'password' => $this->password,
            ]);
            $result = $tokenRequest->execute();
            $this->accessToken = json_decode($result);
            $logger->info('Logged in successfully. Access token saved to cache.');
            Yii::$app->cache->set($cachedTokenKey, $this->accessToken);
        }
        $logger->info('Retrieving subsystem information');

        $request = new CurlRequest($this->subsystemUrl . '/hydra/info');
        $request->setHeader('Authorization', 'Bearer ' . $this->accessToken);
        $info = json_decode($request->execute(), true);

        $this->subsystemId = $info['subsystemId'];
        $this->version = $info['version'];
        $apiVersionDependency = $info['api_version'];

        if (!VersionHelper::checkVersion(Yii::$app->params['version'], $apiVersionDependency)) {
            $logger->info("Subsystem not acknowledged: $this->subsystemId(v$this->version)");
            throw new Exception("Subsystem '$this->subsystemId' does not support Hydra API with current version." .
                " Subsystem API dependency: $apiVersionDependency");
        }

        $logger->info("Acknowledged subsystem: $this->subsystemId(v$this->version)");
    }

    /**
     * @inheritDoc
     */
    public function getAvailableModules(): array
    {
        $request = new CurlRequest($this->subsystemUrl . '/hydra/modules');
        $request->setHeader('Authorization', 'Bearer ' . $this->accessToken);

        $modulesData = json_decode($request->execute(), true);

        $modules = [];
        foreach ($modulesData as $data) {
            $data['subsystemId'] = $this->subsystemId;
            $modules[] = new SubsystemModule($data);
        }

        return $modules;
    }

    /**
     * @inheritDoc
     */
    public function getModuleVersions(string $moduleId): array
    {
        $request = new ModuleVersionsRequest($moduleId, $this->subsystemUrl);
        $request->setBearerToken($this->accessToken);
        $resultDtos = $request->execute();

        $versionEntities = [];
        foreach ($resultDtos as $dto) {
            $versionEntities[] = new SubsystemModuleVersion($dto);
        }

        return $versionEntities;
    }

    /**
     * @inheritDoc
     */
    public function getInstalledModules(): array
    {
        $request = new ModuleListRequest($this->subsystemUrl);
        $request->setBearerToken($this->accessToken);
        $request->filterByInstalled();

        $modulesData = $request->execute();

        $modules = [];
        foreach ($modulesData as $data) {
            $data['subsystemId'] = $this->subsystemId;
            $modules[] = new SubsystemModule($data);
        }

        return $modules;
    }

    /**
     * @inheritDoc
     */
    public function getSubsystemId(): string
    {
        return $this->subsystemId;
    }

    /**
     * @inheritDoc
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @inheritDoc
     */
    public function installModule(string $moduleId, string $version): void
    {
        $request = new ModuleInstallRequest($this->subsystemUrl);
        $request->setData(['moduleId' => $moduleId, 'version' => $version]);
        $request->setBearerToken($this->accessToken);
        $request->execute();
    }

    /**
     * @inheritDoc
     */
    public function uninstallModule(string $moduleId): void
    {
        $request = new ModuleUninstallRequest($this->subsystemUrl);
        $request->setData(['moduleId' => $moduleId]);
        $request->setBearerToken($this->accessToken);
        $request->execute();
    }

    /**
     * @inheritDoc
     */
    public function activateModule(string $moduleId): void
    {
        $request = new ModuleActivateRequest($this->subsystemUrl);
        $request->setData(['moduleId' => $moduleId]);
        $request->setBearerToken($this->accessToken);
        $request->execute();
    }

    /**
     * @inheritDoc
     */
    public function deactivateModule(string $moduleId): void
    {
        $request = new ModuleDeactivateRequest($this->subsystemUrl);
        $request->setData(['moduleId' => $moduleId]);
        $request->setBearerToken($this->accessToken);
        $request->execute();
    }
}
