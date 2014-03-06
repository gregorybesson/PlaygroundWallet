<?php
namespace PlaygroundWallet\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use PlaygroundWallet\Service\Wallet as WalletService;

class WalletController extends AbstractActionController
{

    /**
     * @var WalletService
     */
    protected $walletService;


    public function listAction() {
        $routeMatch = $this->getEvent()->getRouteMatch();
        $filter = $routeMatch->getParam('filter');
        $search = $routeMatch->getParam('search');
        $page = (int) $routeMatch->getParam('p');

        $adapter = new DoctrineAdapter(
            new ORMPaginator(
                $this->getWalletService()->getQueryWallets()
            )
        );

        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber($page);

        return new ViewModel(array(
            'wallets' => $paginator,
            'filter' => $filter,
            'search' => $search,
            'page' => $page
        ));

    }

    public function addAction()
    {
        $form = $this->getServiceLocator()->get('playgroundwallet_transaction_form');
        $form->get('submit')->setLabel('Create');
        $form->setAttribute('action', '');
        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            $form->setData($data);
            $userService = $this->getServiceLocator()->get('playgrounduser_user_service');
            /* @var $userService \PlaygroundUser\Service\User */
            $currency = $this->getServiceLocator()->get('playgroundwallet_currency_service')->getCurrencyMapper()->findById($data['currency_id']);
            if (
                $form->isValid() &&
                $data['email'] &&
                ( $user = $userService->getUserMapper()->findByEmail($data['email']) ) &&
                $currency
            ) {
                $wallet = $this->getWalletService()->createTransaction($user, $currency, $data['amount']);
                return $this->redirect()->toRoute('admin/wallet/wallet/list');
            } else {
                return $this->redirect()->toRoute('admin/wallet/wallet/add');
            }
        }
        return new ViewModel(
            array(
                'form' => $form,
                'flashMessages' => $this->flashMessenger()->getMessages(),
            )
        );
    }

    public function editAction()
    {
        $walletMapper = $this->getWalletService()->getWalletMapper();
        $id = (int) $this->getEvent()->getRouteMatch()->getParam('id');
        if (
        ( !$id) ||
        ! ( $wallet = $walletMapper->findById($id) )
        ) {
            return $this->redirect()->toRoute('admin/wallet/wallet/list');
        }
        $data = $wallet->getArrayCopy();
        $form = $this->getServiceLocator()->get('playgroundwallet_wallet_form');
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
                $wallet = $this->getWalletService()->edit($id,$data);
                return $this->redirect()->toRoute('admin/wallet/wallet/list');
            } else {
                return $this->redirect()->toRoute('admin/wallet/wallet/edit/id/'.$wallet->getId());
            }
        }
        return new ViewModel(
            array(
                'form' => $form,
                'flashMessages' => $this->flashMessenger()->getMessages(),
            )
        );
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
     * @param WalletService $productService
     * @return \PlaygroundWallet\Service\Wallet
     */
    public function setWalletService(WalletService $walletService)
    {
        $this->walletService = $walletService;
        return $this;
    }


}