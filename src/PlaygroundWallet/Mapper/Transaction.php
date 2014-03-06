<?php

namespace PlaygroundWallet\Mapper;

class Transaction
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

     /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $er;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('\PlaygroundWallet\Entity\Transaction');
        }
        return $this->er;
    }

    public function findById($id)
    {
        return $this->getEntityRepository()->find($id);
    }

    public function findBy($array = array(), $sortArray = array())
    {
        return $this->getEntityRepository()->findBy($array, $sortArray);
    }

    public function insert($entity)
    {
        return $this->persist($entity);
    }

    public function update($entity)
    {
        throw new \Exception('Transaction update is forbidden');
    }

    protected function persist($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
        return $entity;
    }

    public function findAll()
    {
        return $this->getEntityRepository()->findAll();
    }

    public function remove($entity)
    {
        throw new \Exception('Transaction deletion is forbidden');
    }
    
    /**
     * Retrive all transactions by user
     */
     public function getuserTransactions($user)
     {
         $query = $this->em->createQuery('
            SELECT tr
            FROM \PlaygroundWallet\Entity\Transaction tr
            JOIN \PlaygroundWallet\Entity\Wallet wal WITH wal.id = tr.wallet
            JOIN \PlaygroundWallet\Entity\Currency cu WITH cu.id = tr.currency
            WHERE wal.user = :user
            AND cu.symbol LIKE :symbol
            ORDER BY tr.createdAt DESC
        ');
        $query->setParameter('user', $user);
        $query->setParameter('symbol', 'jetons');
        return $query->getResult();
     }
}
