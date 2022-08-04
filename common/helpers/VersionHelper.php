<?php

namespace app\common\helpers;

use PharIo\Version\Version;
use PharIo\Version\VersionConstraintParser;

/**
 * Helper for operations with module versions.
 */
class VersionHelper
{
    /**
     * Checks that given version matches given constraint.
     *
     * @param string $version Version to check
     * @param string $constraint Version constraint to check against
     * @return bool
     */
    public static function checkVersion(string $version, string $constraint): bool
    {
        $versionParser = new VersionConstraintParser();
        $constraintChecker = $versionParser->parse($constraint);

        return $constraintChecker->complies(new Version($version));
    }
}
