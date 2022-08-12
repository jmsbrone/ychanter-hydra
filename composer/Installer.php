<?php

namespace app\composer;

/**
 * Extending installer to create required configs.
 */
class Installer extends \yii\composer\Installer
{
    public static function postInstall($event)
    {
        parent::postInstall($event);

        $subsystemsConfigPath = join(DIRECTORY_SEPARATOR, [
            dirname(__DIR__), 'config', 'subsystems.php'
        ]);

        file_put_contents($subsystemsConfigPath, "<?php return [];");
    }
}
