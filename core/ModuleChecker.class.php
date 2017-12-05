<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Module Checker
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @version     2.0.0
 * @package     cloudrexx
 * @subpackage  core
 */

namespace Cx\Core;

/**
 * Module Checker
 * Checks for installed and activated modules
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @version     2.0.0
 * @package     cloudrexx
 * @subpackage  core
 */
class ModuleChecker {

    /**
     * Entity Manager
     *
     * @access  protected
     * @var     EntityManager
     */
    protected $em = null;

    /**
     * Database
     *
     * @access  protected
     * @var     ADONewConnection
     */
    protected $db = null;

    /**
     * ClassLoader
     *
     * @access  protected
     * @var     \Cx\Core\ClassLoader\ClassLoader
     */
    protected $cl = null;

    /**
     * Names of all core modules
     *
     * @access  protected
     * @var     array
     */
    protected $arrCoreModules = array();

    /**
     * Names of all modules (except core modules)
     *
     * @access  protected
     * @var     array
     */
    protected $arrModules = array();

    /**
     * Names of active modules
     *
     * @access  protected
     * @var     array
     */
    protected $arrActiveModules = array();

    /**
     * Names of installed modules
     *
     * @access  protected
     * @var     array
     */
    protected $arrInstalledModules = array();

    /**
     * Sets all modules activated
     *
     * @var boolean
     */
    protected $allActivated = false;

    protected static $instance = null;

    public static function getInstance($em, $db, $cl) {
        if (!static::$instance) {
            static::$instance = new static($em, $db, $cl);
        }
        return static::$instance;
    }

    /**
     * Constructor
     *
     * @access  public
     * @param   EntityManager                     $em
     * @param   ADONewConnection                  $db
     * @param   \Cx\Core\ClassLoader\ClassLoader  $cl
     */
    protected function __construct($em, $db, $cl) {
        $this->em = $em;
        $this->db = $db;
        $this->cl = $cl;

        $this->init();
    }

    /**
     * Sets all modules as activated (as if they would have content pages)
     * Only use in preComponentLoad or earlier!
     */
    public function setAllActivated() {
        $this->allActivated = true;
    }

    /**
     * Initialisation
     *
     * @access  protected
     */
    protected function init() {
        if (!$this->allActivated()) {
            // check the content for installed and used modules
            $arrCmActiveModules = array();
            $arrCmInstalledModules = array();
            $qb = $this->em->createQueryBuilder();
            $qb->add('select', 'p')
                ->add('from', 'Cx\Core\ContentManager\Model\Entity\Page p')
                ->add('where',
                    // TODO: what is the proper syntax for non-empty values?
                    // TODO: add additional check for module != NULL
                    $qb->expr()->neq('p.module', $qb->expr()->literal(''))
                );
            $pages = $qb->getQuery()->getResult();
            foreach ($pages as $page) {
                $arrCmInstalledModules[] = $page->getModule();
                if ($page->isActive()) {
                    $arrCmActiveModules[] = $page->getModule();
                }
            }

            $arrCmInstalledModules = array_unique($arrCmInstalledModules);
            $arrCmActiveModules = array_unique($arrCmActiveModules);

            // add static modules
            $arrCmInstalledModules[] = 'Block';
            $arrCmInstalledModules[] = 'Crm';
            $arrCmInstalledModules[] = 'Order';
            $arrCmInstalledModules[] = 'Pim';
            $arrCmInstalledModules[] = 'Support';
            $arrCmActiveModules[] = 'Block';
            $arrCmInstalledModules[] = 'upload';
            $arrCmActiveModules[] = 'upload';
        }

        $objResult = $this->db->Execute('
            SELECT
                `name`,
                `is_core`,
                `is_required`
            FROM
                `'.DBPREFIX.'modules`
        ');
        if ($objResult === false) {
            return;
        }
        while (!$objResult->EOF) {
            $moduleName = $objResult->fields['name'];

            if (empty($moduleName)) {
                $objResult->MoveNext();
                continue;
            }

            if ($moduleName == 'News') {
                $this->arrModules[] = $moduleName;
                //$this->arrCoreModules[] = $moduleName;
                if (
                    $this->allActivated ||
                    in_array($moduleName, $arrCmInstalledModules)
                ) {
                    $this->arrInstalledModules[] = $moduleName;
                    if (
                        $this->allActivated ||
                        in_array($moduleName, $arrCmInstalledModules)
                    ) {
                        $this->arrActiveModules[] = $moduleName;
                    }
                }
                $objResult->MoveNext();
                continue;
            }

            $isCore = $objResult->fields['is_core'];

            if ($isCore == 1) {
                $this->arrCoreModules[] = $moduleName;
            } else {
                $this->arrModules[] = $moduleName;
            }

            if (
                $this->allActivated ||
                (
                    in_array($moduleName, $arrCmInstalledModules) &&
                    (
                        $isCore ||
                        (
                            !$isCore &&
                            is_dir(
                                $this->cl->getFilePath(
                                    ASCMS_MODULE_PATH.'/'.$moduleName
                                )
                            )
                        )
                    )
                )
            ) {
                $this->arrInstalledModules[] = $moduleName;
            }

            if (
                $this->allActivated ||
                (
                    in_array($moduleName, $arrCmActiveModules) &&
                    (
                        $isCore ||
                        (
                            !$isCore &&
                            is_dir(
                                $this->cl->getFilePath(
                                    ASCMS_MODULE_PATH.'/'.$moduleName
                                )
                            )
                        )
                    )
                )
            ) {
                $this->arrActiveModules[] = $moduleName;
            }

            $objResult->MoveNext();
        }
    }

    /**
     * Checks if the passed module is a core module.
     *
     * @access  public
     * @param   string      $moduleName
     * @return  boolean
     */
    public function isCoreModule($moduleName) {
        // Workaround due to customizing to set News is Module
        // so it could be used as main navigation entry in backend.
        if ($moduleName == 'News') {
            return true;
        }
        return in_array($moduleName, $this->arrCoreModules);
    }

    /**
     * Checks if the passed module is active
     * (application page exists and is active).
     *
     * @access  public
     * @param   string      $moduleName
     * @return  boolean
     */
    public function isModuleActive($moduleName) {
        return in_array($moduleName, $this->arrActiveModules);
    }

    /**
     * Checks if the passed module is installed
     * (application page exists).
     *
     * @access  public
     * @param   string      $moduleName
     * @return  boolean
     */
    public function isModuleInstalled($moduleName) {
        return in_array($moduleName, $this->arrInstalledModules);
    }

    /**
     * Returns the cloudrexx core modules
     * @return array List of core modules
     */
    public function getCoreModules() {
        return $this->arrCoreModules;
    }

    /**
     * Returns the cloudrexx modules
     * @return array List of modules
     */
    public function getModules() {
        return $this->arrModules;
    }

    /**
     * Returns the installed cloudrexx modules
     * @return array List of installed modules
     */
    public function getInstalledModules() {
        return $this->arrInstalledModules;
    }

    /**
     * Returns the active cloudrexx modules
     * @return array List of active modules
     */
    public function getActiveModules() {
        return $this->arrActiveModules;
    }
}
