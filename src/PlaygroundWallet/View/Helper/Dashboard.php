<?php

namespace PlaygroundWallet\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use PlaygroundWallet\Service\Currency as CurrencyService;
use PlaygroundWallet\Service\Wallet as WalletService;
use Zend\View\Model\ViewModel;

class Dashboard extends AbstractHelper implements ServiceLocatorAwareInterface
{

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var CurrencyService
     */
    protected $currencyService;

    /**
     * @var WalletService
     */
    protected $walletService;

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
    public function __invoke($dashboard = true) {
        $walletService = $this->getWalletService();
        $user = $this->getServiceLocator()->getServiceLocator()->get('zfcuser_auth_service')->getIdentity();
        $balances = array();
        $currencies = array();
        foreach( $walletService->getActiveWallets($user) as $wallet ) {
            foreach( $wallet->getBalances() as $balance ) {
                $currencySymbol = $balance->getCurrency()->getSymbol();
                $currencies[$currencySymbol] = $balance->getCurrency();
                if ( !isset($balances[$currencySymbol]) ) {
                    $balances[$currencySymbol] = 0;
                }
                $balances[$currencySymbol] += $this->getWalletService()->getWalletSpendableAmount($wallet, $balance->getCurrency());
            }
        }
        $balancesList = array();
        foreach ($balances as $symbol => $value) {
            $balancesList[$symbol] = array('amount'=>$value,'currency'=>$currencies[$symbol]);
        }
        if ($dashboard) {
            $widgetModel = new ViewModel(array('balances'=> $balancesList));
            $widgetModel->setTemplate('playground-wallet/widgets/dashboard');
            return $this->getView()->render($widgetModel);
        } else {
            return $balances;
        }
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

    /**
     *
     * @return Dashboard
     */
    public function getWalletService()
    {
        if (!$this->walletService) {
            $this->walletService = $this->getServiceLocator()->getServiceLocator()->get('playgroundwallet_wallet_service');
        }
        return $this->walletService;
    }

    /**
     *
     * @param WalletService $walletService
     * @return Dashboard
     */
    public function setWalletService(WalletService $walletService)
    {
        $this->walletService = $walletService;
        return $this;
    }
}