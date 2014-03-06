<?php
return array(
    'doctrine' => array(
        'eventmanager' => array(
            'orm_default' => array(
                'subscribers' => array(
                    'Gedmo\Timestampable\TimestampableListener',
                    'Gedmo\Loggable\LoggableListener',
                ),
            ),
        ),
        'driver' => array(
            'playgroundwallet_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => __DIR__ . '/../src/PlaygroundWallet/Entity'
            ),
            'orm_default' => array(
                'drivers' => array(
                    'PlaygroundWallet\Entity' => 'playgroundwallet_entity'
                )
            )
        )
    ),
    'view_helpers' => array(
        'invokables' => array(
            'currencyformat' => 'PlaygroundWallet\View\Helper\CurrencyFormat',
            'currencyicon' => 'PlaygroundWallet\View\Helper\CurrencyIcon',
            'walletdashboard' => 'PlaygroundWallet\View\Helper\Dashboard',
        ),
    ),
    'view_manager' => array(
        'template_map' => array(),
        'template_path_stack' => array(
             __DIR__ . '/../views/admin/',
             __DIR__ . '/../views/frontend/'
        ),
    ),
    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
            array(
                'type' => 'phpArray',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.php',
                'text_domain' => 'playgroundwallet'
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'playgroundwallet'          => 'PlaygroundWallet\Controller\Frontend\IndexController',
            'playgroundwallet_admin'    => 'PlaygroundWallet\Controller\Admin\IndexController',
            'playgroundwallet_admin_currency' => 'PlaygroundWallet\Controller\Admin\CurrencyController',
            'playgroundwallet_admin_wallet'   => 'PlaygroundWallet\Controller\Admin\WalletController',
        ),
    ),
    'router' => array(
        'routes' =>array(
            'frontend' => array(
                'child_routes' => array(
                    'wallet' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => 'wallet',
                            'defaults' => array(
                                'controller' => 'playgroundwallet',
                                'action' => 'index',
                            )
                        ),
                        'may_terminate' => true,
                    ),
                ),
            ),
            'admin' => array(
                'child_routes' => array(
                    'wallet' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/wallet',
                            'defaults' => array(
                                'controller' => 'playgroundwallet_admin',
                                'action' => 'list',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'currency' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/currency',
                                    'defaults' => array(
                                        'controller' => 'playgroundwallet_admin_currency',
                                        'action' => 'list',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'add' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/add',
                                            'defaults' => array(
                                                'controller' => 'playgroundwallet_admin_currency',
                                                'action' => 'add',
                                            ),
                                        ),
                                    ),
                                    'list' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/list',
                                            'defaults' => array(
                                                'controller' => 'playgroundwallet_admin_currency',
                                                'action' => 'list',
                                            ),
                                        ),
                                    ),
                                    'remove' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/remove/:id',
                                            'constraints' => array(
                                                ':id' => '[0-9]+',
                                            ),
                                            'defaults' => array(
                                                'controller' => 'playgroundwallet_admin_currency',
                                                'action' => 'remove',
                                                'codeId' => 0,
                                            ),
                                        ),
                                    ),
                                    'edit' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/edit/:id',
                                            'constraints' => array(
                                                ':codeId' => '[0-9]+',
                                            ),
                                            'defaults' => array(
                                                'controller' => 'playgroundwallet_admin_currency',
                                                'action' => 'edit',
                                                'codeId' => 0,
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'wallet' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/wallet',
                                    'defaults' => array(
                                        'controller' => 'playgroundwallet_admin_wallet',
                                        'action' => 'list',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'add' => array(
                                        'type' => 'Literal',
                                        'options' => array(
                                            'route' => '/add',
                                            'defaults' => array(
                                                'controller' => 'playgroundwallet_admin_wallet',
                                                'action' => 'add',
                                            ),
                                        ),
                                    ),
                                    'list' => array(
                                        'type' => 'Segment',
                                        'options' => array(
                                            'route' => '/list[/:p]',
                                            'constraints' => array(
                                                ':p' => '[0-9]+',
                                            ),
                                            'defaults' => array(
                                                'controller' => 'playgroundwallet_admin_wallet',
                                                'action' => 'list',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'navigation' => array(
        'admin' => array(
            'wallet' => array(
                'label' => 'Wallet',
                'route' => 'admin/wallet/currency',
                'resource' => 'wallet',
                'privilege' => 'list',
                'pages' => array(
                    'list-currency' => array(
                        'label' => 'Currency list',
                        'route' => 'admin/wallet/currency/list',
                        'resource' => 'wallet',
                        'privilege' => 'list',
                        'pages' => array(
                            'edit' => array(
                                'label' => 'Edit a currency',
                                'route' => 'admin/wallet/currency/edit',
                                'resource' => 'currency',
                                'privilege' => 'edit',
                            ),
                        ),
                    ),
                    'add-currency' => array(
                        'label' => 'Create a currency',
                        'route' => 'admin/wallet/currency/add',
                        'resource' => 'wallet',
                        'privilege' => 'add',
                    ),
                    'list-wallet' => array(
                        'label' => 'Wallet list',
                        'route' => 'admin/wallet/wallet/list',
                        'resource' => 'wallet',
                        'privilege' => 'list',
                        'pages' => array(
                            'edit' => array(
                                'label' => 'Edit a currency',
                                'route' => 'admin/wallet/wallet/edit',
                                'resource' => 'wallet',
                                'privilege' => 'edit',
                            ),
                        ),
                    ),
                    'add-wallet' => array(
                        'label' => 'Add currencies to wallet',
                        'route' => 'admin/wallet/wallet/add',
                        'resource' => 'wallet',
                        'privilege' => 'add',
                    ),
                ),
            ),
        ),
    )
);
