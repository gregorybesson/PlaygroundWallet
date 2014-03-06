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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="wallet")
 */
class Wallet implements InputFilterAwareInterface
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string",length=50)
     */
    protected $name = '';

    /**
     * @ORM\Column(name="is_active",type="boolean")
     */
    protected $isActive = true;

    /**
     * @ORM\ManyToOne(targetEntity="\PlaygroundUser\Entity\User", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     **/
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="Balance", mappedBy="wallet")
     **/
    protected $balances;

    /**
     * @ORM\OneToMany(targetEntity="Provision", mappedBy="wallet")
     **/
    protected $provisions;

    /**
     * @ORM\OneToMany(targetEntity="Transaction", mappedBy="wallet")
     **/
    protected $transactions;

    public function __construct() {
        $this->balances = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->provisions = new ArrayCollection();
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return \PlaygroundUser\Entity\UserInterface $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \PlaygroundUser\Entity\User $user
     */
    public function setUser(\PlaygroundUser\Entity\User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return array $balances
     */
    public function getBalances()
    {
        return $this->balances;
    }

    /**
     * @param array $balances
     */
    public function setBalances($balances)
    {
        $this->balances = $balances;
        return $this;
    }

    /**
     * @return array $transactions
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @param array $transactions
     */
    public function setTransactions($transactions)
    {
        $this->transactions = $transactions;
        return $this;
    }


    /**
     * @return array $provisions
     */
    public function getProvisions()
    {
        return $this->provisions;
    }

    /**
     * @param array $provisions
     */
    public function setProvisions($provisions)
    {
        $this->provisions = $provisions;
        return $this;
    }


    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        $obj_vars = get_object_vars($this);
        return $obj_vars;
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
    }

    public function setInputFilter (InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (! $this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function getIsActive()
    {
        return $this->isActive;
    }

    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
        return $this;
    }
}