<?php

namespace PlaygroundWallet\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundWallet\Entity\Currency as CurrencyEntity;
use PlaygroundWallet\Mapper\Currency as CurrencyMapper;
use PlaygroundCore\Filter\Slugify;
use Zend\Stdlib\ErrorHandler;

class Currency extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var \PlaygroundWallet\Mapper\Currency
     */
    protected $currencyMapper;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function create(array $data)
    {
        $currency = new CurrencyEntity();
        $currency->populate($data);
        $currency = $this->getCurrencyMapper()->insert($currency);
        if (!$currency) {
            return false;
        }
        return $this->update($currency->getId(), $data);
    }

    public function edit($id, array $data)
    {
        $currency = $this->getCurrencyMapper()->findById($id);
        if (!$currency) {
            return false;
        }
        return $this->update($currency->getId(), $data);
    }

    public function update($id, array $data)
    {
        $currency = $this->getCurrencyMapper()->findById($id);
        $currency->populate($data);
        $this->getCurrencyMapper()->update($currency);
        return $currency;
    }

    public function remove($id) {
        $currencyMapper = $this->getCurrencyMapper();
        $currency = $currencyMapper->findById($id);
        if (!$currency) {
            return false;
        }
        $currencyMapper->remove($currency);
        return true;
    }

    public function getCurrencyMapper()
    {
        if (null === $this->currencyMapper) {
            $this->currencyMapper = $this->getServiceManager()->get('playgroundwallet_currency_mapper');
        }
        return $this->currencyMapper;
    }

    public function setCurrencyMapper(CurrencyMapper $currencyMapper)
    {
        $this->currencyMapper = $currencyMapper;
        return $this;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * 
     * @param string $order
     * @param string $search
     * @return unknown
     */
    public function getQueryCurrencies($order=null, $search='')
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
            SELECT c FROM \PlaygroundWallet\Entity\Currency c
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
    public function getCurrencies($order='DESC', $search='')
    {
        return  $this->getQueryCurrencies($order, $search)->getResult();
    }
}