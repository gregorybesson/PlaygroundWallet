<?php

namespace PlaygroundWallet\Mapper;

class Wallet
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
            $this->er = $this->em->getRepository('\PlaygroundWallet\Entity\Wallet');
        }
        return $this->er;
    }

    public function findById($id)
    {
        return $this->getEntityRepository()->find($id);
    }

    public function findOneBy($array = array())
    {
        return $this->getEntityRepository()->findOneBy($array);
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
        return $this->persist($entity);
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
        $this->em->remove($entity);
        $this->em->flush();
    }
}