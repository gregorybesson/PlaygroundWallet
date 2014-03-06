<?php

namespace PlaygroundWallet\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundWallet\Entity\Wallet as WalletEntity;
use PlaygroundWallet\Entity\Transaction as TransactionEntity;
use PlaygroundWallet\Entity\Provision;

class Wallet extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var \PlaygroundWallet\Mapper\Wallet
     */
    protected $walletMapper;

    /**
     * @var \PlaygroundWallet\Mapper\Transaction
     */
    protected $transactionMapper;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function create(array $data)
    {
        $wallet = new WalletEntity();
        $wallet->populate($data);
        $wallet = $this->getWalletMapper()->insert($wallet);
        if (!$wallet) {
            return false;
        }
        return $this->update($wallet->getId(), $data);
    }

    public function edit($id, array $data)
    {
        $wallet = $this->getWalletMapper()->findById($id);
        if (!$wallet) {
            return false;
        }
        return $this->update($wallet->getId(), $data);
    }

    public function update($id, array $data)
    {
        $wallet = $this->getWalletMapper()->findById($id);
        $wallet->populate($data);
        $this->getWalletMapper()->update($wallet);
        return $wallet;
    }

    public function remove($id) {
        $walletMapper = $this->getWalletMapper();
        $wallet = $walletMapper->findById($id);
        if (!$wallet) {
            return false;
        }
        $walletMapper->remove($wallet);
        return true;
    }

    /**
     *
     * @param string $order
     * @param string $search
     * @return unknown
     */
    public function getQueryWallets($order=null, $search='')
    {
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $filterSearch = '';

        if ($search != '') {
            $searchParts = array();
            foreach ( array('name','symbol') as $field ) {
                $searchParts[] = 'p.'.$field.' LIKE :search';
            }
            $filterSearch = 'WHERE ('.implode(' OR ', $searchParts ).')';
            $query->setParameter('search', $search);
        }

        // I Have to know what is the User Class used
        $zfcUserOptions = $this->getServiceManager()->get('zfcuser_module_options');
        $userClass = $zfcUserOptions->getUserEntityClass();

        $query = $em->createQuery('
            SELECT c FROM \PlaygroundWallet\Entity\Wallet c
            ' .$filterSearch
        );
        return $query;
    }

    /**
     *
     * @param string $order
     * @param string $search
     * @return array
     */
    public function getWallets($order='DESC', $search='')
    {
        return  $this->getQueryWallets($order, $search)->getResult();
    }

    /**
     *
     * @return \PlaygroundWallet\Mapper\Wallet
     */
    public function getWalletMapper()
    {
        if ($this->walletMapper === null) {
            $this->walletMapper = $this->getServiceManager()->get('playgroundwallet_wallet_mapper');
        }
        return $this->walletMapper;
    }

    /**
     *
     * @param WalletMapper $walletMapper
     * @return \PlaygroundWallet\Service\Wallet
     */
    public function setWalletMapper(\PlaygroundWallet\Mapper\Wallet $walletMapper)
    {
        $this->walletMapper = $walletMapper;
        return $this;
    }

    /**
     *
     * @return \PlaygroundWallet\Mapper\Transaction
     */
    public function getTransactionMapper()
    {
        if ($this->transactionMapper === null) {
            $this->transactionMapper = $this->getServiceManager()->get('playgroundwallet_transaction_mapper');
        }
        return $this->transactionMapper;
    }

    /**
     *
     * @param TransactionMapper $transactionMapper
     * @return \PlaygroundWallet\Service\Wallet
     */
    public function setTransactionMapper(\PlaygroundWallet\Mapper\Transaction $transactionMapper)
    {
        $this->transactionMapper = $transactionMapper;
        return $this;
    }

    /**
     *
     * @return \Zend\ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\ServiceManagerAwareInterface::setServiceManager()
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     *
     * @param \PlaygroundUser\Entity\User $user
     * @return array
     */
    public function getActiveWallets(\PlaygroundUser\Entity\User $user) {
        return $this->getWalletMapper()->findBy(array(
            'user'=>$user,
            'isActive'=>true
        ));
    }

    /**
     *
     * @param \PlaygroundUser\Entity\User $user
     * @return array
     */
    public function createTransaction(\PlaygroundUser\Entity\User $user, \PlaygroundWallet\Entity\Currency $currency, $amount, $origin = '') {
        $activeWallets = $this->getActiveWallets($user);
        if ( empty($activeWallets) ) {
            $activeWallet = new WalletEntity();
            $activeWallet->setIsActive(true);
            $activeWallet->setUser($user);
            $activeWallet->setName('Default');
            $this->getWalletMapper()->insert($activeWallet);
            $activeWallets = array($activeWallet);
        }
        $transaction = null;
        foreach ( $activeWallets as $activeWallet ) {
            $balance = 0;
            $provision = 0;
//             foreach ( $activeWallet->getBalances() as $balanceEntity ) {
//                 if ( $balanceEntity->getCurrency()->getId() == $currency->getId() ) {
//                     $balance += $balanceEntity->getAmount();
//                 }
//             }
//             foreach ( $activeWallet->getProvisions() as $provisionItem ) {
//                 if ( $provisionItem->getCurrency()->getId() == $currency->getId() ) {
//                     $provision += $provisionItem->getAmount();
//                 }
//             }
            $balance += $this->getWalletSpendableAmount($activeWallet, $currency);
            if ( $balance - $provision + $amount >= 0 ) {
                $transaction = new TransactionEntity();
                $transaction->setWallet($activeWallet);
                $transaction->setCurrency($currency);
                $transaction->setAmount($amount);
                $transaction->setOrigin($origin);
                $activeWallet->getTransactions()->add( $transaction );
                $transactionMapper = $this->getServiceManager()->get('playgroundwallet_transaction_mapper');
                $transactionMapper->insert($transaction);
                return $transaction;
            }
        }
        throw new \Exception('Cannot pay transaction');
    }

    /**
     *
     * @param \PlaygroundUser\Entity\User $user
     * @return array
     */
    public function createProvision(\PlaygroundUser\Entity\User $user, \PlaygroundWallet\Entity\Currency $currency, $amount, $expiresAt, $origin = '')
    {
        $spendableAmount = $this->getSpendableAmount($user, $currency);
        if($amount <= $spendableAmount) {
            $provisionMapper = $this->getServiceManager()->get('playgroundwallet_provision_mapper');
            $wallet = $this->getWalletMapper()->findOneBy(array(
                'user' => $user,
                'isActive' => 1
            ));
            $provision = new Provision();
            $provision->setCurrency($currency);
            $provision->setWallet($wallet);
            $provision->setAmount($amount);
            $provision->setExpiresAt($expiresAt);
            $provision->setOrigin($origin);
            $provision = $provisionMapper->insert($provision);
            return $provision;
        }
        throw new \Exception('Cannot create provision');
    }

    public function updateProvision($provisionId, $amount, $expiresAt, $origin = '')
    {
        $provisionMapper = $this->getServiceManager()->get('playgroundwallet_provision_mapper');
        $oldProvision = $provisionMapper->findById($provisionId);
        if (!$oldProvision) {
            return false;
        }
        $spendableAmount = $this->getSpendableAmount($oldProvision->getWallet()->getUser(), $oldProvision->getCurrency());
        if($amount <= ($spendableAmount + $oldProvision->getAmount())) {
            $oldProvision->setAmount($amount);
            $oldProvision->setExpiresAt($expiresAt);
            $oldProvision->setOrigin($origin);
            $oldProvision = $provisionMapper->update($oldProvision);
            return $oldProvision;
        }
        throw new \Exception('Cannot update provision');
    }

    public function getSpendableAmount(\PlaygroundUser\Entity\User $user, \PlaygroundWallet\Entity\Currency $currency)
    {
        $wallet = $this->getWalletMapper()->findOneBy(array(
            'user' => $user,
            'isActive' => 1
        ));
        if (!$wallet) {
            return 0;
        }
        $balanceMapper = $this->getServiceManager()->get('playgroundwallet_balance_mapper');
        $balance = $balanceMapper->findOneBy(array('wallet'=>$wallet, 'currency'=>$currency));
        if (!$balance || $balance->getAmount()<= 0) {
            return 0;
        }
        $provisionMapper = $this->getServiceManager()->get('playgroundwallet_provision_mapper');
        $totalProvision = $provisionMapper->findProvisionTotal($wallet, $currency);
        $totalProvision = ($totalProvision) ? $totalProvision : 0;
        return $balance->getAmount() - $totalProvision;
    }

    public function getWalletSpendableAmount(\PlaygroundWallet\Entity\Wallet $wallet, \PlaygroundWallet\Entity\Currency $currency)
    {
        $balanceMapper = $this->getServiceManager()->get('playgroundwallet_balance_mapper');
        $balance = $balanceMapper->findOneBy(array('wallet'=>$wallet, 'currency'=>$currency));
        if (!$balance || $balance->getAmount()<= 0) {
            return 0;
        }
        $provisionMapper = $this->getServiceManager()->get('playgroundwallet_provision_mapper');
        $totalProvision = $provisionMapper->findProvisionTotal($wallet, $currency);
        $totalProvision = ($totalProvision) ? $totalProvision : 0;
        return $balance->getAmount() - $totalProvision;
    }
}
