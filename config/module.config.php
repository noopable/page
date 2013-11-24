<?php
return array(
    //@see \Page\ProvidesConfigId
    'page' => array(
        'blocks' => array(),
        'config_resolver' => array(
            'path_stack' => array(
                // この行はサンプルモジュールへ移動する。
                'sample' => __DIR__ . '/pages',
            ),
            'map' => array(
                //'Page\Pages\Sample'       => __DIR__ . '/pages/sample.php',
                //'error/404'               => __DIR__ . '/../view/error/404.phtml',
                //'error/index'             => __DIR__ . '/../view/error/index.phtml',
            ),
        ),
        
        'config_loader' => 'Page\Config\Loader\File',
        'service_name' => 'Page_Service',
        'config_loader_name' => 'Page_ConfigLoader',
        'config_resolver_name' => 'Page_ConfigResolver',
        'blocks_service_name' => 'Page_BlockPluginManager',
    ),
    'service_manager' => array(
        'factories' => array(
            'Page_Service' => 'Page\Service\ServiceFactory',
            'Page_ConfigLoader' => 'Page\Service\ConfigLoaderFactory',
            'Page_ConfigResolver' => 'Page\Service\ConfigFileResolverFactory',
            'Page_BlockPluginManager' => 'Page\Service\BlockPluginManagerFactory',
        ),
    ),
    'router' => array(
    // The following section is new and should be added to your file
        'routes' => array(

        ),
    ),
);
