<?php

namespace Cx\Core\Routing\Model\Entity;

/**
 * Exception from within this file
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Michael Ritter <drissg@gmail.com>
 * @version     1.0.0
 * @package     cloudrexx
 * @subpackage  core_routing
 */
class UrlException extends \Cx\Lib\Net\Model\Entity\UrlException {}

/**
 * Represents a Cloudrexx URL (knows about internal/external, frontend/backend/command, forces protocol, ports)
 * @todo add missing docblocks
 * @todo handle get and post params (contrexx_input2raw())
 * Future resolving process:
 * // to get mode:
 * $request = \Cx\Core\Routing\Model\Entity\Request::fromCurrent();
 * $url = $request->getUrl();
 * $cx->mode = $url->getMode();
 * 
 * // resolving:
 * switch ($cx->mode) {
 *     case 'frontend':
 *         $page = $url->getPage(); // could be any type of page including alias
 *         $isAdjusting = true;
 *         while ($isAdjusting) {
 *             $isAdjusting = false;
 *             if (external redirect || !$page) { // type redirect (internal or external)
 *                 $isAdjusting = false;
 *                 redirect to $page->getTargetPage()!
 *             }
 *             if (internal redirect) { // types symlink and fallback
 *                 $page = $page->getTargetPage();
 *             }
 *         }
 *         break;
 *     case 'command':
 *     case 'backend':
 *         $url->getComponent()
 *         $url->getArguments()
 * }
 */
abstract class Url extends \Cx\Lib\Net\Model\Entity\Url {
    
    public static function fromString($stringUrl) {
        return static::fromUrl(new \Cx\Lib\Net\Model\Entity\Url($stringUrl));
    }
    
    public static function fromUrl($url) {
        try {
            switch (static::calculateMode($url)) {
                case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                    return new FrontendUrl($url); // resolving (incl. aliases), virtual language dirs and can be generated from pages and so
                    break;
                case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                    return new BackendUrl($url); // can be generated from component backend commands
                    break;
                case \Cx\Core\Core\Controller\Cx::MODE_COMMAND:
                    return new CommandUrl($url); // can be generated from datasource/-access and component commands
                    break;
                default;
                    throw new UrlException('Unknown Url mode');
                    break;
            }
            
        // external url
        } catch (UrlException $e) {
            return $url;
        }
    }
    
    /**
     * Returns an Url object for module, cmd and lang
     * @todo There could be more than one page using the same module and cmd per lang
     * @param string $module Module name
     * @param string $cmd (optional) Module command, default is empty string
     * @param int $lang (optional) Language to use, default is FRONTENT_LANG_ID
     * @param array $parameters (optional) HTTP GET parameters to append
     * @param string $scheme (optional) The scheme to use
     * @param boolean $returnErrorPageOnError (optional) If set to TRUE, this method will return an URL object that point to the error page of Cloudrexx. Defaults to TRUE.
     * @return \Cx\Core\Routing\Model\Entity\Url Url object for the supplied module, cmd and lang
     */
    public static function fromModuleAndCmd($module, $cmd = '', $lang = '', $parameters = array(), $scheme = '', $returnErrorPageOnError = true) {
        return \Cx\Core\Routing\Model\Entity\FrontendUrl::fromModuleAndCmd(
            $module,
            $cmd,
            $lang,
            $parameters,
            $scheme,
            $returnErrorPageOnError
        );
    }
    
    /**
     * Returns an Url object pointing to the documentRoot of the website
     * @param int $lang (optional) Language to use, default is FRONTEND_LANG_ID
     * @param string $scheme (optional) The protocol to use
     * @return \Cx\Core\Routing\Model\Entity\Url Url object for the documentRoot of the website
     */
    public static function fromDocumentRoot($arrParameters = array(), $lang = '', $scheme = '') {
        return \Cx\Core\Routing\Model\Entity\FrontendUrl::fromModuleAndCmd(
            $arrParameters,
            $lang,
            $scheme
        );
    }
    
    /**
     * Returns the URL object for a page
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Page to get the URL to
     * @param array $parameters (optional) HTTP GET parameters to append
     * @param string $protocol (optional) The protocol to use
     * @return \Cx\Core\Routing\Model\Entity\Url Url object for the supplied page
     */
    public static function fromPage($page, $parameters = array(), $scheme = '') {
        return \Cx\Core\Routing\Model\Entity\FrontendUrl::fromModuleAndCmd(
            $page,
            $parameters,
            $scheme
        );
    }
    
    /**
     * Tells the mode for an internal Url
     * @param \Cx\Lib\Net\Model\Entity\Url Internal Url
     * @throws UrlException if Url is not internal
     * @return string One of the modes defined in Cx class
     */
    protected static function calculateMode($internalUrl) {
        // commmand line is always command mode
        if (php_sapi_name() == 'cli') {
            return \Cx\Core\Core\Controller\Cx::MODE_COMMAND;
        }
        
        // sort out externals
        if (!static::isInternal($internalUrl)) {
            throw new UrlException('Not an internal Url');
        }
        
        $cx = \Cx\Core\Core\Controller\Cx::instanciate();
        $installationOffsetParts = explode('/', $cx->getWebsiteOffsetPath());
        $firstNonOffsetPartIndex = count($installationOffsetParts);
        $path = $internalUrl->getPathParts();
        $firstPathPart = '/';
        if (isset($path[$firstNonOffsetPartIndex])) {
            $firstPathPart .= $path[$firstNonOffsetPartIndex];
        }
        switch ($firstPathPart) {
            case \Cx\Core\Core\Controller\Cx::FOLDER_NAME_COMMAND_MODE:
                return \Cx\Core\Core\Controller\Cx::MODE_COMMAND;
                break;
            case 'admin':
            case \Cx\Core\Core\Controller\Cx::FOLDER_NAME_BACKEND:
                return \Cx\Core\Core\Controller\Cx::MODE_BACKEND;
                break;
            default:
                return \Cx\Core\Core\Controller\Cx::MODE_FRONTEND;
                break;
        }
    }
    
    public function getMode() {
        return static::calculateMode($this);
    }
    
    public function getPathWithoutOffset() {
        $installationOffset = $this->cx->getWebsiteOffsetPath();
        $providedOffset = $this->getPath();
        return substr(
            $providedOffset,
            strlen($installationOffset)
        );
    }
    
    /**
     * Tells wheter the given Url points to this CLX installation
     * @param \Cx\Lib\Net\Model\Entity\Url Url
     * @return boolean True if Url is internal, false otherwise
     */
    public static function isInternal($url) {
        // check domain
        $domainRepo = new \Cx\Core\Net\Model\Repository\DomainRepository();
        if (!$domainRepo->findOneBy(array('name' => $url->getHost()))) {
            return false;
        }
        
        // check offset
        $cx = \Cx\Core\Core\Controller\Cx::instanciate();
        $installationOffset = $cx->getWebsiteOffsetPath();
        $providedOffset = $url->getPath();
        if (
            $installationOffset != substr(
                $providedOffset,
                0,
                strlen($installationOffset)
            )
        ) {
            return false;
        }
        return true;
    }
}
