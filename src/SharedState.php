<?php

namespace Dbu\SharedState;

use Symfony\Component\Filesystem\Filesystem;

class SharedState
{
    public static function setVars(array $vars): void
    {
        // TODO env variable name?
        $name = getenv('BACKGROUND_NAME');
        if (!is_string($name)) {
            throw new \RuntimeException('BACKGROUND_NAME must be set to define the name of this background worker');
        }

        // TODO validate that $vars only contains primitive types

        $fs = new Filesystem();
        // this is an atomic operation, first creating a temporary file and then renaming it
        $fs->dumpFile(self::filename($name), serialize($vars));
    }

    public static function getVars(string|array $name, float $timeout = 30.0): array
    {
        $timeout*= 1000000; //convert to microseconds
        if (is_string($name)) {
            return self::getVarsSingle($name, $timeout);
        }

        $start = microtime(true);
        $vars = [];
        foreach ($name as $singleName) {
            $vars[$singleName] = self::getVarsSingle($singleName, $timeout);
            $timeout -= microtime(true) - $start;
        }

        return $vars;
    }

    private static function getVarsSingle(string $name, float $timeout): array
    {
        $fs = new Filesystem();
        $filename = self::filename($name);
        while ($timeout > 0 && !$fs->exists($filename)) {
            usleep(1000);
            $timeout -= 1000;
        }
        if (!$fs->exists($filename)) {
            throw new \RuntimeException("Timeout waiting for background worker '$name'");
        }

        return unserialize($fs->readFile($filename));
    }

    private static function filename(string $backgroundName): string
    {
        // TODO env variable name?
        $backgroundScope = getenv('BACKGROUND_SCOPE') ?: '_default';

        // TODO directory configurable? how to find the symfony var directory?

        return "/tmp/php-shared-state/$backgroundScope/$backgroundName.bin";
    }
}
