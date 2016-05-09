<?php

return array(
    'name' => 'Fraym Core',
    'version' => '1.0.0',
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
                'name' => 'Extension Manager',
                'class' => '\Fraym\Registry\RegistryManagerController',
            ),
        ),
    ),
    'entity' => array(
        '\Fraym\SiteManager\Entity\Extension' => array(
            array(
                'name' => 'Menu Editor',
                'class' => '\Fraym\Menu\MenuController',
                'method' => 'getContent',
                'active' => '1',
                'description' => 'EXT_SITEMENUEDITOR_DESC',
                'iconCssClass' => 'fa fa-sitemap',
                'sorter' => 0
            ),
            array(
                'name' => 'Data Manager',
                'class' => '\Fraym\EntityManager\EntityManagerController',
                'method' => 'getContent',
                'active' => '1',
                'description' => 'EXT_ENTITYMANAGER_DESC',
                'iconCssClass' => 'fa fa-briefcase',
                'sorter' => 10
            ),
            array(
                'name' => 'File Manager',
                'class' => '\Fraym\FileManager\FileManagerController',
                'method' => 'getContent',
                'active' => '1',
                'description' => 'EXT_FILEMANAGER_DESC',
                'iconCssClass' => 'fa fa-hdd-o',
                'sorter' => 20
            ),
            array(
                'name' => 'Extension Manager',
                'class' => '\Fraym\Registry\RegistryManagerController',
                'method' => 'getContent',
                'active' => '1',
                'description' => 'EXT_EXTENSIONMANAGER_DESC',
                'iconCssClass' => 'fa fa-archive',
                'sorter' => 30
            ),
            array(
                'name' => 'Change Set Manager',
                'class' => '\Fraym\Block\BlockChangeSetManagerController',
                'method' => 'getContent',
                'active' => '1',
                'description' => 'EXT_CHANGE_SET_MANAGER_DESC',
                'iconCssClass' => 'fa fa-tasks',
                'sorter' => 40
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
                'className' => '\Fraym\Block\Entity\Template',
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
        '\Fraym\Block\Entity\Extension' => array(
            array(
                'name' => 'Menu',
                'description' => 'Add a website menu to your site.',
                'class' => '\Fraym\Menu\MenuController',
                'configMethod' => 'getBlockConfig',
                'execMethod' => 'execBlock',
                'saveMethod' => 'saveBlockConfig'
            ),
            array(
                'name' => 'Configurable template',
                'description' => 'Add a configurable template to your website.',
                'class' => '\Fraym\Template\DynamicTemplate',
                'configMethod' => 'getBlockConfig',
                'execMethod' => 'execBlock',
                'saveMethod' => 'saveBlockConfig'
            ),
            array(
                'name' => 'Container',
                'description' => 'Add a static template to your website.',
                'class' => '\Fraym\Block\Block',
                'execMethod' => 'execBlock',
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
    ),
    'deletable' => false,
    'repositoryKey' => 'FRAYM',
    'composer' => array(
        'require' => array(
            'doctrine/orm',
            'php-di/php-di',
            'beberlei/DoctrineExtensions',
            'werkint/jsmin',
            'imagine/imagine',
            'stichoza/google-translate-php',
            'swiftmailer/swiftmailer',
            'ocramius/proxy-manager',
            'gedmo/doctrine-extensions',
        )
    ),
);