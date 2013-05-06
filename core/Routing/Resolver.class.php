<?php

/**
 * Resolver
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_routing
 */

namespace Cx\Core\Routing;

/**
 * ResolverException
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_routing
 */
class ResolverException extends \Exception {};

/**
 * Takes an URL and tries to find the Page.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core_routing
 */
class Resolver {
    protected $em = null;
    protected $url = null;
    /**
     * language id.
     * @var integer
     */
    protected $lang = null;

    /**
     * the page we found.
     * @var Cx\Core\ContentManager\Model\Entity\Page
     */
    protected $page = null;

    /**
     * Doctrine PageRepository
     */
    protected $pageRepo = null;

    /**
     * Doctrine NodeRepository
     */
    protected $nodeRepo = null;

    /**
     * Remembers if we've come across a redirection while resolving the URL.
     * This allow to properly redirect via 302.
     * @var boolean
     */
    protected $isRedirection = false;

    /**
     * Maps language ids to fallback language ids.
     * @var array ($languageId => $fallbackLanguageId)
     */
    protected $fallbackLanguages = null;

    /**
     * Contains the resolved module name (if any, empty string if none)
     * @var String
     */
    protected $section = '';

    /**
     * Contains the resolved module command (if any, empty string if none)
     * @var String
     */
    protected $command = '';

    /**
     * Remembers if it's a page preview.
     * @var boolean
     */
    protected $pagePreview = 0;

    /**
     * Contains the history id to revert the page to an older version.
     * @var int
     */
    protected $historyId = 0;

    /**
     * Contains the page array from the session.
     * @var array
     */
    protected $sessionPage = array();
    protected $path;

    /**
     * @param Url $url the url to resolve
     * @param integer $lang the language Id
     * @param $entityManager
     * @param string $pathOffset ASCMS_PATH_OFFSET
     * @param array $fallbackLanguages (languageId => fallbackLanguageId)
     * @param boolean $forceInternalRedirection does not redirect by 302 for internal redirections if set to true.
     *                this is used mainly for testing currently.
     *                IMPORTANT: Do insert new parameters before this one if you need to and correct the tests.
     */
    public function __construct($url, $lang, $entityManager, $pathOffset, $fallbackLanguages, $forceInternalRedirection=false) {
        $this->init($url, $lang, $entityManager, $pathOffset, $fallbackLanguages, $forceInternalRedirection);
    }


    /**
     * @param Url $url the url to resolve
     * @param integer $lang the language Id
     * @param $entityManager
     * @param string $pathOffset ASCMS_PATH_OFFSET
     * @param array $fallbackLanguages (languageId => fallbackLanguageId)
     * @param boolean $forceInternalRedirection does not redirect by 302 for internal redirections if set to true.
     *                this is used mainly for testing currently.
     *                IMPORTANT: Do insert new parameters before this one if you need to and correct the tests.
     */
    public function init($url, $lang, $entityManager, $pathOffset, $fallbackLanguages, $forceInternalRedirection=false) {
        $this->url = $url;
        $this->em = $entityManager;
        $this->lang = $lang;
        $this->pathOffset = $pathOffset;
        $this->pageRepo = $this->em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
        $this->nodeRepo = $this->em->getRepository('Cx\Core\ContentManager\Model\Entity\Node');
        $this->logRepo  = $this->em->getRepository('Cx\Core\ContentManager\Model\Entity\LogEntry');
        $this->forceInternalRedirection = $forceInternalRedirection;
        $this->fallbackLanguages = $fallbackLanguages;
        $this->pagePreview = !empty($_GET['pagePreview']) && ($_GET['pagePreview'] == 1) ? 1 : 0;
        $this->historyId = !empty($_GET['history']) ? $_GET['history'] : 0;
        $this->sessionPage = !empty($_SESSION['page']) ? $_SESSION['page'] : array();
    }

