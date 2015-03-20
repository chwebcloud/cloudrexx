<?php

namespace Cx\Model\Proxies;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class CxModulesOrderModelEntityOrderProxy extends \Cx\Modules\Order\Model\Entity\Order implements \Doctrine\ORM\Proxy\Proxy
{
    private $_entityPersister;
    private $_identifier;
    public $__isInitialized__ = false;
    public function __construct($entityPersister, $identifier)
    {
        $this->_entityPersister = $entityPersister;
        $this->_identifier = $identifier;
    }
    private function _load()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;
            if ($this->_entityPersister->load($this->_identifier, $this) === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            unset($this->_entityPersister, $this->_identifier);
        }
    }

    
    public function getId()
    {
        $this->_load();
        return parent::getId();
    }

    public function setId($id)
    {
        $this->_load();
        return parent::setId($id);
    }

    public function setContactId($contactId)
    {
        $this->_load();
        return parent::setContactId($contactId);
    }

    public function getContactId()
    {
        $this->_load();
        return parent::getContactId();
    }

    public function setCurrency(\Cx\Modules\Crm\Model\Entity\Currency $currency)
    {
        $this->_load();
        return parent::setCurrency($currency);
    }

    public function getCurrency()
    {
        $this->_load();
        return parent::getCurrency();
    }

    public function addSubscription(\Cx\Modules\Order\Model\Entity\Subscription $subscription)
    {
        $this->_load();
        return parent::addSubscription($subscription);
    }

    public function getSubscriptions()
    {
        $this->_load();
        return parent::getSubscriptions();
    }

    public function setSubscriptions($subscriptions)
    {
        $this->_load();
        return parent::setSubscriptions($subscriptions);
    }

    public function createSubscription($product, $subscriptionOptions)
    {
        $this->_load();
        return parent::createSubscription($product, $subscriptionOptions);
    }

    public function getInvoices()
    {
        $this->_load();
        return parent::getInvoices();
    }

    public function setInvoices($invoices)
    {
        $this->_load();
        return parent::setInvoices($invoices);
    }

    public function addInvoice(\Cx\Modules\Order\Model\Entity\Invoice $invoice)
    {
        $this->_load();
        return parent::addInvoice($invoice);
    }

    public function complete()
    {
        $this->_load();
        return parent::complete();
    }

    public function billSubscriptions()
    {
        $this->_load();
        return parent::billSubscriptions();
    }

    public function getUnpaidInvoices()
    {
        $this->_load();
        return parent::getUnpaidInvoices();
    }

    public function __get($name)
    {
        $this->_load();
        return parent::__get($name);
    }

    public function getComponentController()
    {
        $this->_load();
        return parent::getComponentController();
    }

    public function setVirtual($virtual)
    {
        $this->_load();
        return parent::setVirtual($virtual);
    }

    public function isVirtual()
    {
        $this->_load();
        return parent::isVirtual();
    }

    public function validate()
    {
        $this->_load();
        return parent::validate();
    }

    public function __toString()
    {
        $this->_load();
        return parent::__toString();
    }


    public function __sleep()
    {
        return array('__isInitialized__', 'id', 'contactId', 'subscriptions', 'invoices', 'currency');
    }

    public function __clone()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;
            $class = $this->_entityPersister->getClassMetadata();
            $original = $this->_entityPersister->load($this->_identifier);
            if ($original === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            foreach ($class->reflFields AS $field => $reflProperty) {
                $reflProperty->setValue($this, $reflProperty->getValue($original));
            }
            unset($this->_entityPersister, $this->_identifier);
        }
        
    }
}