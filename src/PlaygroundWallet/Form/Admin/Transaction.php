<?php
namespace PlaygroundWallet\Form\Admin;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;

class Transaction extends ProvidesEventsForm
{
    protected $serviceManager;

    public function __construct ($name = null, ServiceManager $sm, Translator $translator)
    {
        parent::__construct($name);

        $this->setServiceManager($sm);

        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',
                'value' => 0
            ),
        ));
        
        $this->add(array(
            'name' => 'email',
            'options' => array(
                'label' => $translator->translate('Email', 'playgroundwallet'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Email', 'playgroundwallet'),
            ),
        ));
        
        $this->add(array(
            'name' => 'amount',
            'options' => array(
                'label' => $translator->translate('Amount', 'playgroundwallet'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Amount', 'playgroundwallet'),
            ),
        ));
        
        
        $currencies = array();
        $currencyService = $sm->get('playgroundwallet_currency_service');
        foreach( $currencyService->getCurrencies() as $currency ) {
            $currencies[$currency->getId()] = $currency->getName();
        }
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'currency_id',
            'options' => array(
                'label' => $translator->translate('Currency', 'playgroundwallet'),
                'value_options' => $currencies,
            )
        ));

        $submitElement = new Element\Button('submit');
        $submitElement->setAttributes(array(
            'type'  => 'submit',
            'class' => 'btn btn-primary',
        ));

        $this->add($submitElement, array(
            'priority' => -100,
        ));
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager ()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager (ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
}