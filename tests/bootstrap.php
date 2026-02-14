<?php

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

$monorepoRoot = dirname(dirname(dirname(__DIR__))); // .../ksf_modules_common
set_include_path(get_include_path() . PATH_SEPARATOR . $monorepoRoot);

// FrontAccounting provides a global translation helper. Stub it for unit tests.
if (!function_exists('_')) {
    function _(string $s): string
    {
        return $s;
    }
}

// FrontAccounting provides a base hooks class. Stub it for unit tests.
if (!class_exists('hooks')) {
    class hooks
    {
    }
}
