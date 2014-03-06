<?php

namespace PlaygroundWallet\Mapper;

class Provision
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
        $this->em      = $em;
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('\PlaygroundWallet\Entity\Provision');
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

    public function findOneBy($array = array())
    {
        return $this->getEntityRepository()->findOneBy($array);
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

    public function findProvisionTotal($wallet, $currency)
    {
        $now = new \DateTime();
        $query = $this->em->createQueryBuilder();
        $query->select('SUM(provisions.amount) AS provisionsAmount');
        $query->from('\PlaygroundWallet\Entity\Provision', 'provisions');
        $query->where('provisions.wallet = :wallet');
        $query->setParameter('wallet', $wallet);
        $query->andwhere('provisions.currency = :currency');
        $query->setParameter('currency', $currency);
        $query->andwhere('provisions.expiresAt > :now');
        $query->setParameter('now', $now);
        return $query->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_SINGLE_SCALAR);
    }

}
