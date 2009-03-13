<?php
class Download {

    private $id;
    private $type;
    private $mime_type;
    private $source;
    //private $icon;
    private $size;
    private $image;
    private $owner_id;
    private $access_id;
    private $protected;
    private $license;
    private $version;
    private $author;
    private $website;
    private $ctime;
    private $mtime;
    private $is_active;
    private $visibility;
    private $order;
    private $views;
    private $download_count;
    private $downloads;
    private $categories;

    private $names;
    private $descriptions;

    private $access_groups;

    private $arrAttributes = array(
        'core' => array(
            'id'                                => 'int',
            'type'                              => 'string',
            'mime_type'                         => 'string',
            'source'                            => 'string',
            //'icon'                              => 'string',
            'size'                              => 'int',
            'image'                             => 'string',
            'owner_id'                         => 'int',
            'access_id'                         => 'int',
            'license'                           => 'string',
            'version'                           => 'string',
            'author'                            => 'string',
            'website'                           => 'string',
            'ctime'                             => 'int',
            'mtime'                             => 'int',
            'is_active'                         => 'int',
            'visibility'                        => 'int',
            'order'                             => 'int',
            'views'                             => 'int',
            'download_count'                    => 'int'
           ),
        'locale' => array(
            'name'                              => 'string',
            'description'                       => 'string'
         )
    );

    private $arrTypes = array('file', 'url');
    private $defaultType = 'file';

//    private $arrIcons = array(
//        'avi',
//        'bmp',
//        'css',
//        'doc',
//        'dot',
//        'exe',
//        'fla',
//        'gif',
//        'htm',
//        'html',
//        'inc',
//        'jpg',
//        'js',
//        'mp3',
//        'nfo',
//        'pdf',
//        'php',
//        'png',
//        'pps',
//        'ppt',
//        'rar',
//        'swf',
//        'txt',
//        'wma',
//        'xls',
//        'zip'
//    );
//  private $defaultIcon = '_blank';
//    private $urlIcon = 'htm';

    private $isFrontendMode;

    public static $arrMimeTypes = array(
        'image'         => array(
            'description'   => 'TXT_DOWNLOADS_TYPE_IMAGE',
            'extensions'    => array('jpg', 'jpeg', 'gif', 'png'),
            'icon'          => 'picture.png',
            'icon_small'    => 'picture_small.png'
        ),
        'document'      => array(
            'description'   => 'TXT_DOWNLOADS_TYPE_DOCUMENT',
            'extensions'    => array('doc', 'xls', 'txt', 'ppt', 'xml', 'odt', 'ott', 'sxw', 'stw', 'dot', 'rtf', 'sdw', 'wpd', 'jtd', 'cvs'),
            'icon'          => 'document.png',
            'icon_small'    => 'document_small.png'
        ),
        'pdf'           => array(
            'description'   => 'TXT_DOWNLOADS_TYPE_PDF',
            'extensions'    => array('pdf'),
            'icon'          => 'pdf.png',
            'icon_small'    => 'pdf_small.png'
        ),
        'media'         => array(
            'description'   => 'TXT_DOWNLOADS_TYPE_MEDIA',
            'extensions'    => array('avi', 'mp3', 'mpeg', 'wmv', 'mov', 'rm', 'wav', 'ogg'),
            'icon'          => 'media.png',
            'icon_small'    => 'media_small.png'
        ),
        'archive'       => array(
            'description'   => 'TXT_DOWNLOADS_TYPE_ARCHIVE',
            'extensions'    => array('tar', 'tar.gz', 'tar.bz2', 'tbz2', 'tb2', 'tbz', 'tgz', 'taz', 'tar.Z', 'zip', 'rar', 'cab'),
            'icon'          => 'archive.jpg',
            'icon_small'    => 'archive_small.png'
        ),
        'application'   => array(
            'description'   => 'TXT_DOWNLOADS_TYPE_APPLICATION',
            'extensions'    => array('exe', 'sh', 'bin', 'dmg', 'deb', 'rpm', 'msi', 'jar', 'pkg'),
            'icon'          => 'software.png',
            'icon_small'    => 'software_small.png'
        ),
        'link'          => array(
            'description'   => 'TXT_DOWNLOADS_TYPE_LINK',
            'extensions'    => array(),
            'icon'          => 'links.png',
            'icon_small'    => 'links_small.png'
        )
    );


    private $defaultMimeType = 'document';

    /**
     * @access public
     */
    public $EOF;

    /**
     * Array which holds all loaded downloads for later usage
     *
     * @var array
     * @access private
     */
    private $arrLoadedDownloads = array();

    /**
     * Array that holds all downloads which were ever loaded
     *
     * @var array
     * @access protected
     */
    protected  $arrCachedDownloads = array();

    /**
     * Contains the number of currently loaded downloads
     *
     * @var integer
     * @access private
     */
    private $filtered_search_count = 0;

    /**
     * Contains the message if an error occurs
     * @var string
     */
    public $error_msg = array();


    private $userId;

    public function __construct()
    {
        global $objInit;

        $this->isFrontendMode = $objInit->mode == 'frontend';

        $objFWUser = FWUser::getFWUserObject();
        $this->userId = $objFWUser->objUser->login() ? $objFWUser->objUser->getId() : 0;

        $this->clean();
    }

    public function __clone()
    {
        $this->clean();
    }

    /**
     * Clean download metadata
     *
     * Reset all download metadata for a new download.
     */
    private function clean()
    {
        $objFWUser = FWUser::getFWUserObject();

        $this->id = 0;
        $this->type = $this->defaultType;
        $this->mime_type = $this->defaultMimeType;
        $this->source = '';
        //$this->icon = $this->defaultIcon;
        $this->size = 0;
        $this->image = '';
        $this->owner_id = $objFWUser->objUser->login() ? $objFWUser->objUser->getId() : 0;
        $this->access_id = 0;
        $this->protected = false;
        $this->license = '';
        $this->version = '';
        $this->author = '';
        $this->website = '';
        $this->ctime = time();
        $this->mtime = $this->ctime;
        $this->is_active = 1;
        $this->visibility = 1;
        $this->order = 0;
        $this->views = 0;
        $this->download_count = 0;
        $this->downloads = null;
        $this->categories = null;
        $this->names = array();
        $this->descriptions = array();
        $this->EOF = true;
    }

