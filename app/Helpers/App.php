<?php

namespace App\Helpers;

use App as AppBase;

class App
{
    public static $ENV_PRODUCTION = 'production';
    public static $ENV_TESTING = 'testing';
    public static $ENV_LOCAL = 'local';
    public static $ENV_DEV = 'dev';

    public static function runningInLocalOrTesting(): bool
    {
        return static::runningInLocal() || static::runningInTesting();
    }

    public static function runningInLocal(): bool
    {
        return AppBase::environment(static::$ENV_LOCAL);
    }

    public static function notRunningInLocal(): bool
    {
        return !static::runningInLocal();
    }

    public static function runningInProduction(): bool
    {
        return AppBase::environment(static::$ENV_PRODUCTION);
    }

    public static function notRunningInProduction(): bool
    {
        return !static::runningInProduction();
    }

    public static function runningInTesting(): bool
    {
        return AppBase::environment(static::$ENV_TESTING);
    }

    public static function notRunningInTesting(): bool
    {
        return !static::runningInTesting();
    }

    public static function runningInConsoleOrUnitTests(): bool
    {
        return static::runningInConsole() || static::runningInUnitTests();
    }

    public static function notRunningInConsoleOrUnitTests(): bool
    {
        return !static::runningInConsoleOrUnitTests();
    }

    public static function runningInConsole(): bool
    {
        return AppBase::runningInConsole();
    }

    public static function notRunningInConsole(): bool
    {
        return !static::runningInConsole();
    }

    public static function runningInUnitTests(): bool
    {
        return AppBase::runningUnitTests();
    }

    public static function notRunningInUnitTests(): bool
    {
        return !static::runningInUnitTests();
    }

    public static function getServerUserInformation(): string
    {
        return str_replace('.', '. ', php_uname() . ' | ' . get_current_user());
    }
}