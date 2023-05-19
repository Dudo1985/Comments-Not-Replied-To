<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb47df6418ffb3e4bcea30b022add2400
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Dudo1985\\CNRT\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Dudo1985\\CNRT\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
            1 => __DIR__ . '/../..' . '/admin',
            2 => __DIR__ . '/../..' . '/cnrt_pro',
            3 => __DIR__ . '/../..' . '/cnrt_pro/admin',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb47df6418ffb3e4bcea30b022add2400::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb47df6418ffb3e4bcea30b022add2400::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb47df6418ffb3e4bcea30b022add2400::$classMap;

        }, null, ClassLoader::class);
    }
}
