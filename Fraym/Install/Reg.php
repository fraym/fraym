<?php

return array(
    'name' => 'Fraym Core',
    'version' => '0.9.1',
    'author' => 'Fraym.org',
    'website' => 'http://www.fraym.org',
    'updateEntity' => array(
        '\Fraym\SiteManager\Entity\Extension' => array(
            array(
                'class' => '\Fraym\Menu\MenuController',
                'method' => 'getContent',
            ),
            array(
                'class' => '\Fraym\EntityManager\EntityManagerController',
                'method' => 'getContent',
            ),
            array(
                'class' => '\Fraym\FileManager\FileManagerController',
                'method' => 'getContent',
            ),
            array(
                'name' => 'Package Manager',
                'class' => '\Fraym\Registry\RegistryManagerController',
            ),
        ),
    ),
    'entity' => array(
        '\Fraym\SiteManager\Entity\Extension' => array(
            array(
                'name' => 'Menu editor',
                'class' => '\Fraym\Menu\MenuController',
                'method' => 'getContent',
                'active' => '1',
                'description' => 'EXT_EXTENSION_SITEMENUEDITOR_DESC',
                'iconCssClass' => 'fa fa-sitemap'
            ),
            array(
                'name' => 'Data Manager',
                'class' => '\Fraym\EntityManager\EntityManagerController',
                'method' => 'getContent',
                'active' => '1',
                'description' => 'EXT_EXTENSION_ENTITYMANAGER_DESC',
                'iconCssClass' => 'fa fa-briefcase'
            ),
            array(
                'name' => 'File Manager',
                'class' => '\Fraym\FileManager\FileManagerController',
                'method' => 'getContent',
                'active' => '1',
                'description' => 'EXT_EXTENSION_FILEMANAGER_DESC',
                'iconCssClass' => 'fa fa-hdd-o'
            ),
            array(
                'name' => 'Package Manager',
                'class' => '\Fraym\Registry\RegistryManagerController',
                'method' => 'getContent',
                'active' => '1',
                'description' => 'EXT_EXTENSION_PACKAGEMANAGER_DESC',
                'iconCssClass' => 'fa fa-archive'
            ),
        ),
        '\Fraym\Route\Entity\VirtualRoute' => array(
            array(
                'key' => 'menuControllerAjax',
                'route' => '/fraym/admin/menu/ajax',
                'class' => '\Fraym\Menu\MenuController',
                'method' => 'ajaxHandler'
            ),
            array(
                'key' => 'block',
                'route' => '/fraym/admin/block',
                'class' => '\Fraym\Block\BlockController',
                'method' => 'renderBlock'
            ),
            array(
                'key' => 'adminPanel',
                'route' => '/fraym/admin/adminpanel',
                'class' => '\Fraym\SiteManager\SiteManagerController',
                'method' => 'getAdminPanel'
            ),
            array(
                'key' => 'fileViewer',
                'route' => '/fraym/admin/fileViewer',
                'class' => '\Fraym\FileManager\FileManagerController',
                'method' => 'fileViewer'
            ),
            array(
                'key' => 'fileManager',
                'route' => '/fraym/admin/filemanager',
                'class' => '\Fraym\FileManager\FileManagerController',
                'method' => 'getContent'
            ),
            array(
                'key' => 'menuSelection',
                'route' => '/fraym/admin/menu/selection',
                'class' => '\Fraym\Menu\MenuController',
                'method' => 'getContent'
            ),
            array(
                'key' => 'adminLogin',
                'route' => '/fraym',
                'class' => '\Fraym\User\UserController',
                'method' => 'renderAdminPage'
            ),
            array(
                'key' => 'registryManagerDownload',
                'route' => '/fraym/registry/download',
                'class' => '\Fraym\Registry\RegistryManagerController',
                'method' => 'downloadPackage'
            ),
        ),
        '\Fraym\EntityManager\Entity\Entity' => array(
            array(
                'className' => '\Fraym\User\Entity\User',
                'name' => 'User entry',
                'group' => array(
                    '\Fraym\EntityManager\Entity\Group' => array(
                        'name' => 'User'
                    )
                ),
            ),
            array(
                'className' => '\Fraym\User\Entity\Group',
                'name' => 'Usergroup entry',
                'group' => array(
                    '\Fraym\EntityManager\Entity\Group' => array(
                        'name' => 'User'
                    )
                ),
            ),
            array(
                'className' => '\Fraym\Template\Entity\Template',
                'name' => 'Menu template entry',
                'group' => array(
                    '\Fraym\EntityManager\Entity\Group' => array(
                        'name' => 'Template'
                    )
                ),
            ),
            array(
                'className' => '\Fraym\Block\Entity\BlockTemplate',
                'name' => 'Block template entry',
                'group' => array(
                    '\Fraym\EntityManager\Entity\Group' => array(
                        'name' => 'Template'
                    )
                ),
            ),
            array(
                'className' => '\Fraym\Site\Entity\Site',
                'name' => 'Website entry',
                'group' => array(
                    '\Fraym\EntityManager\Entity\Group' => array(
                        'name' => 'Website'
                    )
                ),
            ),
            array(
                'className' => '\Fraym\Site\Entity\Domain',
                'name' => 'Domain entry',
                'group' => array(
                    '\Fraym\EntityManager\Entity\Group' => array(
                        'name' => 'Website'
                    )
                ),
            ),
            array(
                'className' => '\Fraym\Translation\Entity\Translation',
                'name' => 'Translation entry',
                'group' => array(
                    '\Fraym\EntityManager\Entity\Group' => array(
                        'name' => 'Translation'
                    )
                ),
            ),
            array(
                'className' => '\Fraym\Registry\Entity\Config',
                'name' => 'Config entry',
                'group' => array(
                    '\Fraym\EntityManager\Entity\Group' => array(
                        'name' => 'Config'
                    )
                ),
            ),
            array(
                'className' => '\Fraym\Locale\Entity\Locale',
                'name' => 'Locale',
                'group' => array(
                    '\Fraym\EntityManager\Entity\Group' => array(
                        'name' => 'Website'
                    )
                ),
            )
        ),
        '\Fraym\Block\Entity\BlockExtension' => array(
            array(
                'name' => 'Menu',
                'description' => 'Add a website menu to your site.',
                'class' => '\Fraym\Menu\MenuController',
                'configMethod' => 'getBlockConfig',
                'execMethod' => 'execBlock',
                'saveMethod' => 'saveBlockConfig'
            ),
            array(
                'name' => 'Container',
                'description' => 'Add a static content to your website.',
                'class' => '\Fraym\Block\Block',
                'execMethod' => 'execBlock'
            ),
            array(
                'name' => 'User',
                'description' => 'Adds a LogIn / LogOut form to your website.',
                'class' => '\Fraym\User\User',
                'configMethod' => 'getBlockConfig',
                'execMethod' => 'execBlock',
                'saveMethod' => 'saveBlockConfig'
            ),
            array(
                'name' => 'Image',
                'description' => 'Add a image to your website.',
                'class' => '\Fraym\Image\Image',
                'configMethod' => 'getBlockConfig',
                'execMethod' => 'execBlock',
                'saveMethod' => 'saveBlockConfig'
            ),
        )
    ),
    'config' => array(
        'ADMIN_GROUP_IDENTIFIER' => array(
            'value' => 'GROUP:Administrator',
            'description' => 'The identifier for the Administrator group.',
            'deletable' => false
        ),
        'ADMIN_LOCALE_ID' => array(
            'value' => '1',
            'description' => 'The default administrator locale.',
            'deletable' => false
        ),
        'TRANSLATION_AUTO' => array(
            'value' => '1',
            'description' => 'Set to 1 for enable auto translation or to 0 to disable auto translation.',
            'deletable' => false
        ),
        'IMAGE_PATH' => array(
            'value' => 'images/deposit',
            'description' => 'The save path of the converted images.',
            'deletable' => false
        ),
        'FILEMANAGER_STORAGES' => array(
            'value' => 'Template,Public/images',
            'description' => 'The folders that are mapped in the file manager. You can seperate multiple folder with comma.',
            'deletable' => false
        ),
    ),
    'files' => array(
        'Fraym/*',
        'Fraym/',
        'Template/Default/Fraym/*',
        'Template/Default/Fraym/',
        'Test/Fraym/*',
        'Test/Fraym/',
        'Public/images/fraym/*',
        'Public/images/fraym/',
        'Public/css/fraym/*',
        'Public/css/fraym/',
        'Public/fonts/arial.ttf',
        'Public/css/install/*',
        'Public/css/install/',
        'Public/js/fraym/*',
        'Public/js/fraym/',
        'Public/index.php',
        'Public/install.php',
        'Bootstrap.php',
        'phpunit.xml',
        'CHANGELOG.txt',
        'COPYRIGHT.txt',
        'LICENSE.txt',
        'README.txt',
        'Vendor/Detection/*',
        'Vendor/Detection/',
        'Vendor/DI/*',
        'Vendor/DI/',
        'Vendor/Interop/*',
        'Vendor/Interop/',
        'Vendor/Doctrine/*',
        'Vendor/Doctrine/',
        'Vendor/DoctrineExtensions/*',
        'Vendor/DoctrineExtensions/',
        'Vendor/Elasticsearch/*',
        'Vendor/Elasticsearch/',
        'Vendor/Gedmo/*',
        'Vendor/Gedmo/',
        'Vendor/Guzzle/*',
        'Vendor/Guzzle/',
        'Vendor/Imagine/*',
        'Vendor/Imagine/',
        'Vendor/Monolog/*',
        'Vendor/Monolog/',
        'Vendor/MyCLabs/*',
        'Vendor/MyCLabs/',
        'Vendor/PhpDocReader/*',
        'Vendor/PhpDocReader/',
        'Vendor/PHPUnit/*',
        'Vendor/PHPUnit/',
        'Vendor/ProxyManager/*',
        'Vendor/ProxyManager/',
        'Vendor/Psr/*',
        'Vendor/Psr/',
        'Vendor/Swift/*',
        'Vendor/Swift/',
        'Vendor/Swift/*',
        'Vendor/Swift/',
        'Vendor/Symfony/*',
        'Vendor/Symfony/',
        'Vendor/Zend/*',
        'Vendor/Zend/',
        'Vendor/Pimple.php',
    ),
    'deletable' => false,
    'repositoryKey' => 'FRAYM',
);