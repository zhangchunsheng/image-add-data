<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4c4d747e6d7a32f8a8bf32a033f75349
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'Ajaxray\\PHPWatermark\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Ajaxray\\PHPWatermark\\' => 
        array (
            0 => __DIR__ . '/..' . '/ajaxray/php-watermark/src/Ajaxray/PHPWatermark',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit4c4d747e6d7a32f8a8bf32a033f75349::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit4c4d747e6d7a32f8a8bf32a033f75349::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