    /**
     * Checks for alias request
     * @return Page or null
     */
    public function resolveAlias() {
        // This is our alias, if any
        $path = $this->url->getSuggestedTargetPath();
        $this->path = $path;

        //(I) see what the model has for us, aliases only.
        $result = $this->pageRepo->getPagesAtPath($path, null, null, false, \Cx\Core\ContentManager\Model\Repository\PageRepository::SEARCH_MODE_ALIAS_ONLY);

        //(II) sort out errors
        if(!$result) {
            // no alias
            return null;
        }

        if(!$result['page']) {
            // no alias
            return null;
        }
        if (count($result['page']) != 1) {
            throw new ResolverException('Unable to match a single page for this alias (tried path ' . $path . ').');
        }
        $page = current($result['page']);
        if (!$page->isActive()) {
            throw new ResolverException('Alias found, but it is not active.');
        }
        
        $langDir = $this->url->getLangDir();
        if (!empty($langDir) && $this->pageRepo->getPagesAtPath($langDir.'/'.$path, null, FRONTEND_LANG_ID, false, \Cx\Core\ContentManager\Model\Repository\PageRepository::SEARCH_MODE_PAGES_ONLY)) {
            return null;
        }

        $this->page = $page;
        
        $params = $this->url->getParamArray();
        if (
            (isset($params['external']) && $params['external'] == 'permanent') ||
            ($this->page->isTargetInternal() && preg_match('/[?&]external=permanent/', $this->page->getTarget()))
        ) {
            if ($this->page->isTargetInternal()) {
                $params = array();
                if (trim($this->page->getTargetQueryString()) != '') {
                    $params = explode('&', $this->page->getTargetQueryString());
                }
                $target = \Cx\Core\Routing\Url::fromNodeId($this->page->getTargetNodeId(), $this->page->getTargetLangId(), $params);
            } else {
                $target = $this->page->getTarget();
            }
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $target);
            header('Connection: close');
            exit;
        }

