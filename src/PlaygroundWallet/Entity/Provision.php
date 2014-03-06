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
 * @ORM\Table(name="wallet_provision")
 */
class Provision implements InputFilterAwareInterface
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
     * @ORM\Column(type="string",length=100)
     */
    protected $origin;

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
     * @ORM\Column(name="expires_at",type="datetimetz",nullable=FALSE)
     */
    protected $expiresAt;

    /**
     * @ORM\ManyToOne(targetEntity="Currency", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="currency_id", referencedColumnName="id")
     **/
    protected $currency;

    /**
     * @ORM\ManyToOne(targetEntity="Wallet", inversedBy="provisions", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="wallet_id", referencedColumnName="id")
     **/
    protected $wallet;

    /**
     * @param unknown $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return $id
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
     * @return $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
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
     *
     * @return string $signature
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
        return $this;
    }

    /**
     *
     * @param double $origin
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     *
     * @return string $origin
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * @return \Datetime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \Datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \Datetime $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \Datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return \Datetime $expiresAt
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @param \Datetime $expiresAt
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;
        return $this;
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
     * @ORM\PrePersist
     */
    public function beforePersist()
    {
        $this->signature = sha1(
            $this->getAmount()
            .'|'.
            $this->getOrigin()
            .'|'.
            $this->getCurrency()->getId()
            .'|'.
            $this->getCreatedAt()
        );
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        foreach( array('amount','signature','origin','expiration') as $name ) {
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
            $inputFilter->add($factory->createInput(array(
                'name' => 'origin',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 100
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
}