<?php

namespace PlaygroundWallet\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Gedmo\Mapping\Annotation as Gedmo;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="wallet_balance")
 */
class Balance implements InputFilterAwareInterface
{
    protected $inputFilter;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="decimal",precision=20,scale=8)
     */
    protected $amount;
    
    /**
     * @ORM\Column(type="string",length=64)
     */
    protected $signature;
    
    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at",type="datetimetz",nullable=FALSE)
     */
    protected $createdAt;
    
    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at",type="datetimetz",nullable=FALSE)
     */
    protected $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Currency", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="currency_id", referencedColumnName="id")
     **/
    protected $currency;
    
    /**
     * @ORM\ManyToOne(targetEntity="Wallet", inversedBy="balances", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="wallet_id", referencedColumnName="id")
     **/
    protected $wallet;

    /**
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param double $amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     *
     * @param string $signature
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @return \Datetime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \Datetime $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return Wallet $wallet
     */
    public function getWallet()
    {
        return $this->wallet;
    }

    /**
     * @param Wallet $wallet
     */
    public function setWallet(Wallet $wallet)
    {
        $this->wallet = $wallet;
        return $this;
    }

    /**
     * @return Currency $currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param Currency $currency
     */
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        foreach( array('amount','signature','origin') as $name ) {
            $this->$name = (isset($data[$name])) ? $data[$name] : null;
        }
    }

    /**
     * @return the $inputFilter
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new Factory();
            $inputFilter->add($factory->createInput(array(
                'name' => 'id',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'Int'
                    )
                )
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => 'signature',
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 64,
                            'max' => 64
                        )
                    )
                )
            )));
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    /**
     * @param field_type $inputFilter
     */
    public function setInputFilter (InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
    
    /**
     * @ORM\PrePersist
     */
    public function beforePersist()
    {
        $this->signature = sha1(
            $this->getAmount()
            .'|'.
            $this->getCurrency()->getId()
            .'|'.
            $this->getCreatedAt()
        );
    }
    
    public function compute(\Doctrine\ORM\EntityManager $em)
    {
        $balance = 0;
        $ids = array();
        foreach( $this->getWallet()->getTransactions() as $transaction ) {
            if ( ( $this->getCurrency()->getId() == $transaction->getCurrency()->getId() ) && ! in_array($transaction->getId(),$ids) ) {
                $ids[] = $transaction->getId();
                $balance += $transaction->getAmount();
            }
        }
        $this->amount = $balance;
        $em->persist($this);
        $em->flush();
    }
}