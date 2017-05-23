<?php

namespace Cx\Model\Proxies\__CG__\Cx\Core_Modules\LinkManager\Model\Entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class Link extends \Cx\Core_Modules\LinkManager\Model\Entity\Link implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array properties to be lazy loaded, with keys being the property
     *            names and values being their default values
     *
     * @see \Doctrine\Common\Persistence\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = array();



    /**
     * @param \Closure $initializer
     * @param \Closure $cloner
     */
    public function __construct($initializer = null, $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }







    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return array('__isInitialized__', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'id', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'lang', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'requestedPath', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'refererPath', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'leadPath', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'linkStatusCode', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'linkStatus', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'entryTitle', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'moduleName', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'moduleAction', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'moduleParams', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'detectedTime', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'flagStatus', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'updatedBy', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'linkRecheck', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'requestedLinkType', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'brokenLinkText');
        }

        return array('__isInitialized__', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'id', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'lang', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'requestedPath', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'refererPath', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'leadPath', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'linkStatusCode', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'linkStatus', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'entryTitle', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'moduleName', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'moduleAction', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'moduleParams', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'detectedTime', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'flagStatus', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'updatedBy', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'linkRecheck', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'requestedLinkType', '' . "\0" . 'Cx\\Core_Modules\\LinkManager\\Model\\Entity\\Link' . "\0" . 'brokenLinkText');
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (Link $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy->__getLazyProperties() as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', array());
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', array());
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return (int)  parent::getId();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getId', array());

        return parent::getId();
    }

    /**
     * {@inheritDoc}
     */
    public function setId($id)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setId', array($id));

        return parent::setId($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getLang()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLang', array());

        return parent::getLang();
    }

    /**
     * {@inheritDoc}
     */
    public function setLang($lang)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLang', array($lang));

        return parent::setLang($lang);
    }

    /**
     * {@inheritDoc}
     */
    public function getLinkStatusCode()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLinkStatusCode', array());

        return parent::getLinkStatusCode();
    }

    /**
     * {@inheritDoc}
     */
    public function setLinkStatusCode($linkStatusCode)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLinkStatusCode', array($linkStatusCode));

        return parent::setLinkStatusCode($linkStatusCode);
    }

    /**
     * {@inheritDoc}
     */
    public function getLinkStatus()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLinkStatus', array());

        return parent::getLinkStatus();
    }

    /**
     * {@inheritDoc}
     */
    public function setLinkStatus($linkStatus)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLinkStatus', array($linkStatus));

        return parent::setLinkStatus($linkStatus);
    }

    /**
     * {@inheritDoc}
     */
    public function getDetectedTime()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDetectedTime', array());

        return parent::getDetectedTime();
    }

    /**
     * {@inheritDoc}
     */
    public function updateDetectedTime()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'updateDetectedTime', array());

        return parent::updateDetectedTime();
    }

    /**
     * {@inheritDoc}
     */
    public function setDetectedTime($detectedTime)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDetectedTime', array($detectedTime));

        return parent::setDetectedTime($detectedTime);
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestedPath()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getRequestedPath', array());

        return parent::getRequestedPath();
    }

    /**
     * {@inheritDoc}
     */
    public function setRequestedPath($requestedPath)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setRequestedPath', array($requestedPath));

        return parent::setRequestedPath($requestedPath);
    }

    /**
     * {@inheritDoc}
     */
    public function getRefererPath()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getRefererPath', array());

        return parent::getRefererPath();
    }

    /**
     * {@inheritDoc}
     */
    public function setRefererPath($refererPath)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setRefererPath', array($refererPath));

        return parent::setRefererPath($refererPath);
    }

    /**
     * {@inheritDoc}
     */
    public function getLeadPath()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLeadPath', array());

        return parent::getLeadPath();
    }

    /**
     * {@inheritDoc}
     */
    public function setLeadPath($leadPath)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLeadPath', array($leadPath));

        return parent::setLeadPath($leadPath);
    }

    /**
     * {@inheritDoc}
     */
    public function getEntryTitle()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getEntryTitle', array());

        return parent::getEntryTitle();
    }

    /**
     * {@inheritDoc}
     */
    public function setEntryTitle($entryTitle)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setEntryTitle', array($entryTitle));

        return parent::setEntryTitle($entryTitle);
    }

    /**
     * {@inheritDoc}
     */
    public function getModuleName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getModuleName', array());

        return parent::getModuleName();
    }

    /**
     * {@inheritDoc}
     */
    public function setModuleName($moduleName)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setModuleName', array($moduleName));

        return parent::setModuleName($moduleName);
    }

    /**
     * {@inheritDoc}
     */
    public function getModuleAction()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getModuleAction', array());

        return parent::getModuleAction();
    }

    /**
     * {@inheritDoc}
     */
    public function setModuleAction($moduleAction)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setModuleAction', array($moduleAction));

        return parent::setModuleAction($moduleAction);
    }

    /**
     * {@inheritDoc}
     */
    public function getModuleParams()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getModuleParams', array());

        return parent::getModuleParams();
    }

    /**
     * {@inheritDoc}
     */
    public function setModuleParams($moduleParams)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setModuleParams', array($moduleParams));

        return parent::setModuleParams($moduleParams);
    }

    /**
     * {@inheritDoc}
     */
    public function getFlagStatus()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFlagStatus', array());

        return parent::getFlagStatus();
    }

    /**
     * {@inheritDoc}
     */
    public function setFlagStatus($flagStatus)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setFlagStatus', array($flagStatus));

        return parent::setFlagStatus($flagStatus);
    }

    /**
     * {@inheritDoc}
     */
    public function getLinkRecheck()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLinkRecheck', array());

        return parent::getLinkRecheck();
    }

    /**
     * {@inheritDoc}
     */
    public function setLinkRecheck($linkRecheck)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLinkRecheck', array($linkRecheck));

        return parent::setLinkRecheck($linkRecheck);
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedBy()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUpdatedBy', array());

        return parent::getUpdatedBy();
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedBy($updatedBy)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setUpdatedBy', array($updatedBy));

        return parent::setUpdatedBy($updatedBy);
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestedLinkType()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getRequestedLinkType', array());

        return parent::getRequestedLinkType();
    }

    /**
     * {@inheritDoc}
     */
    public function setRequestedLinkType($requestedLinkType)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setRequestedLinkType', array($requestedLinkType));

        return parent::setRequestedLinkType($requestedLinkType);
    }

    /**
     * {@inheritDoc}
     */
    public function getBrokenLinkText()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getBrokenLinkText', array());

        return parent::getBrokenLinkText();
    }

    /**
     * {@inheritDoc}
     */
    public function setBrokenLinkText($brokenLinkText)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setBrokenLinkText', array($brokenLinkText));

        return parent::setBrokenLinkText($brokenLinkText);
    }

    /**
     * {@inheritDoc}
     */
    public function updateFromArray($newData)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'updateFromArray', array($newData));

        return parent::updateFromArray($newData);
    }

}