    /**
     * Delete the current loaded download
     *
     * @return boolean
     */
    public function delete()
    {
        global $objDatabase, $_ARRAYLANG, $_LANGID;

        $objFWUser = FWUser::getFWUserObject();

        if (// managers are allowed to delete the download
            !Permission::checkAccess(142, 'static', true)
            // the owner has the permission to delete it by himself
            && (!$objFWUser->objUser->login() || $this->owner_id != $objFWUser->objUser->getId())
        ) {
            $this->error_msg[] = sprintf($_ARRAYLANG['TXT_DOWNLOADS_NO_PERM_DEL_DOWNLOAD'], htmlentities($this->getName($_LANGID), ENT_QUOTES, CONTREXX_CHARSET));
            return false;
        }

        Permission::removeAccess($this->access_id, 'dynamic');

        if ($objDatabase->Execute(
            'DELETE tblD, tblL, tblRC, tblR
            FROM `'.DBPREFIX.'module_downloads_download` AS tblD
            LEFT JOIN `'.DBPREFIX.'module_downloads_download_locale` AS tblL ON tblL.`download_id` = tblD.`id`
            LEFT JOIN `'.DBPREFIX.'module_downloads_rel_download_category` AS tblRC ON tblRC.`download_id` = tblD.`id`
            LEFT JOIN `'.DBPREFIX.'module_downloads_rel_download_download` AS tblR ON (tblR.`id1` = tblD.`id` OR tblR.`id2` = tblD.`id`)
            WHERE tblD.`id` = '.$this->id) !== false
        ) {
            return true;
        } else {
            $this->error_msg[] = sprintf($_ARRAYLANG['TXT_DOWNLOADS_DOWNLOAD_DELETE_FAILED'], htmlentities($this->name, ENT_QUOTES, CONTREXX_CHARSET));
        }

        return false;
    }

    /**
     * Load first download
     *
     */
    public function first()
    {
        if (reset($this->arrLoadedDownloads) === false || !$this->load(key($this->arrLoadedDownloads))) {
            $this->EOF = true;
        } else {
            $this->EOF = false;
        }
    }

    public function reset()
    {
        $this->clean();
    }

    /**
     * Load next download
     *
     */
    public function next()
    {
        if (next($this->arrLoadedDownloads) === false || !$this->load(key($this->arrLoadedDownloads))) {
            $this->EOF = true;
        }
    }

    public function getName($langId)
    {
        if (!isset($this->names)) {
            $this->loadLocales();
        }
        return isset($this->names[$langId]) ? $this->names[$langId] : '';
    }

    public function getFilteredSearchDownloadCount()
    {
        return $this->filtered_search_count;
    }

    public function getDescription($langId)
    {
        if (!isset($this->descriptions)) {
            $this->loadLocales();
        }
        return isset($this->descriptions[$langId]) ? $this->descriptions[$langId] : '';
    }

    // TODO: extend function that is loads the locales of all loaded downloads at once
    public function loadLocales()
    {
        global $objDatabase;

        $objResult = $objDatabase->Execute('
            SELECT
                `download_id`,
                `lang_id`,
                `name`,
                `description`
            FROM `'.DBPREFIX.'module_downloads_download_locale`
            WHERE `download_id` IN ('.implode(',', array_keys($this->arrLoadedDownloads)).')');
        if ($objResult) {
            while (!$objResult->EOF) {
                $this->arrLoadedDownloads[$objResult->fields['download_id']]['names'][$objResult->fields['lang_id']] = $objResult->fields['name'];
                $this->arrLoadedDownloads[$objResult->fields['download_id']]['descriptions'][$objResult->fields['lang_id']] = $objResult->fields['description'];

                $objResult->MoveNext();
            }

            $this->names = isset($this->arrLoadedDownloads[$this->id]['names']) ? $this->arrLoadedDownloads[$this->id]['names'] : null;
            $this->descriptions = isset($this->arrLoadedDownloads[$this->id]['descriptions']) ? $this->arrLoadedDownloads[$this->id]['descriptions'] : null;
        }
    }

    public function getDownload($id)
    {
        $objDownload = clone $this;
        $objDownload->arrCachedDownloads = &$this->arrCachedDownloads;

        if ($objDownload->load($id)) {
            return $objDownload;
        } else {
            return false;
        }
    }

    public function getDownloads($filter = null, $search = null, $arrSort = null, $arrAttributes = null, $limit = null, $offset = null)
    {
        $objDownload = clone $this;
        $objDownload->arrCachedDownloads = &$this->arrCachedDownloads;

        if ($objDownload->loadDownloads($filter, $search, $arrSort, $arrAttributes, $limit, $offset)) {
            return $objDownload;
        } else {
            return false;
        }
    }

    public function getErrorMsg()
    {
        return $this->error_msg;
    }

    /**
     * Load download data
     *
     * Get meta data of download from database
     * and put them into the analogous class variables.
     *
     * @param integer $id
     * @return unknown
     */
    public function load($id)
    {
        global $_LANGID;

//        $arrDebugBackTrace = debug_backtrace();
//        if (!in_array($arrDebugBackTrace[1]['function'], array('getDownload', 'first','next'))) {
//            die("Download->load(): Illegal method call in {$arrDebugBackTrace[0]['file']} on line {$arrDebugBackTrace[0]['line']}!");
//        }

        if ($id) {
            if (!isset($this->arrLoadedDownloads[$id])) {
                return $this->loadDownloads($id);
            } else {
                $this->id = $id;
                $this->type = isset($this->arrLoadedDownloads[$id]['type']) ? $this->arrLoadedDownloads[$id]['type'] : $this->defaultType;
                $this->mime_type = isset($this->arrLoadedDownloads[$id]['mime_type']) ? $this->arrLoadedDownloads[$id]['mime_type'] : $this->defaultMimeType;
                $this->source = isset($this->arrLoadedDownloads[$id]['source']) ? $this->arrLoadedDownloads[$id]['source'] : '';
                //$this->icon = isset($this->arrLoadedDownloads[$id]['icon']) ? $this->arrLoadedDownloads[$id]['icon'] : $this->defaultIcon;
                $this->size = isset($this->arrLoadedDownloads[$id]['size']) ? $this->arrLoadedDownloads[$id]['size'] : 0;
                $this->image = isset($this->arrLoadedDownloads[$id]['image']) ? $this->arrLoadedDownloads[$id]['image'] : '';
                $this->owner_id = isset($this->arrLoadedDownloads[$id]['owner_id']) ? $this->arrLoadedDownloads[$id]['owner_id'] : 0;
                $this->access_id = isset($this->arrLoadedDownloads[$id]['access_id']) ? $this->arrLoadedDownloads[$id]['access_id'] : 0;
                $this->protected = (bool) $this->access_id;
                $this->license = isset($this->arrLoadedDownloads[$id]['license']) ? $this->arrLoadedDownloads[$id]['license'] : '';
                $this->version = isset($this->arrLoadedDownloads[$id]['version']) ? $this->arrLoadedDownloads[$id]['version'] : '';
                $this->author = isset($this->arrLoadedDownloads[$id]['author']) ? $this->arrLoadedDownloads[$id]['author'] : '';
                $this->website = isset($this->arrLoadedDownloads[$id]['website']) ? $this->arrLoadedDownloads[$id]['website'] : '';
                $this->ctime = isset($this->arrLoadedDownloads[$id]['ctime']) ? $this->arrLoadedDownloads[$id]['ctime'] : time();
                $this->mtime = isset($this->arrLoadedDownloads[$id]['mtime']) ? $this->arrLoadedDownloads[$id]['mtime'] : (isset($this->ctime) ? $this->ctime : time());
                $this->is_active = isset($this->arrLoadedDownloads[$id]['is_active']) ? $this->arrLoadedDownloads[$id]['is_active'] : 1;
                $this->visibility = isset($this->arrLoadedDownloads[$id]['visibility']) ? $this->arrLoadedDownloads[$id]['visibility'] : 1;
                $this->order = isset($this->arrLoadedDownloads[$id]['order']) ? $this->arrLoadedDownloads[$id]['order'] : 0;
                $this->views = isset($this->arrLoadedDownloads[$id]['views']) ? $this->arrLoadedDownloads[$id]['views'] : 0;
                $this->download_count = isset($this->arrLoadedDownloads[$id]['download_count']) ? $this->arrLoadedDownloads[$id]['download_count'] : 0;
                $this->downloads = isset($this->arrLoadedDownloads[$id]['downloads']) ? $this->arrLoadedDownloads[$id]['downloads'] : null;
                $this->categories = isset($this->arrLoadedDownloads[$id]['categories']) ? $this->arrLoadedDownloads[$id]['categories'] : null;
                $this->names = isset($this->arrLoadedDownloads[$id]['names']) ? $this->arrLoadedDownloads[$id]['names'] : null;
                $this->descriptions = isset($this->arrLoadedDownloads[$id]['descriptions']) ? $this->arrLoadedDownloads[$id]['descriptions'] : null;
                $this->EOF = false;
                return true;
            }
        } else {
            $this->clean();
            return false;
        }
    }

    public function loadDownloads($filter = null, $search = null, $arrSort = null, $arrAttributes = null, $limit = null, $offset = null)
    {
        global $objDatabase;

//        $arrDebugBackTrace = debug_backtrace();
//        if (!in_array($arrDebugBackTrace[1]['function'], array('load', 'getDownloads'))) {
//            die("Download->loadDownloads(): Illegal method call in {$arrDebugBackTrace[0]['file']} on line {$arrDebugBackTrace[0]['line']}!");
//        }

        $this->arrLoadedDownloads = array();
        $arrSelectCoreExpressions = array();
        $this->filtered_search_count = 0;
        $sqlCondition = '';

        // set filter
        if (isset($filter) && is_array($filter) && count($filter) || !empty($search)) {
            $sqlCondition = $this->getFilteredIdList($filter, $search);
        } elseif (!empty($filter)) {
            $sqlCondition['tables'] = array('core');
            $sqlCondition['conditions'] = array('tblD.`id` = '.intval($filter));
            $limit = 1;
        }

        // set sort order
        if (!($arrQuery = $this->setSortedIdList($arrSort, $sqlCondition, $limit, $offset))) {
            $this->clean();
            return false;
        }

        // set field list
        if (is_array($arrAttributes)) {
            foreach ($arrAttributes as $attribute) {
                if (isset($this->arrAttributes['core'][$attribute]) && !in_array($attribute, $arrSelectCoreExpressions)) {
                    $arrSelectCoreExpressions[] = $attribute;
                }/* elseif (isset($this->arrAttributes['locale'][$attribute]) && !in_array($attribute, $arrSelectLocaleExpressions)) {
                    $arrSelectLocaleExpressions[] = $attribute;
                }*/
            }

            if (!in_array('id', $arrSelectCoreExpressions)) {
                $arrSelectCoreExpressions[] = 'id';
            }
        } else {
            $arrSelectCoreExpressions = array_keys($this->arrAttributes['core']);
            //$arrSelectLocaleExpressions = array_keys($this->arrAttributes['locale']);
        }

        $query = 'SELECT DISTINCT tblD.`'.implode('`, tblD.`', $arrSelectCoreExpressions).'`'
            //.(count($arrSelectLocaleExpressions) ? ', tblL.`'.implode('`, tblL.`', $arrSelectLocaleExpressions).'`' : '')
            .'FROM `'.DBPREFIX.'module_downloads_download` AS tblD'
            .(/*count($arrSelectLocaleExpressions) || */$arrQuery['tables']['locale'] ? ' INNER JOIN `'.DBPREFIX.'module_downloads_download_locale` AS tblL ON tblL.`download_id` = tblD.`id`' : '')
            .($arrQuery['tables']['category'] ? (
                ' INNER JOIN `'.DBPREFIX.'module_downloads_rel_download_category` AS tblRC ON tblRC.`download_id` = tblD.`id`')
                .($this->isFrontendMode ? ' INNER JOIN `'.DBPREFIX.'module_downloads_category` AS tblC ON tblC.`id` = tblRC.`category_id`' : '')
                : '')
            .($arrQuery['tables']['download'] ? ' INNER JOIN `'.DBPREFIX.'module_downloads_rel_download_download` AS tblR ON (tblR.`id1` = tblD.`id` OR tblR.`id2` = tblD.`id`)' : '')
            .(count($arrQuery['conditions']) ? ' WHERE ('.implode(') AND (', $arrQuery['conditions']).')' : '')
            //.' GROUP BY tblD.`id`'
            .(count($arrQuery['sort']) ? ' ORDER BY '.implode(', ', $arrQuery['sort']) : '');

        if (empty($limit)) {
            $objDownload = $objDatabase->Execute($query);
        } else {
            $objDownload = $objDatabase->SelectLimit($query, $limit, $offset);
        };

        if ($objDownload !== false && $objDownload->RecordCount() > 0) {
            while (!$objDownload->EOF) {
                foreach ($objDownload->fields as $attributeId => $value) {
                    $this->arrCachedDownloads[$objDownload->fields['id']][$attributeId] = $this->arrLoadedDownloads[$objDownload->fields['id']][$attributeId] = $value;
                }
                $objDownload->MoveNext();
            }

            $this->first();
            return true;
        } else {
            $this->clean();
            return false;
        }
    }

    private function getFilteredIdList($arrFilter = null, $search = null)
    {
        $arrConditions = array();
        $tblLocales = false;
        $arrTables = array();

        // parse filter
        if (isset($arrFilter) && is_array($arrFilter)) {
            if (count($arrFilterConditions = $this->parseFilterConditions($arrFilter))) {
                $arrConditions[] = implode(' AND ', $arrFilterConditions['conditions']);
                $tblLocales = isset($arrFilterConditions['tables']['locale']);
            }
        }

        if (in_array('category_id', array_keys($arrFilter)) && !empty($arrFilter['category_id'])) {
            if (is_array($arrFilter['category_id'])) {
                foreach ($arrFilter['category_id'] as $condition => $categoryId) {
                    $arrCategoryConditions[] = 'tblRC.`category_id` '.$condition.' '.intval($categoryId);
                }
            } else {
                $arrCategoryConditions[] = 'tblRC.`category_id` = '.intval($arrFilter['category_id']);
            }
            $arrConditions[] = '('.implode(' OR ', $arrCategoryConditions).')';
            $arrTables[] = 'category';
        }

        if (in_array('download_id', array_keys($arrFilter)) && !empty($arrFilter['download_id'])) {
            $arrConditions[] = '(tblR.`id1` = '.intval($arrFilter['download_id']).' OR tblR.`id2` = '.intval($arrFilter['download_id']).')';
            $arrConditions[] = 'tblD.`id` != '.intval($arrFilter['download_id']);
            $arrTables[] = 'download';
        }


        // parse access permissions for the frontend
        if ($this->isFrontendMode) {
            $objFWUser = FWUser::getFWUserObject();

            // download access
            if (!isset($arrFilter['is_active'])) {
                $arrConditions[] = 'tblD.`is_active` = 1';
            }
            $arrConditions[] = 'tblD.`visibility` = 1'.(
                $objFWUser->objUser->login() ?
                ' OR tblD.`owner_id` = '.$objFWUser->objUser->getId()
                .(count($objFWUser->objUser->getDynamicPermissionIds()) ? ' OR tblD.`access_id` IN ('.implode(', ', $objFWUser->objUser->getDynamicPermissionIds()).')' : '')
                : '');


            // category access
            if (!in_array('category', $arrTables)) {
                $arrTables[] = 'category';
            }
            $arrConditions[] = 'tblC.`is_active` = 1';
            $arrConditions[] = 'tblC.`visibility` = 1'.(
                $objFWUser->objUser->login() ?
                    ' OR tblC.`owner_id` = '.$objFWUser->objUser->getId()
                    .(count($objFWUser->objUser->getDynamicPermissionIds()) ? ' OR tblC.`read_access_id` IN ('.implode(', ', $objFWUser->objUser->getDynamicPermissionIds()).')' : '')
                : '');
        }

        // parse search
        if (!empty($search)) {
            if (count($arrSearchConditions = $this->parseSearchConditions($search))) {
                $arrConditions[] = implode(' OR ', $arrSearchConditions);
                $tblLocales = true;
            }
        }

        if ($tblLocales) {
            $arrTables[] = 'locale';
        }

        return array(
            'tables'        => $arrTables,
            'conditions'    => $arrConditions
        );
    }

/**
     * Parse filter conditions
     *
     * Generate conditions of the attributes for the SQL WHERE statement.
     * The filter conditions are defined through the two dimensional array $arrFilter.
     * Each key-value pair represents an attribute and its associated condition to which it must fit to.
     * The condition could either be a integer or string depending on the attributes type, or it could be
     * a collection of integers or strings represented in an array.
     *
     * Examples of the filer array:
     *
     * array(
     *      'name' => '%editor%',
     * )
     * // will return all downloads who's name includes 'editor'
     *
     *
     * array(
     *      'name' => array(
     *          'd%',
     *          'e%',
     *          'f%',
     *          'g%'
     *      )
     * )
     * // will return all downloads which have a name of which its first letter is and between 'd' to 'g' (case less)
     *
     *
     * array(
     *      'name' => array(
     *          array(
     *              '>' => 'd',
     *              '<' => 'g'
     *          ),
     *          'LIKE'  => 'g%'
     *      )
     * )
     * // same as the preview example but in an other way
     *
     *
     * array(
     *      'is_active' => 1,
     *      'license' => 'GPL'
     * )
     * // will return all downloads that are active and are licensed by the GPL
     *
     *
     *
     * @param array $arrFilter
     * @return array
     */
    private function parseFilterConditions($arrFilter)
    {
        $arrConditions = array();

        $arrComparisonOperators = array(
            'int'       => array('=','<','>'),
            'string'    => array('!=','<','>', 'REGEXP')
        );
        $arrDefaultComparisonOperator = array(
            'int'       => '=',
            'string'    => 'LIKE'
        );
        $arrEscapeFunction = array(
            'int'       => 'intval',
            'string'    => 'addslashes'
        );

        foreach ($arrFilter as $attribute => $condition) {
            /**
             * $attribute is the attribute like 'is_active' or 'name'
             * $condition is either a simple condition (integer or string) or an condition matrix (array)
             */
            foreach ($this->arrAttributes as $type => $arrAttributes) {
                $table = $type == 'core' ? 'tblD' : 'tblL';

                if (isset($arrAttributes[$attribute])) {
                    if (is_array($condition)) {
                        $arrRestrictions = array();
                        foreach ($condition as $operator => $restriction) {
                            /**
                             * $operator is either a comparison operator ( =, LIKE, <, >) if $restriction is an array or if $restriction is just an integer or a string then its an index which would be useless
                             * $restriction is either a integer or a string or an array which represents a restriction matrix
                             */
                            if (is_array($restriction)) {
                                $arrConditionRestriction = array();
                                foreach ($restriction as $restrictionOperator => $restrictionValue) {
                                    /**
                                     * $restrictionOperator is a comparison operator ( =, <, >)
                                     * $restrictionValue represents the condition
                                     */
                                    $arrConditionRestriction[] = $table.".`{$attribute}` ".(
                                        in_array($restrictionOperator, $arrComparisonOperators[$arrAttributes[$attribute]], true) ?
                                            $restrictionOperator
                                        :   $arrDefaultComparisonOperator[$arrAttributes[$attribute]]
                                    )." '".$arrEscapeFunction[$arrAttributes[$attribute]]($restrictionValue)."'";
                                }
                                $arrRestrictions[] = implode(' AND ', $arrConditionRestriction);
                            } else {
                                $arrRestrictions[] = $table.".`{$attribute}` ".(
                                    in_array($operator, $arrComparisonOperators[$arrAttributes[$attribute]], true) ?
                                        $operator
                                    :   $arrDefaultComparisonOperator[$arrAttributes[$attribute]]
                                )." '".$arrEscapeFunction[$arrAttributes[$attribute]]($restriction)."'";
                            }
                        }
                        $arrConditions['conditions'][] = '(('.implode(') OR (', $arrRestrictions).'))';
                        $arrConditions['tables'][$type] = true;
                    } else {
                        $arrConditions['conditions'][] = "(".$table.".`".$attribute."` ".$arrDefaultComparisonOperator[$arrAttributes[$attribute]]." '".$arrEscapeFunction[$arrAttributes[$attribute]]($condition)."')";
                        $arrConditions['tables'][$type] = true;
                    }
                }
            }
        }

        return $arrConditions;
    }

    private function parseSearchConditions($search)
    {
        $arrConditions = array();
        $arrAttribute = array('name', 'description');
        foreach ($arrAttribute as $attribute) {
            $arrConditions[] = "tblL.`".$attribute."` LIKE '%".(is_array($search) ? implode("%' OR tblL.`".$attribute."` LIKE '%", array_map('addslashes', $search)) : addslashes($search))."%'";
        }

        return $arrConditions;
    }


    private function setSortedIdList($arrSort, $sqlCondition = null, $limit = null, $offset = null)
    {
        global $objDatabase, $_LANGID;

        $arrCustomSelection = array();
        $joinLocaleTbl = false;
        $joinCategoryTbl = false;
        $joinDownloadTbl = false;
        $arrIds = array();
        $arrSortExpressions = array();
        $nr = 0;

        if (!empty($sqlCondition)) {
            if (isset($sqlCondition['conditions']) && count($sqlCondition['conditions'])) {
                $arrCustomSelection = $sqlCondition['conditions'];
            }

            if (isset($sqlCondition['tables'])) {
                if (in_array('locale', $sqlCondition['tables'])) {
                    $joinLocaleTbl = true;
                    $arrCustomSelection[] = 'tblL.`lang_id` = '.$_LANGID;
                }
                if (in_array('category', $sqlCondition['tables'])) {
                    $joinCategoryTbl = true;
                }
                if (in_array('download', $sqlCondition['tables'])) {
                    $joinDownloadTbl = true;
                }
            }

        }

        if (is_array($arrSort)) {
            foreach ($arrSort as $attribute => $direction) {
                if (in_array(strtolower($direction), array('asc', 'desc'))) {
                    if (isset($this->arrAttributes['core'][$attribute])) {
                        $arrSortExpressions[] = 'tblD.`'.$attribute.'` '.$direction;
                    } elseif (isset($this->arrAttributes['locale'][$attribute])) {
                        $arrSortExpressions[] = 'tblL.`'.$attribute.'` '.$direction;
                        $joinLocaleTbl = true;
                    }
                } elseif ($attribute == 'special') {
                    $arrSortExpressions[] = $direction;
                }
            }
        }

        $query = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT tblD.`id`
            FROM `'.DBPREFIX.'module_downloads_download` AS tblD'
            .($joinLocaleTbl ? ' INNER JOIN `'.DBPREFIX.'module_downloads_download_locale` AS tblL ON tblL.`download_id` = tblD.`id`' : '')
            .($joinCategoryTbl ?
                ' INNER JOIN `'.DBPREFIX.'module_downloads_rel_download_category` AS tblRC ON tblRC.`download_id` = tblD.`id`'
                .($this->isFrontendMode ? ' INNER JOIN `'.DBPREFIX.'module_downloads_category` AS tblC ON tblC.`id` = tblRC.`category_id`' : '')
                : '')
            .($joinDownloadTbl ? ' INNER JOIN `'.DBPREFIX.'module_downloads_rel_download_download` AS tblR ON (tblR.`id1` = tblD.`id` OR tblR.`id2` = tblD.`id`)' : '')
            .(count($arrCustomSelection) ? ' WHERE ('.implode(') AND (', $arrCustomSelection).')' : '')
            .(count($arrSortExpressions) ? ' ORDER BY '.implode(', ', $arrSortExpressions) : '');

        if (empty($limit)) {
            $objDownloadId = $objDatabase->Execute($query);
            $this->filtered_search_count = $objDownloadId->RecordCount();
        } else {
            $objDownloadId = $objDatabase->SelectLimit($query, $limit, intval($offset));
            $objDownloadCount = $objDatabase->Execute('SELECT FOUND_ROWS()');
            $this->filtered_search_count = $objDownloadCount->fields['FOUND_ROWS()'];
        }

        if ($objDownloadId !== false) {
            while (!$objDownloadId->EOF) {
                $arrIds[$objDownloadId->fields['id']] = '';
                $objDownloadId->MoveNext();
            }
        }

        $this->arrLoadedCategories = $arrIds;

        if (!count($arrIds)) {
            return false;
        }

        return array(
            'tables' => array(
                'locale'    => $joinLocaleTbl,
                'category'  => $joinCategoryTbl,
                'download'  => $joinDownloadTbl
            ),
            'conditions'    => $arrCustomSelection,
            'sort'          => $arrSortExpressions
        );

        return $arrIds;
    }


    public function incrementDownloadCount()
    {
        global $objDatabase;

        $objDatabase->Execute('UPDATE `'.DBPREFIX.'module_downloads_download` SET `download_count` = `download_count` + 1 WHERE `id` = '.$this->id);
    }

    public function incrementViewCount()
    {
        global $objDatabase;

        $objDatabase->Execute('UPDATE `'.DBPREFIX.'module_downloads_download` SET `views` = `views` + 1 WHERE `id` = '.$this->id);
    }

    /**
     * Store download
     *
     * This stores the metadata of the download to the database.
     *
     * @global ADONewConnection
     * @global array
     * @return boolean
     */
    public function store()
    {
        global $objDatabase, $_ARRAYLANG;

        if (!Permission::checkAccess(142, 'static', true)
            && (($objFWUser = FWUser::getFWUserObject()) == false || !$objFWUser->objUser->login() || $this->owner_id != $objFWUser->objUser->getId())
        ) {
            $this->error_msg[] = $_ARRAYLANG['TXT_DOWNLOADS_MODIFY_DOWNLOAD_PROHIBITED'];
            return false;
        }


        if (isset($this->names) && !$this->validateName()) {
            return false;
        }

        if ($this->id) {
            if ($objDatabase->Execute("
                UPDATE `".DBPREFIX."module_downloads_download`
                SET
                    `type` = '".$this->type."',
                    `mime_type` = '".$this->mime_type."',
                    `source` = '".addslashes($this->source)."',
                    `size` = ".intval($this->size).",
                    `image` = '".addslashes($this->image)."',
                    `owner_id` = ".intval($this->owner_id).",
                    `license` = '".addslashes($this->license)."',
                    `version` = '".addslashes($this->version)."',
                    `author` = '".addslashes($this->author)."',
                    `website` = '".addslashes($this->website)."',
                    `mtime` = ".$this->mtime.",
                    `is_active` = ".intval($this->is_active).",
                    `visibility` = ".intval($this->visibility).",
                    `order` = ".intval($this->order)."
                WHERE `id` = ".$this->id
            ) === false) {
                // TODO: add lang var ?
                $this->error_msg[] = $_ARRAYLANG['TXT_DOWNLOADS_FAILED_UPDATE_DOWNLOAD'];
                return false;
            }
        } else {
            if ($objDatabase->Execute("
                INSERT INTO `".DBPREFIX."module_downloads_download` (
                    `type`,
                    `mime_type`,
                    `source`,
                    `size`,
                    `image`,
                    `owner_id`,
                    `license`,
                    `version`,
                    `author`,
                    `website`,
                    `ctime`,
                    `mtime`,
                    `is_active`,
                    `visibility`,
                    `order`
                ) VALUES (
                    '".$this->type."',
                    '".$this->mime_type."',
                    '".addslashes($this->source)."',
                    ".intval($this->size).",
                    '".addslashes($this->image)."',
                    ".intval($this->owner_id).",
                    '".addslashes($this->license)."',
                    '".addslashes($this->version)."',
                    '".addslashes($this->author)."',
                    '".addslashes($this->website)."',
                    ".$this->ctime.",
                    ".$this->mtime.",
                    ".intval($this->is_active).",
                    ".intval($this->visibility).",
                    ".intval($this->order)."
                )") !== false) {
                $this->id = $objDatabase->Insert_ID();
            } else {
                // TODO: add lang var
                $this->error_msg[] = $_ARRAYLANG['TXT_DOWNLOADS_FAILED_ADD_DOWNLOAD'];
                return false;
            }
        }

        if (isset($this->names) && !$this->storeLocales()) {
            $this->error_msg[] = $_ARRAYLANG['TXT_DOWNLOADS_COULD_NOT_STORE_LOCALES'];
            return false;
        }

        if (!$this->storeCategoryAssociations()) {
            $this->error_msg[] = $_ARRAYLANG['TXT_DOWNLOADS_COULD_NOT_STORE_CATEGORY_ASSOCIATIONS'];
            return false;
        }

        if (!$this->storeDownloadRelations()) {
            $this->error_msg[] = $_ARRAYLANG['TXT_DOWNLOADS_COULD_NOT_STORE_DOWNLOAD_RELATIONS'];
            return false;
        }

        if (!$this->storePermissions()) {
            $this->error_msg[] = $_ARRAYLANG['TXT_DOWNLOADS_COULD_NOT_STORE_PERMISSIONS'];
            return false;
        }

        return true;
    }

    /**
     * Store locales
     *
     * @global ADONewConnection
     * @return boolean TRUE on success, otherwise FALSE
     */
    private function storeLocales()
    {
        global $objDatabase;

        $arrOldLocales = array();
        $status = true;

        $objOldLocales = $objDatabase->Execute('SELECT `lang_id`, `name`, `description` FROM `'.DBPREFIX.'module_downloads_download_locale` WHERE `download_id` = '.$this->id);
        if ($objOldLocales !== false) {
            while (!$objOldLocales->EOF) {
                $arrOldLocales[$objOldLocales->fields['lang_id']] = array(
                    'name'          => $objOldLocales->fields['name'],
                    'description'   => $objOldLocales->fields['description']
                );
                $objOldLocales->MoveNext();
            }
        } else {
            return false;
        }

        $arrNewLocales = array_diff(array_keys($this->names), array_keys($arrOldLocales));
        $arrRemovedLocales = array_diff(array_keys($arrOldLocales), array_keys($this->names));
        $arrUpdatedLocales = array_intersect(array_keys($this->names), array_keys($arrOldLocales));

        foreach ($arrNewLocales as $langId) {
            if ($objDatabase->Execute("INSERT INTO `".DBPREFIX."module_downloads_download_locale` (`lang_id`, `download_id`, `name`, `description`) VALUES (".$langId.", ".$this->id.", '".addslashes($this->names[$langId])."', '".addslashes($this->descriptions[$langId])."')") === false) {
                $status = false;
            }
        }

        foreach ($arrRemovedLocales as $langId) {
            if ($objDatabase->Execute("DELETE FROM `".DBPREFIX."module_downloads_download_locale` WHERE `download_id` = ".$this->id." AND `lang_id` = ".$langId) === false) {
                $status = false;
            }
        }

        foreach ($arrUpdatedLocales as $langId) {
            if ($this->names[$langId] != $arrOldLocales[$langId]['name'] || $this->descriptions[$langId] != $arrOldLocales[$langId]['description']) {
                if ($objDatabase->Execute("UPDATE `".DBPREFIX."module_downloads_download_locale` SET `name` = '".addslashes($this->names[$langId])."', `description` = '".addslashes($this->descriptions[$langId])."' WHERE `download_id` = ".$this->id." AND `lang_id` = ".$langId) === false) {
                    $status = false;
                }
            }
        }
        return $status;
    }

    private function storeCategoryAssociations()
    {
        global $objDatabase;

        $arrOldCategories = array();
        $status = true;

        if (!isset($this->categories)) {
            $this->loadCategoryAssociations();
        }

        $objOldCategories = $objDatabase->Execute('SELECT `category_id` FROM `'.DBPREFIX.'module_downloads_rel_download_category` WHERE `download_id` = '.$this->id);
        if ($objOldCategories !== false) {
            while (!$objOldCategories->EOF) {
                $arrOldCategories[] = $objOldCategories->fields['category_id'];
                $objOldCategories->MoveNext();
            }
        } else {
            return false;
        }

        $arrNewCategories = array_diff($this->categories, $arrOldCategories);
        $arrRemovedCategories = array_diff($arrOldCategories, $this->categories);

        if (!Permission::checkAccess(142, 'static', true)) {
            // we have to check if all associations are within the users permissions
            $objFWUser = FWUser::getFWUserObject();
            $objCategory = Category::getCategories(null, null, array('order' => 'ASC', 'name' => 'ASC', 'id' => 'ASC'));

            while (!$objCategory->EOF) {
                if ($objFWUser->objUser->login() && $objCategory->getOwnerId() == $objFWUser->objUser->getId()) {
                    // the owner of the category is allowed to associated with it whatever he wants
                    $objCategory->next();
                    continue;
                }

                if (in_array($objCategory->getId(), $arrNewCategories)) {
                    // the download has been added to this category
                    if ($objCategory->getAddFilesAccessId()
                        && !Permission::checkAccess($objCategory->getAddFilesAccessId(), 'dynamic', true)
                    ) {
                        // we won't store this association, because the user doesn't have the permission to
                        unset($arrNewCategories[array_search($objCategory->getId(), $arrNewCategories)]);
                    }
                } elseif (in_array($objCategory->getId(), $arrRemovedCategories)) {
                    // the download has been removed from this category
                    if ($objCategory->getManageFilesAccessId()
                        && !Permission::checkAccess($objCategory->getManageFilesAccessId(), 'dynamic', true)
                        // the owner of the download is allowed to unlink it
                        && (!$objFWUser->objUser->login() || $this->owner_id == $objFWUser->objUser->getId())
                    ) {
                        // we won't store this association, because the user doesn't have the permission to
                        unset($arrRemovedCategories[array_search($objCategory->getId(), $arrRemovedCategories)]);
                    }
                }

                $objCategory->next();
            }
        }

        foreach ($arrNewCategories as $categoryId) {
            if ($objDatabase->Execute("INSERT INTO `".DBPREFIX."module_downloads_rel_download_category` (`download_id`, `category_id`) VALUES (".$this->id.", ".$categoryId.")") === false) {
                $status = false;
            }
        }

        foreach ($arrRemovedCategories as $categoryId) {
            if ($objDatabase->Execute("DELETE FROM `".DBPREFIX."module_downloads_rel_download_category` WHERE `download_id` = ".$this->id." AND `category_id` = ".$categoryId) === false) {
                $status = false;
            }
        }
        return $status;

        return true;
    }

    private function storeDownloadRelations()
    {
        global $objDatabase;

        $arrOldRelations = array();
        $status = true;

        if (!isset($this->downloads)) {
            $this->loadRelatedDownloads();
        }

        $objResult = $objDatabase->Execute('SELECT `id1`, `id2` FROM `'.DBPREFIX.'module_downloads_rel_download_download` WHERE `id1` = '.$this->id.' OR `id2` = '.$this->id);
        if ($objResult) {
            while (!$objResult->EOF) {
                $arrOldRelations[] = $objResult->fields['id1'] == $this->id ? $objResult->fields['id2'] : $objResult->fields['id1'];
                $objResult->MoveNext();
            }
        } else {
            return false;
        }

        $arrNewRelations = array_diff($this->downloads, $arrOldRelations);
        $arrRemovedRelations = array_diff($arrOldRelations, $this->downloads);

        foreach ($arrNewRelations as $downloadId) {
            if ($objDatabase->Execute("INSERT INTO `".DBPREFIX."module_downloads_rel_download_download` (`id1`, `id2`) VALUES (".$this->id.", ".$downloadId.")") === false) {
                $status = false;
            }
        }

        foreach ($arrRemovedRelations as $downloadId) {
            if ($objDatabase->Execute('DELETE FROM `'.DBPREFIX.'module_downloads_rel_download_download` WHERE (`id1` = '.$this->id.' AND `id2` = '.$downloadId.') OR (`id2` = '.$this->id.' AND `id1` = '.$downloadId.')') === false) {
                $status = false;
            }
        }
        return $status;

        return true;
    }

    private function storePermissions()
    {
        global $objDatabase;

        $status = true;
        if ($this->protected) {
            // set protection
            if ($this->access_id || $this->access_id = Permission::createNewDynamicAccessId()) {
                Permission::removeAccess($this->access_id, 'dynamic');
                if (count($this->access_groups)) {
                    Permission::setAccess($this->access_id, 'dynamic', $this->access_groups);
                }
            } else {
                // remove protection due that no new access-ID could have been created
                $this->access_id = 0;
                $status = false;
            }
        } elseif ($this->access_id) {
            // remove protection
            Permission::removeAccess($this->access_id, 'dynamic');
            $this->access_id = 0;
        }

        if (!$status) {
            return false;
        }

        if ($objDatabase->Execute("
            UPDATE `".DBPREFIX."module_downloads_download`
            SET
                `access_id` = ".intval($this->access_id)."
            WHERE `id` = ".$this->id
        ) === false) {
            return false;
        } else {
            return true;
        }
    }

    private function validateName()
    {
        global $_ARRAYLANG, $objLanguage;

        if (!isset($objLanguages)) {
            $objLanguages = new FWLanguage();
        }

        $arrLanguages = $objLanguages->getLanguageArray();
        $namesSet = true;
        foreach ($arrLanguages as $langId => $arrLanguage) {
            if (empty($this->names[$langId])) {
                $namesSet = false;
                break;
            }
        }

        if ($namesSet) {
            return true;
        } else {
            $this->error_msg[] = $_ARRAYLANG['TXT_DOWNLOADS_EMPTY_NAME_ERROR'];
            return false;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMimeType()
    {
        return $this->mime_type;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getIcon($small = false)
    {
        return ASCMS_MODULE_IMAGE_WEB_PATH.'/downloads/'.Download::$arrMimeTypes[$this->getMimeType()][($small ? 'icon_small' : 'icon')];
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getOwnerId()
    {
        return $this->owner_id;
    }


    public function getAccessId()
    {
        return $this->access_id;
    }

    public function getLicense()
    {
        return $this->license;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getWebsite()
    {
        return $this->website;
    }

    public function getCTime()
    {
        return $this->ctime;
    }

    public function getMTime()
    {
        return $this->mtime;
    }

    public function getActiveStatus()
    {
        return $this->is_active;
    }

    public function getVisibility()
    {
        return $this->visibility;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getViewCount()
    {
        return $this->views;
    }

    public function getDownloadCount()
    {
        return $this->download_count;
    }

    public function getAssociatedCategoryIds()
    {
        if (!isset($this->categories)) {
            $this->loadCategoryAssociations();
        }
        return $this->categories;
    }


    public function getRelatedDownloadIds()
    {
        if (!isset($this->downloads)) {
            $this->loadRelatedDownloads();
        }
        return $this->downloads;
    }

    public function getAccessGroupIds()
    {
        return $this->access_groups;
    }

    private function loadCategoryAssociations()
    {
        global $objDatabase;

        if (count($this->arrLoadedDownloads)) {
            $objFWUser = FWUser::getFWUserObject();
            $objResult = $objDatabase->Execute('
                SELECT  tblR.`download_id`, tblR.`category_id`
                FROM    `'.DBPREFIX.'module_downloads_rel_download_category` AS tblR
                        '.($this->isFrontendMode ? 'INNER JOIN `'.DBPREFIX.'module_downloads_category` AS tblC ON tblC.`id` = tblR.`category_id`' : '').'
                WHERE   tblR.`download_id` IN ('.implode(',', array_keys($this->arrLoadedDownloads)).')
                        '.($this->isFrontendMode ? 'AND tblC.`is_active` = 1 AND (tblC.`visibility` = 1'.(
                            $objFWUser->objUser->login() ?
                                ' OR tblC.`owner_id` = '.$objFWUser->objUser->getId()
                                .(count($objFWUser->objUser->getDynamicPermissionIds()) ? ' OR tblC.`read_access_id` IN ('.implode(', ', $objFWUser->objUser->getDynamicPermissionIds()).')' : '')
                                : '').')
                        ORDER BY tblC.`parent_id`, tblC.`order`'
                        : '')
            );
            if ($objResult) {
                while (!$objResult->EOF) {
                    $this->arrLoadedDownloads[$objResult->fields['download_id']]['categories'][] = $objResult->fields['category_id'];
                    $objResult->MoveNext();
                }
            }
        }

        $this->categories = isset($this->arrLoadedDownloads[$this->id]['categories']) ? $this->arrLoadedDownloads[$this->id]['categories'] : array();
    }

    private function loadRelatedDownloads()
    {
        global $objDatabase;

        $this->downloads = array();
        $objResult = $objDatabase->Execute('SELECT `id1`, `id2` FROM `'.DBPREFIX.'module_downloads_rel_download_download` WHERE `id1` = '.$this->id.' OR `id2` = '.$this->id);
        if ($objResult) {
            while (!$objResult->EOF) {
                $this->downloads[] = $objResult->fields['id1'] == $this->id ? $objResult->fields['id2'] : $objResult->fields['id1'];
                $objResult->MoveNext();
            }
        }
    }

    public function setType($type)
    {
        $this->type = in_array($type, $this->arrTypes) ? $type : $this->defaultType;
    }

    public function setMimeType($mimeType)
    {
        $this->mime_type = in_array($mimeType, array_keys(Download::$arrMimeTypes)) ? $mimeType : $this->defaultMimeType;
    }

    public function setSource($source)
    {
        // TODO: add url valicator
        if ($this->type == 'url') {
            $source = FWValidator::getUrl($source);
            //$this->icon = $this->urlIcon;
//        } else {
//            $extension = pathinfo($source, PATHINFO_EXTENSION);
//            $this->icon = in_array($extension, $this->arrIcons) ? $extension : $this->defaultIcon;
        }

        $this->source = $source;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function setOwnerId($ownerId)
    {
        $this->owner_id = $ownerId;
    }

    public function setAccessId($accessId)
    {
        $this->access_id = $accessId;
    }

    public function setLicense($license)
    {
        $this->license = $license;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }

    public function setWebsite($website)
    {
        $this->website = FWValidator::getUrl($website);
    }

    public function updateMTime()
    {
        $this->mtime = time();
    }

    public function setActiveStatus($isActive)
    {
        $this->is_active = $isActive;
    }

    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getProtection()
    {
        return $this->protected;
    }

    public function setProtection($protected)
    {
        $this->protected = $protected;

        if (!$this->protected) {
            $this->visibility = 1;
        }
    }

    public function setNames($arrNames)
    {
        $this->names = $arrNames;
    }

    public function setDescriptions($arrDescriptions)
    {
        $this->descriptions = $arrDescriptions;
    }

    public function setGroups($arrGroups)
    {
        $this->access_groups = $arrGroups;
    }

    public function setCategories($arrCategories)
    {
        $this->categories = $arrCategories;
    }

    public function setDownloads($arrDownloads)
    {
        $this->downloads = $arrDownloads;
    }













}
?>
