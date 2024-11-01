<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite7b74c3fa55be084c708d8ad748fd72c
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'Gratien\\Urubutopay\\' => 19,
        ),
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Gratien\\Urubutopay\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite7b74c3fa55be084c708d8ad748fd72c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite7b74c3fa55be084c708d8ad748fd72c::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite7b74c3fa55be084c708d8ad748fd72c::$classMap;

        }, null, ClassLoader::class);
    }
}
