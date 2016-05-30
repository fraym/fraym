<?php

return [
    'name' => 'Fraym Core',
    'entity' => [
        '\Fraym\SiteManager\Entity\Extension' => [
            [
                'name' => 'Menu Editor',
                'class' => '\Fraym\Menu\MenuController',
                'method' => 'getContent',
                'active' => '1',
                'description' => 'EXT_SITEMENUEDITOR_DESC',
                'iconCssClass' => 'fa fa-sitemap',
                'sorter' => 0
            ],
            [
                'name' => 'Data Manager',
                'class' => '\Fraym\EntityManager\EntityManagerController',
                'method' => 'getContent',
                'active' => '1',
                'description' => 'EXT_ENTITYMANAGER_DESC',
                'iconCssClass' => 'fa fa-briefcase',
                'sorter' => 10
            ],
            [
                'name' => 'File Manager',
                'class' => '\Fraym\FileManager\FileManagerController',
                'method' => 'getContent',
                'active' => '1',
                'description' => 'EXT_FILEMANAGER_DESC',
                'iconCssClass' => 'fa fa-hdd-o',
                'sorter' => 20
            ],
            [
                'name' => 'Extension Manager',
                'class' => '\Fraym\Registry\RegistryManagerController',
                'method' => 'getContent',
                'active' => '1',
                'description' => 'EXT_EXTENSIONMANAGER_DESC',
                'iconCssClass' => 'fa fa-archive',
                'sorter' => 30
            ],
            [
                'name' => 'Change Set Manager',
                'class' => '\Fraym\Block\BlockChangeSetManagerController',
                'method' => 'getContent',
                'active' => '1',
                'description' => 'EXT_CHANGE_SET_MANAGER_DESC',
                'iconCssClass' => 'fa fa-tasks',
                'sorter' => 40
            ],
        ],
        '\Fraym\EntityManager\Entity\Entity' => [
            [
                'className' => '\Fraym\User\Entity\User',
                'name' => 'User entry',
                'group' => [
                    '\Fraym\EntityManager\Entity\Group' => [
                        'name' => 'User'
                    ]
                ],
            ],
            [
                'className' => '\Fraym\User\Entity\Group',
                'name' => 'Usergroup entry',
                'group' => [
                    '\Fraym\EntityManager\Entity\Group' => [
                        'name' => 'User'
                    ]
                ],
            ],
            [
                'className' => '\Fraym\Template\Entity\Template',
                'name' => 'Menu template entry',
                'group' => [
                    '\Fraym\EntityManager\Entity\Group' => [
                        'name' => 'Template'
                    ]
                ],
            ],
            [
                'className' => '\Fraym\Block\Entity\Template',
                'name' => 'Block template entry',
                'group' => [
                    '\Fraym\EntityManager\Entity\Group' => [
                        'name' => 'Template'
                    ]
                ],
            ],
            [
                'className' => '\Fraym\Site\Entity\Site',
                'name' => 'Website entry',
                'group' => [
                    '\Fraym\EntityManager\Entity\Group' => [
                        'name' => 'Website'
                    ]
                ],
            ],
            [
                'className' => '\Fraym\Site\Entity\Domain',
                'name' => 'Domain entry',
                'group' => [
                    '\Fraym\EntityManager\Entity\Group' => [
                        'name' => 'Website'
                    ]
                ],
            ],
            [
                'className' => '\Fraym\Translation\Entity\Translation',
                'name' => 'Translation entry',
                'group' => [
                    '\Fraym\EntityManager\Entity\Group' => [
                        'name' => 'Translation'
                    ]
                ],
            ],
            [
                'className' => '\Fraym\Registry\Entity\Config',
                'name' => 'Config entry',
                'group' => [
                    '\Fraym\EntityManager\Entity\Group' => [
                        'name' => 'Config'
                    ]
                ],
            ],
            [
                'className' => '\Fraym\Locale\Entity\Locale',
                'name' => 'Locale',
                'group' => [
                    '\Fraym\EntityManager\Entity\Group' => [
                        'name' => 'Website'
                    ]
                ],
            ]
        ],
        '\Fraym\Block\Entity\Extension' => [
            [
                'name' => 'Menu',
                'description' => 'Add a website menu to your site.',
                'class' => '\Fraym\Menu\MenuController',
                'configMethod' => 'getBlockConfig',
                'execMethod' => 'execBlock',
                'saveMethod' => 'saveBlockConfig'
            ],
            [
                'name' => 'Configurable template',
                'description' => 'Add a configurable template to your website.',
                'class' => '\Fraym\Template\DynamicTemplate',
                'configMethod' => 'getBlockConfig',
                'execMethod' => 'execBlock',
                'saveMethod' => 'saveBlockConfig'
            ],
            [
                'name' => 'Container',
                'description' => 'Add a static template to your website.',
                'class' => '\Fraym\Block\Block',
                'execMethod' => 'execBlock',
            ],
            [
                'name' => 'User',
                'description' => 'Adds a LogIn / LogOut form to your website.',
                'class' => '\Fraym\User\User',
                'configMethod' => 'getBlockConfig',
                'execMethod' => 'execBlock',
                'saveMethod' => 'saveBlockConfig'
            ],
            [
                'name' => 'Image',
                'description' => 'Add a image to your website.',
                'class' => '\Fraym\Image\Image',
                'configMethod' => 'getBlockConfig',
                'execMethod' => 'execBlock',
                'saveMethod' => 'saveBlockConfig'
            ],
        ]
    ],
    'config' => [
        'ADMIN_GROUP_IDENTIFIER' => [
            'value' => 'GROUP:Administrator',
            'description' => 'The identifier for the Administrator group.',
            'deletable' => false
        ],
        'ADMIN_LOCALE_ID' => [
            'value' => '1',
            'description' => 'The default administrator locale.',
            'deletable' => false
        ],
        'TRANSLATION_AUTO' => [
            'value' => '1',
            'description' => 'Set to 1 for enable auto translation or to 0 to disable auto translation.',
            'deletable' => false
        ],
        'TRANSLATION_ADD_DEFAULT_TO_DB' => [
            'value' => '0',
            'description' => 'Set to 1 for adding all default translation texts to the database.',
            'deletable' => false
        ],
        'IMAGE_PATH' => [
            'value' => 'images/deposit',
            'description' => 'The save path of the converted images.',
            'deletable' => false
        ],
        'FILEMANAGER_STORAGES' => [
            'value' => 'Template,Public/images',
            'description' => 'The folders that are mapped in the file manager. You can seperate multiple folder with comma.',
            'deletable' => false
        ],
    ],
    'deletable' => false,
    'repositoryKey' => 'fraym/fraym'
];