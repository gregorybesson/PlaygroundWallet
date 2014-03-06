<?php
namespace PlaygroundWallet\Controller\Frontend;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Datetime;
use PlaygroundWallet\Service\Wallet as WalletService;

class IndexController extends AbstractActionController
{
    
    
    /**
     * @var WalletService
     */
    protected $walletService;

    public function indexAction()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();
        $wallets = $this->getWalletService()->getActiveWallets($user);
        
        return new ViewModel(array(
            'user'=>$user,
            'wallets'=>$wallets
        ));
    }

    /**
     * 
     * @return \PlaygroundWallet\Service\Wallet
     */
    public function getWalletService()
    {
        if (!$this->walletService) {
            $this->walletService = $this->getServiceLocator()->get('playgroundwallet_wallet_service');
        }
        return $this->walletService;
    }

    /**
     * 
     * @param WalletService $walletService
     * @return \PlaygroundWallet\Controller\Frontend\IndexController
     */
    public function setWalletService(WalletService $walletService)
    {
        $this->walletService = $walletService;
        return $this;
    }

}