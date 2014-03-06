<?php

namespace PlaygroundWallet\View\Helper;

use Zend\I18n\View\Helper\CurrencyFormat as ZendCurrencyFormat;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use PlaygroundWallet\Mapper\Currency as CurrencyMapper;

class CurrencyFormat extends ZendCurrencyFormat implements ServiceLocatorAwareInterface
{

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var CurrencyMapper
     */
    protected $currencyMapper;

    protected static $currencies = array();

    /**
     *
     * @param string $currencyCode
     * @return \PlaygroundWallet\Entity\Currency
     */
    protected function getCurrencyEntity($currencyCode) {
        if ( ! isset( self::$currencies[$currencyCode] ) ) {
            $currency = $this->getCurrencyMapper()->findBySymbol($currencyCode);
            self::$currencies[$currencyCode] = $currency ? $currency : false;
        }
        return self::$currencies[$currencyCode];
    }

    /**
     * Format a number
     *
     * @param  float  $number
     * @param  string $currencyCode
     * @param  bool   $showDecimals
     * @param  string $locale
     * @param  string $pattern
     * @return string
     */
    public function __invoke(
        $number,
        $currencyCode = null,
        $showDecimals = null,
        $locale       = null,
        $pattern      = null,
        $icon         = false
    ) {

        if (null === $locale) {
            $locale = $this->getLocale();
        }
        if (null === $currencyCode) {
            $currencyCode = $this->getCurrencyCode();
        }
        if (null === $showDecimals) {
            $showDecimals = $this->shouldShowDecimals();
        }
        if (null === $pattern) {
            $pattern = $this->getCurrencyPattern();
        }
        return $this->formatCurrency($number, $currencyCode, $showDecimals, $locale, $pattern, $icon);
    }

    /**
     * Format a number
     *
     * @param  float  $number
     * @param  string $currencyCode
     * @param  bool   $showDecimals
     * @param  string $locale
     * @param  string $pattern
     * @return string
     */
    protected function formatCurrency(
        $number,
        $currencyCode,
        $showDecimals,
        $locale,
        $pattern,
        $icon = false
    ) {
        if ( $icon ) {
            $currency = $this->getCurrencyEntity($currencyCode);
            if ( ( ! $currency ) || ( ! $currency->getIcon() ) )  {
                $icon = false;
            }
        }
        if ( ! $icon ) {
            $result = parent::formatCurrency(
                $number,
                $currencyCode,
                $showDecimals,
                $locale,
                $pattern
            );
            if ( strlen( $result ) ) {
                return $result;
            }
        }
        $formatterId = md5($locale);
        $formatter = $this->formatters[$formatterId];
        /* @var $formatter \NumberFormatter */
        $currency = $this->getCurrencyEntity($currencyCode);
        $currencySymbol = $currency ? ( $icon && $currency->getIcon() ? '<img src="/'.$currency->getIcon().'" alt="'.$currency->getName().'" />': $currency->getName().' ' ) : '';
        $formatter->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, $currencySymbol);

        if ($showDecimals) {
            $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 2);
        } else {
            $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);
        }

        return $formatter->format($number, \NumberFormatter::DECIMAL);
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
     * @return \PlaygroundWallet\Mapper\Currency
     */
    public function getCurrencyMapper()
    {
        if ($this->currencyMapper === null) {
            $this->currencyMapper = $this->getServiceLocator()->getServiceLocator()->get('playgroundwallet_currency_mapper');
        }
        return $this->currencyMapper;
    }

    /**
     *
     * @param CurrencyMapper $currencyMapper
     * @return \PlaygroundWallet\Mapper\Currency
     */
    public function setCurrencyMapper(CurrencyMapper $currencyMapper)
    {
        $this->currencyMapper = $currencyMapper;
        return $this;
    }
}