<?php

/**
 * Json
 * Json controller for FavoriteList
 *
 * @copyright   Comvation AG
 * @author      Manuel Schenk <manuel.schenk@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_favoritelist
 * @version     5.0.0
 */

namespace Cx\Modules\FavoriteList\Controller;

/**
 * Json
 * Json controller for FavoriteList
 *
 * @copyright   Comvation AG
 * @author      Manuel Schenk <manuel.schenk@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_favoritelist
 * @version     5.0.0
 */
class JsonController extends \Cx\Core\Core\Model\Entity\Controller implements \Cx\Core\Json\JsonAdapter
{

    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName()
    {
        return parent::getName();
    }

    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods()
    {
        return array(
            'getCatalog',
            'addFavorite',
            'removeFavorite',
            'editFavoriteMessage',
        );
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString()
    {
        return implode('<br />', $this->messages);
    }

    /**
     * Returns default permission as object
     * @return Object
     */
    public function getDefaultPermissions()
    {
        return new \Cx\Core_Modules\Access\Model\Entity\Permission(
            null,
            null,
            false
        );
    }

    /**
     * get catalog by session
     *
     * @param   array   $data contains data from ajax request
     * @return  string  template as HTML generated by Sigma
     */
    public function getCatalog($data = array())
    {
        $lang = contrexx_input2raw($data['get']['lang']);
        $langId = \FWLanguage::getLanguageIdByCode($lang);
        $_ARRAYLANG = \Env::get('init')->getComponentSpecificLanguageData($this->getName(), true, $langId);

        $themeId = contrexx_input2raw($data['get']['themeId']);
        $theme = $this->getController('Frontend')->getTheme($themeId);
        $templateFile = $this->cx->getWebsiteThemesPath() . '/' . $theme->getFoldername() . '/' . strtolower($this->getName()) . '_block_list.html';
        $template = new \Cx\Core\Html\Sigma(dirname($templateFile));
        $template->loadTemplateFile(strtolower($this->getName()) . '_block_list.html');

        $em = $this->cx->getDb()->getEntityManager();
        $catalogRepo = $em->getRepository($this->getNamespace() . '\Model\Entity\Catalog');
        $catalog = $catalogRepo->findOneBy(array('sessionId' => $this->getComponent('Session')->getSession()->sessionid));

        if (empty($catalog)) {
            $template->setVariable(array(
                strtoupper($this->getName()) . '_BLOCK_LIST_MESSAGE_NO_CATALOG' => $_ARRAYLANG['TXT_' . strtoupper($this->getType()) . '_' . strtoupper($this->getName()) . '_MESSAGE_NO_CATALOG'],
            ));
            $template->parse(strtolower($this->getName()) . '_block_list_no_catalog');
        } else {
            $favorites = $catalog->getFavorites();
            $template->parse(strtolower($this->getName()) . '_block_list');
            if (!$favorites->count()) {
                $template->setVariable(array(
                    strtoupper($this->getName()) . '_BLOCK_LIST_MESSAGE_NO_ENTRIES' => $_ARRAYLANG['TXT_' . strtoupper($this->getType()) . '_' . strtoupper($this->getName()) . '_MESSAGE_NO_ENTRIES'],
                ));
                $template->parse(strtolower($this->getName()) . '_block_list_no_entries');
            } else {
                $totalPrice = 0;
                foreach ($favorites as $favorite) {
                    $template->setVariable(array(
                        strtoupper($this->getName()) . '_BLOCK_LIST_ENTITY' => 'favoriteListBlockListEntity',
                        strtoupper($this->getName()) . '_BLOCK_LIST_ID' => $favorite->getId(),
                        strtoupper($this->getName()) . '_BLOCK_LIST_NAME' => contrexx_raw2xhtml($favorite->getTitle()),
                        strtoupper($this->getName()) . '_BLOCK_LIST_DELETE_ACTION' => 'cx.favoriteListRemoveFavorite(' . $favorite->getId() . ');',
                        strtoupper($this->getName()) . '_BLOCK_LIST_EDIT_LINK' => \Cx\Core\Html\Controller\ViewGenerator::getVgEditUrl(
                            0,
                            $favorite->getId(),
                            \Cx\Core\Routing\Url::fromModuleAndCmd($this->getName())
                        ),
                        strtoupper($this->getName()) . '_BLOCK_LIST_MESSAGE' => contrexx_raw2xhtml($favorite->getMessage()),
                        strtoupper($this->getName()) . '_BLOCK_LIST_MESSAGE_NAME' => 'favoriteListBlockListEntityMessage',
                    ));
                    $template->parse(strtolower($this->getName()) . '_block_list_row');
                    $totalPrice += contrexx_raw2xhtml($favorite->getPrice());
                }
                $template->setVariable(array(
                    strtoupper($this->getName()) . '_BLOCK_SAVE_LABEL' => $_ARRAYLANG['TXT_' . strtoupper($this->getType()) . '_' . strtoupper($this->getName()) . '_BLOCK_SAVE'],
                    strtoupper($this->getName()) . '_BLOCK_SAVE_ACTION' => 'cx.favoriteListSave();',
                    strtoupper($this->getName()) . '_BLOCK_TOTAL_PRICE' => number_format($totalPrice, 2, '.', '\''),
                    strtoupper($this->getName()) . '_BLOCK_TOTAL_PRICE_LABEL' => $_ARRAYLANG['TXT_' . strtoupper($this->getType()) . '_' . strtoupper($this->getName()) . '_BLOCK_TOTAL_PRICE_LABEL'],
                ));
            }
        }

        return $template->get();
    }

    /**
     * adds Favorite to a Catalog
     *
     * @param   array   $data contains data from ajax request
     * @return  string  template as HTML generated by Sigma
     */
    public function addFavorite($data = array())
    {
        $lang = contrexx_input2raw($data['get']['lang']);
        $langId = \FWLanguage::getLanguageIdByCode($lang);
        $_ARRAYLANG = \Env::get('init')->getComponentSpecificLanguageData($this->getName(), true, $langId);

        $title = contrexx_input2db($data['get']['title']);
        if (empty($title)) {
            return;
        }

        $allowedAttributes = array(
            'title',
            'link',
            'description',
            'message',
            'price',
            'image1',
            'image2',
            'image3',
        );

        $attributes = array();
        foreach ($data['get'] as $key => $value) {
            if (in_array($key, $allowedAttributes)) {
                $attributes[$key] = contrexx_input2db($data['get'][$key]);
            }
        }

        $em = $this->cx->getDb()->getEntityManager();

        $catalogRepo = $em->getRepository($this->getNamespace() . '\Model\Entity\Catalog');
        $catalog = $catalogRepo->findOneBy(array('sessionId' => $this->getComponent('Session')->getSession()->sessionid));

        if (!$catalog) {
            $dateTimeNow = new \DateTime('now');
            $dateTimeNowFormat = $dateTimeNow->format('d.m.Y H:i:s');

            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $meta['ipaddress'] = contrexx_input2raw($_SERVER['HTTP_X_FORWARDED_FOR']);
            } else {
                $meta['ipaddress'] = contrexx_input2raw($_SERVER['REMOTE_ADDR']);
            }
            $meta['host'] = contrexx_input2raw(gethostbyaddr($meta['ipaddress']));
            $meta['lang'] = contrexx_input2raw($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $meta['browser'] = contrexx_input2raw($_SERVER['HTTP_USER_AGENT']);

            $catalog = new \Cx\Modules\FavoriteList\Model\Entity\Catalog();
            $catalog->setName($_ARRAYLANG['TXT_' . strtoupper($this->getType()) . '_' . strtoupper($this->getName())] . ' ' . $dateTimeNowFormat);
            $catalog->setMeta(serialize($meta));
            $catalog->setCounterMail(0);
            $catalog->setCounterPrint(0);
            $catalog->setCounterRecommendation(0);
            $catalog->setCounterInquiry(0);
            $em->persist($catalog);
        }

        $favorite = new \Cx\Modules\FavoriteList\Model\Entity\Favorite();
        $favorite->setCatalog($catalog);

        foreach ($attributes as $key => $value) {
            $favorite->{'set' . ucfirst($key)}($value);
        }

        $em->persist($favorite);
        $em->flush();
        $em->clear();

        if (isset($data['get']['lang'])) {
            return $this->getCatalog($data);
        }
    }

    /**
     * removes a Favorite from Catalog by id
     *
     * @param   array   $data contains data from ajax request
     * @return  string  template as HTML generated by Sigma
     */
    public function removeFavorite($data = array())
    {
        $id = contrexx_input2raw($data['get']['id']);
        if (!$id) {
            return;
        }

        $em = $this->cx->getDb()->getEntityManager();
        $catalogRepo = $em->getRepository($this->getNamespace() . '\Model\Entity\Catalog');
        $catalog = $catalogRepo->findOneBy(array('sessionId' => $this->getComponent('Session')->getSession()->sessionid));
        $favorite = $catalog->getFavorites()->filter(
            function ($favorite) use ($id) {
                return $favorite->getId() == $id;
            }
        )->first();
        if ($favorite) {
            $em->remove($favorite);
            $em->flush();
            $em->clear();

            if (isset($data['get']['lang'])) {
                return $this->getCatalog($data);
            }
        }
    }

    /**
     * edits the message of a favorite
     *
     * @param   array   $data contains data from ajax request
     * @return  string  template as HTML generated by Sigma
     */
    public function editFavoriteMessage($data = array())
    {
        $id = contrexx_input2db($data['get']['id']);
        $attribute = contrexx_input2db($data['get']['attribute']);
        if (empty($id) || empty($attribute)) {
            return;
        }

        $value = contrexx_input2db($data['get']['value']);

        $em = $this->cx->getDb()->getEntityManager();
        $catalogRepo = $em->getRepository($this->getNamespace() . '\Model\Entity\Catalog');
        $catalog = $catalogRepo->findOneBy(array('sessionId' => $this->getComponent('Session')->getSession()->sessionid));
        $favorite = $catalog->getFavorites()->filter(
            function ($favorite) use ($id) {
                return $favorite->getId() == $id;
            }
        )->first();

        $allowedAttributes = array(
            'title',
            'link',
            'description',
            'message',
            'price',
            'image1',
            'image2',
            'image3',
        );

        if (in_array($attribute, $allowedAttributes)) {
            $favorite->{'set' . ucfirst($attribute)}($value);

            $em->persist($favorite);
            $em->flush();
            $em->clear();

            if (isset($data['get']['lang'])) {
                return $this->getCatalog($data);
            }
        }
    }
}