        return $this->page;
    }

    /**
     * Does the resolving work, extends $this->url with targetPath and params.
     */
    public function resolve($internal = false) {
        // Abort here in case we're handling a legacy request.
        // The legacy request will be handled by $this->legacyResolve().
        // Important: We must check for $internal == FALSE here, to abort the resolving process
        //            only when we're resolving the initial (original) request.
        //            Internal resolving requests might be called by $this->legacyResolve()
        //            and shall therefore be processed.
        if (!$internal && isset($_REQUEST['section'])) {
            throw new ResolverException('Legacy request');
        }

        $path = $this->url->getSuggestedTargetPath();

        if (!$this->page || $internal) {
            if ($this->pagePreview) {
                if (!empty($this->sessionPage)) {
                    $this->getPreviewPage();
                }
            }

            //(I) see what the model has for us
            $result = $this->pageRepo->getPagesAtPath($this->url->getLangDir().'/'.$path, null, $this->lang, false, \Cx\Core\ContentManager\Model\Repository\PageRepository::SEARCH_MODE_PAGES_ONLY);
            if ($this->pagePreview) {
                if (empty($this->sessionPage)) {
                    if (\Permission::checkAccess(6, 'static', true)) {
                        $result['page']->setActive(true);
                        $result['page']->setDisplay(true);
                        if (($result['page']->getEditingStatus() == 'hasDraft') || (($result['page']->getEditingStatus() == 'hasDraftWaiting'))) {
                            $logEntries = $this->logRepo->getLogEntries($result['page']);
                            $this->logRepo->revert($result['page'], $logEntries[1]->getVersion());
                        }
                    }
                }
            }

            //(II) sort out errors
            if(!$result) {
                throw new ResolverException('Unable to locate page (tried path ' . $path .').');
            }

            if(!$result['page']) {
                throw new ResolverException('Unable to locate page for this language. (tried path ' . $path .').');
            }

            if (!$result['page']->isActive()) {
                throw new ResolverException('Page found, but it is not active.');
            }

            // if user has no rights to see this page, we redirect to login
            $this->checkPageFrontendProtection($result['page']);

            // If an older revision was requested, revert to that in-place:
            if (!empty($this->historyId) && \Permission::checkAccess(6, 'static', true)) {
                $this->logRepo->revert($result['page'], $this->historyId);
            }

            //(III) extend our url object with matched path / params
            $this->url->setTargetPath($result['matchedPath'].$result['unmatchedPath']);
            $this->url->setParams($this->url->getSuggestedParams());

            $this->page = $result['page'];
        }
        /*
          the page we found could be a redirection.
          in this case, the URL object is overwritten with the target details and
          resolving starts over again.
         */
        $target = $this->page->getTarget();
        $isRedirection = $this->page->getType() == \Cx\Core\ContentManager\Model\Entity\Page::TYPE_REDIRECT;
        $isAlias = $this->page->getType() == \Cx\Core\ContentManager\Model\Entity\Page::TYPE_ALIAS;

        //handles alias redirections internal / disables external redirection
        $this->forceInternalRedirection = $this->forceInternalRedirection || $isAlias;

        if($target && ($isRedirection || $isAlias)) {
            // Check if page is a internal redirection and if so handle it
            if($this->page->isTargetInternal()) {
//TODO: add check for endless/circular redirection (a -> b -> a -> b ... and more complex)
                $nId = $this->page->getTargetNodeId();
                $lId = $this->page->getTargetLangId();
                $module = $this->page->getTargetModule();
                $cmd = $this->page->getTargetCmd();
                $qs = $this->page->getTargetQueryString();

                $langId = $lId ? $lId : $this->lang;

                // try to find the redirection target page
                if ($nId) {
                    $targetPage = $this->pageRepo->findOneBy(array('node' => $nId, 'lang' => $langId));

                    // revert to default language if we could not retrieve the specified langauge by the redirection.
                    // so lets try to load the redirection of the current language
                    if(!$targetPage) {
                        if($langId != 0) { //make sure we weren't already retrieving the default language
                            $targetPage = $this->pageRepo->findOneBy(array('node' => $nId, 'lang' => $this->lang));
                            $langId = $this->lang;
                        }
                    }
                } else {
                    $targetPage = $this->pageRepo->findOneByModuleCmdLang($module, $cmd, $langId);

                    // in case we were unable to find the requested page, this could mean that we are
                    // trying to retrieve a module page that uses a string with an ID (STRING_ID) as CMD.
                    // therefore, lets try to find the module by using the string in $cmd and INT in $langId as CMD.
                    // in case $langId is really the requested CMD then we will have to set the
                    // resolved language back to our original language $this->lang.
                    if (!$targetPage) {
                        $targetPage = $this->pageRepo->findOneBymoduleCmdLang($module, $cmd.'_'.$langId, $this->lang);
                        if ($targetPage) {
                            $langId = $this->lang;
                        }
                    }

                    // try to retrieve a module page that uses only an ID as CMD.
                    // lets try to find the module by using the INT in $langId as CMD.
                    // in case $langId is really the requested CMD then we will have to set the
                    // resolved language back to our original language $this->lang.
                    if (!$targetPage) {
                        $targetPage = $this->pageRepo->findOneByModuleCmdLang($module, $langId, $this->lang);
                        $langId = $this->lang;
                    }

                    // revert to default language if we could not retrieve the specified langauge by the redirection.
                    // so lets try to load the redirection of the current language
                    if (!$targetPage) {
                        if ($langId != 0) { //make sure we weren't already retrieving the default language
                            $targetPage = $this->pageRepo->findOneByModuleCmdLang($module, $cmd, $this->lang);
                            $langId = $this->lang;
                        }
                    }
                }

                //check whether we have a page now.
                if (!$targetPage) {
                    $this->page = null;
                    return;
                }

                // the redirection page is located within a different language.
                // therefore, we must set $this->lang to the target's language of the redirection.
                // this is required because we will next try to resolve the redirection target
                if ($langId != $this->lang) {
                    $this->lang = $langId;
                    $this->url->setLangDir(\FWLanguage::getLanguageCodeById($langId));
                    $this->pathOffset = ASCMS_INSTANCE_OFFSET.'/'.\FWLanguage::getLanguageCodeById($langId);
                }

                $targetPath = substr($targetPage->getPath(), 1);

                $this->url->setTargetPath($targetPath.$qs);
                $this->url->setPath($targetPath.$qs);
                $this->isRedirection = true;
                $this->resolve(true);
            } else { //external target - redirect via HTTP 302
                if (\FWValidator::isUri($target)) {
                    header('Location: '.$target);
                    exit;
                } else {
                    if ($target[0] == '/') {
                        $target = substr($target, 1);
                    }
                    header('Location: '.ASCMS_INSTANCE_OFFSET.'/'.\FWLanguage::getLanguageCodeById($this->lang).'/'.$target);
                    exit;
                }
            }
        }

        //if we followed one or more redirections, the user shall be redirected by 302.
        if ($this->isRedirection && !$this->forceInternalRedirection) {
            $params = $this->url->getSuggestedParams();
            header('Location: '.$this->page->getURL($this->pathOffset, $params));
            exit;
        }

        // in case the requested page is of type fallback, we will now handle/load this page
        $this->handleFallbackContent($this->page, !$internal);

        // set legacy <section> and <cmd> in case the requested page is an application
        if ($this->page->getType() == \Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION
                || $this->page->getType() == \Cx\Core\ContentManager\Model\Entity\Page::TYPE_FALLBACK) {
            $this->command = $this->page->getCmd();
            $this->section = $this->page->getModule();
        }
    }

    public function legacyResolve($url, $section, $command)
    {
        global $sessionObj;

        $objFWUser = \FWUser::getFWUserObject();

        /*
          The Resolver couldn't find a page.
          We're looking at one of the following situations, which are treated in the listed order:
           a) Request for the 'home' page
           b) Legacy request with section / cmd
           c) Request for inexistant page
          We try to locate a module page via cmd and section (if provided).
          If that doesn't work, an error is shown.
        */

        // a: 'home' page
        $urlPointsToHome =    $url->getSuggestedTargetPath() == 'index.php'
                           || $url->getSuggestedTargetPath() == '';
        //    user probably tried requesting the home-page
        if(!$section && $urlPointsToHome) {
            $section = 'home';
        }
        $this->setSection($section, $command);

        // b(, a): fallback if section and cmd are specified
        if ($section) {
            if ($section == 'logout') {
                if (empty($sessionObj)) {
                    $sessionObj = new \cmsSession();
                }
                if ($objFWUser->objUser->login()) {
                    $objFWUser->logout();
                }
            }

            $pageRepo = \Env::em()->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
            $this->page = $pageRepo->findOneByModuleCmdLang($section, $command, FRONTEND_LANG_ID);

            //fallback content
            if (!$this->page) {
                return;
            }

            $this->checkPageFrontendProtection($this->page);

            $this->handleFallbackContent($this->page);
        }

        // c: inexistant page gets catched below.
    }

    /**
     * Returns the preview page built from the session page array.
     * @return Cx\Core\ContentManager\Model\Entity\Page $page
     */
    private function getPreviewPage() {
        $data = $this->sessionPage;

        $page = $this->pageRepo->findOneById($data['pageId']);
        if (!$page) {
            $page = new \Cx\Core\ContentManager\Model\Entity\Page();
            $node = new \Cx\Core\ContentManager\Model\Entity\Node();
            $node->setParent($this->nodeRepo->getRoot());
            $node->setLvl(1);
            $this->nodeRepo->getRoot()->addChildren($node);
            $node->addPage($page);
            $page->setNode($node);

            $this->pageRepo->addVirtualPage($page);
        }

        unset($data['pageId']);
        $page->setLang(\FWLanguage::getLanguageIdByCode($data['lang']));
        unset($data['lang']);
        $page->updateFromArray($data);
        $page->setUpdatedAtToNow();
        $page->setActive(true);
        $page->setVirtual(true);
        $page->validate();

        return $page;
    }

    /**
     * Checks whether $page is of type 'fallback'. Loads fallback content if yes.
     * @param Cx\Core\ContentManager\Model\Doctrine $page
     * @param boolean $requestedPage Set to TRUE (default) if the $page passed by $page is the first resolved page (actual requested page)
     * @throws ResolverException
     */
    public function handleFallbackContent($page, $requestedPage = true) {
        //handle untranslated pages - replace them by the right language version.
        if($page->getType() == \Cx\Core\ContentManager\Model\Entity\Page::TYPE_FALLBACK) {
            // in case the first resolved page (= original requested page) is a fallback page
            // we must check here if this very page is active.
            // If we miss this check, we would only check if the referenced fallback page is active!
            if ($requestedPage && !$page->isActive()) {
                return;
            }

            // if this page is protected, we do not follow fallback
            $this->checkPageFrontendProtection($page);

            $fallbackPage = $this->getFallbackPage($page);

            // due that the fallback is located within a different language
            // we must set $this->lang to the fallback's language.
            // this is required because we will next try to resolve the page
            // that is referenced by the fallback page
            $this->lang = $fallbackPage->getLang();
            $this->url->setLangDir(\FWLanguage::getLanguageCodeById($this->lang));
            $this->url->setSuggestedTargetPath(substr($fallbackPage->getPath(), 1));

            // now lets resolve the page that is referenced by our fallback page
            $this->resolve(true);
            $page->getFallbackContentFrom($this->page);
            $this->page = $page;
        }
    }

    public function getFallbackPage($page) {
        $fallbackPage = null;
        if (isset($this->fallbackLanguages[$page->getLang()])) {
            $langId = $this->fallbackLanguages[$page->getLang()];
            $fallbackPage = $page->getNode()->getPage($langId);
        }
        if (!$fallbackPage) {
            throw new ResolverException('Followed fallback page, but couldn\'t find content of fallback Language');
        }
        return $fallbackPage;
    }

    /**
     * Checks if this page can be displayed in frontend, redirects to login of not
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Page to check
     * @param int $history (optional) Revision of page to use, 0 means current, default 0
     */
    public function checkPageFrontendProtection($page, $history = 0) {
        global $sessionObj;

        $page_protected = $page->isFrontendProtected();
        $pageAccessId = $page->getFrontendAccessId();
        if ($history) {
            $pageAccessId = $page->getBackendAccessId();
        }

        // login pages are unprotected by design
        $checkLogin = array($page);
        while (count($checkLogin)) {
            $currentPage = array_pop($checkLogin);
            if ($currentPage->getType() == \Cx\Core\ContentManager\Model\Entity\Page::TYPE_FALLBACK) {
                try {
                    array_push($checkLogin, $this->getFallbackPage($currentPage));
                } catch (ResolverException $e) {}
            }
            if ($currentPage->getModule() == 'login') {
                return;
            }
        }

        // Authentification for protected pages
        if (   (   $page_protected
                || $history
                || !empty($_COOKIE['PHPSESSID']))
            && (   !isset($_REQUEST['section'])
                || $_REQUEST['section'] != 'login')
        ) {
            if (empty($sessionObj)) $sessionObj = new \cmsSession();
            $sessionObj->cmsSessionStatusUpdate('frontend');
            if (\FWUser::getFWUserObject()->objUser->login()) {
                if ($page_protected) {
                    if (!\Permission::checkAccess($pageAccessId, 'dynamic', true)) {
                        $link=base64_encode(CONTREXX_SCRIPT_PATH.'?'.$_SERVER['QUERY_STRING']);
                        \CSRF::header('Location: '.\Cx\Core\Routing\Url::fromModuleAndCmd('login', 'noaccess', '', array('redirect' => $link)));
                        exit;
                    }
                }
                if ($history && !\Permission::checkAccess(78, 'static', true)) {
                    $link=base64_encode(CONTREXX_SCRIPT_PATH.'?'.$_SERVER['QUERY_STRING']);
                    \CSRF::header('Location: '.\Cx\Core\Routing\Url::fromModuleAndCmd('login', 'noaccess', '', array('redirect' => $link)));
                    exit;
                }
            } elseif (!empty($_COOKIE['PHPSESSID']) && !$page_protected) {
                unset($_COOKIE['PHPSESSID']);
            } else {
                if (isset($_GET['redirect'])) {
                    $link = $_GET['redirect'];
                } else {
                    $link=base64_encode(CONTREXX_SCRIPT_PATH.'?'.$_SERVER['QUERY_STRING']);
                }
                \CSRF::header('Location: '.\Cx\Core\Routing\Url::fromModuleAndCmd('login', '', '', array('redirect' => $link)));
                exit;
            }
        }
    }

    public function getPage() {
        return $this->page;
    }

    public function getURL() {
        return $this->url;
    }

    /**
     * Returns the resolved module name (if any, empty string if none)
     * @return String Module name
     */
    public function getSection() {
        return $this->section;
    }

    /**
     * Returns the resolved module command (if any, empty string if none)
     * @return String Module command
     */
    public function getCmd() {
        return $this->command;
    }

    /**
     * Sets the value of the resolved module name and command
     * This should not be called from any (core_)module!
     * For legacy requests only!
     *
     * @param String $section Module name
     * @param String $cmd Module command
     * @todo Remove this method as soon as legacy request are no longer possible
     */
    public function setSection($section, $command = '') {
        $this->section = $section;
        $this->command = $command;
    }
}
