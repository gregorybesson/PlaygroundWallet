<?php

namespace PlaygroundWallet\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use PlaygroundWallet\Service\Currency as CurrencyService;

class CurrencyIcon extends AbstractHelper implements ServiceLocatorAwareInterface
{

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var CurrencyService
     */
    protected $currencyService;
    
    protected static $currencies = array();
    
    /**
     * 
     * @param string $currencyCode
     * @return \PlaygroundWallet\Entity\Currency
     */
    protected function getCurrencyEntity($currencyCode) {
        $currencyService = $this->getCurrencyService();
        if ( ! isset( self::$currencies[$currencyCode] ) ) {
            $currency = $currencyService->getCurrencyMapper()->findBySymbol($currencyCode);
            self::$currencies[$currencyCode] = $currency ? $currency : false;
        }
        return self::$currencies[$currencyCode];
    }
    
    /**
     * Format a number
     *
     * @param  string $currencyCode
     * @return string
     */
    public function __invoke( $currencyCode ) {
        $currency = $this->getCurrencyEntity($currencyCode);
        $currencySymbol = $currency && $currency->getIcon() ? '<img src="/'.$currency->getIcon().'" alt="'.$currency->getName().'" />' : '';
        return $currencySymbol;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    

    /**
     *
     * @return \PlaygroundWallet\Service\Currency
     */
    public function getCurrencyService()
    {
        if (!$this->currencyService) {
            $this->currencyService = $this->getServiceLocator()->getServiceLocator()->get('playgroundwallet_currency_service');
        }
        return $this->currencyService;
    }
    
    /**
     *
     * @param CurrencyService $productService
     * @return \PlaygroundWallet\Service\Currency
     */
    public function setCurrencyService(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }
}