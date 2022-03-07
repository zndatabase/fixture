<?php

use ZnCore\Base\Libs\App\Kernel;
use ZnCore\Base\Libs\App\Loaders\BundleLoader;
use ZnCore\Base\Libs\DotEnv\DotEnv;

DotEnv::init();

$kernel = new Kernel('console');
$container = $kernel->getContainer();

$bundleLoader = new BundleLoader([], ['i18next', 'container', 'console', 'migration']);
$bundleLoader->addBundles(include __DIR__ . '/bundle.php');
$kernel->setLoader($bundleLoader);

$config = $kernel->loadAppConfig();
