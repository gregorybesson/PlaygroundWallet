<?php

namespace PlaygroundWallet;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Validator\AbstractValidator;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();

        $options = $sm->get('playgroundcore_module_options');
        $locale = $options->getLocale();
        $translator = $sm->get('translator');
        if (!empty($locale)) {
            //translator
            $translator->setLocale($locale);

            // plugins
            $translate = $sm->get('viewhelpermanager')->get('translate');
            $translate->getTranslator()->setLocale($locale);
        }
        AbstractValidator::setDefaultTranslator($translator,'playgroundcore');

        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        // Here we need to schedule the core cron service

        // If cron is called, the $e->getRequest()->getPost() produces an error so I protect it with
        // this test
        if ((get_class($e->getRequest()) == 'Zend\Console\Request')) {
            return;
        }
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/../../autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoLoader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__.'/../../src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * @return array
     */
    public function getViewHelperConfig()
    {
        return array();
    }

    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
            ),

            'invokables' => array(
                'playgroundwallet_currency_service' => 'PlaygroundWallet\Service\Currency',
                'playgroundwallet_wallet_service' => 'PlaygroundWallet\Service\Wallet',
            ),

            'factories' => array(
                'playgroundwallet_currency_mapper' => function ($sm) {
                    $mapper = new Mapper\Currency(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundwallet_transaction_mapper' => function ($sm) {
                    $mapper = new Mapper\Transaction(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundwallet_provision_mapper' => function ($sm) {
                    $mapper = new Mapper\Provision(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundwallet_wallet_mapper' => function ($sm) {
                    $mapper = new Mapper\Wallet(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundwallet_balance_mapper' => function ($sm) {
                    $mapper = new Mapper\Balance(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundwallet_currency_form' => function ($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\Currency(null, $sm, $translator);
                    return $form;
                },
                'playgroundwallet_wallet_form' => function ($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\Wallet(null, $sm, $translator);
                    return $form;
                },
                'playgroundwallet_transaction_form' => function ($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\Transaction(null, $sm, $translator);
                    return $form;
                },
            ),
        );
    }
}