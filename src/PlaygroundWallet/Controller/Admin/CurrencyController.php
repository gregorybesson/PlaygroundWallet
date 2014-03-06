<?php
namespace PlaygroundWallet\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use PlaygroundWallet\Service\Currency as CurrencyService;

class CurrencyController extends AbstractActionController
{
    /**
     * @var CurrencyService
     */
    protected $currencyService;
    
    
    public function listAction() {
        $routeMatch = $this->getEvent()->getRouteMatch();
        $filter = $routeMatch->getParam('filter');
        $search = $routeMatch->getParam('search');
        $page = (int) $routeMatch->getParam('p');
        
        $adapter = new DoctrineAdapter(
            new ORMPaginator(
                $this->getCurrencyService()->getQueryCurrencies()
            )
        );
        
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber($page);
        
        return new ViewModel(array(
            'currencies' => $paginator,
            'filter' => $filter,
            'search' => $search,
            'page' => $page
        ));
        
    }

    public function addAction()
    {
        $form = $this->getServiceLocator()->get('playgroundwallet_currency_form');
        $form->get('submit')->setLabel('Create');
        $form->setAttribute('action', '');
        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            $form->setData($data);
            if ($form->isValid()) {
                $currency = $this->getCurrencyService()->create($data);
                return $this->redirect()->toRoute('admin/wallet/currency/list');
            } else {
                return $this->redirect()->toRoute('admin/wallet/currency/add');
            }
        }
        $viewModel = new ViewModel(array(
            'form' => $form,
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));
        $viewModel->setTemplate('playground-wallet/currency/edit');
        return $viewModel;
    }

    public function editAction()
    {
        $currencyMapper = $this->getCurrencyService()->getCurrencyMapper();
        $id = (int) $this->getEvent()->getRouteMatch()->getParam('id');
        if (
            ( !$id) ||
            ! ( $currency = $currencyMapper->findById($id) )
        ) {
            return $this->redirect()->toRoute('admin/wallet/currency/list');
        }
        $data = $currency->getArrayCopy();
        $form = $this->getServiceLocator()->get('playgroundwallet_currency_form');
        $form->get('submit')->setLabel('Edit');
        $form->setAttribute('action', '');
        $form->setData($data);
        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            $form->setData($data);
            if ($form->isValid()) {
                $currency = $this->getCurrencyService()->edit($id,$data);
                return $this->redirect()->toRoute('admin/wallet/currency/list');
            } else {
                return $this->redirect()->toRoute('admin/wallet/currency/edit/id/'.$currency->getId());
            }
        }
        $viewModel = new ViewModel(array(
            'form' => $form,
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));
        $viewModel->setTemplate('playground-wallet/currency/edit');
        return $viewModel;
    }

    public function removeAction()
    {
        $currencyMapper = $this->getCurrencyService()->getCurrencyMapper();
        $id = (int) $this->getEvent()->getRouteMatch()->getParam('id');
        if ( ! ( $currency = $currencyMapper->findById($id) ) ) {
            return $this->redirect()->toRoute('admin/wallet/currency/list');
        }
        $result = $currencyMapper->remove($currency);
        if (!$result) {
            $this->flashMessenger()->addMessage('An error occured');
        } else {
            $this->flashMessenger()->addMessage('The element has been deleted');
        }
        return $this->redirect()->toRoute('admin/wallet/currency/list');
    }
    

    /**
     *
     * @return \PlaygroundWallet\Service\Currency
     */
    public function getCurrencyService()
    {
        if (!$this->currencyService) {
            $this->currencyService = $this->getServiceLocator()->get('playgroundwallet_currency_service');
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