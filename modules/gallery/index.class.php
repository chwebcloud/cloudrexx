<?php
/**
 * Gallery
 *
 * This class is used to publish the pictures of the gallery on the frontend.
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Astalavista Development Team <thun@astalvista.ch>
 * @version     1.2
 * @package     contrexx
 * @subpackage  module_gallery
 * @todo        Edit PHP DocBlocks!
 */
class Gallery {
    var $_objTpl;
    var $pageContent;
    var $arrSettings;
    var $strImagePath;
    var $strImageWebPath;
    var $strThumbnailPath;
    var $strThumbnailWebPath;
    var $langId;
    var $strCmd = '';


    /**
    * Constructor php4
    *
    * @param      string
    * @access     public
    */
    function Gallery($pageContent)
    {
        $this->__construct($pageContent);
    }

    /**
    * Constructor
    *
    * @global array $_ARRAYLANG
    * @global object $objDatabase
    * @global integer $_LANGID
    */
    function __construct($pageContent)
    {
        global $objDatabase, $_ARRAYLANG, $_LANGID;

        $this->pageContent = $pageContent;
        $this->langId= $_LANGID;

        $this->_objTpl = &new HTML_Template_Sigma('.');
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $this->strImagePath = ASCMS_GALLERY_PATH . '/';
        $this->strImageWebPath = ASCMS_GALLERY_WEB_PATH . '/';
        $this->strThumbnailPath = ASCMS_GALLERY_THUMBNAIL_PATH . '/';
        $this->strThumbnailWebPath = ASCMS_GALLERY_THUMBNAIL_WEB_PATH . '/';

// temp
	$arrGallerySettings = array(
    'header_type'   => 'hierarchy',
    'show_ext'      => 'on',
);
foreach ($arrGallerySettings as $name => $value) {
	$query = "SELECT id FROM `".DBPREFIX."module_gallery_settings` WHERE `name`='".$name."'";
	if (($objSettings = $objDatabase->Execute($query)) !== false) {
		if ($objSettings->RecordCount() == 0) {
			$query = "INSERT INTO `".DBPREFIX."module_gallery_settings` ( `id` , `name` , `value` ) VALUES ('', '".$name."', '".$value."')";
			if ($objDatabase->Execute($query) === false) {
				die("$query:<br />".$objDatabase->ErrorMsg());
			}
		}
	}
}


        $objResult = $objDatabase->Execute('SELECT name, value FROM '.DBPREFIX.'module_gallery_settings');
        while (!$objResult->EOF) {
            $this->arrSettings[$objResult->fields['name']] = $objResult->fields['value'];
            $objResult->MoveNext();
        }
    }

    /**
    * Reads the act and selects the right action
    */
    function getPage()
    {
        if (!isset($_GET['cmd'])) {
            $_GET['cmd'] = '';
        } else {
        	$this->strCmd = '&amp;cmd='.intval($_GET['cmd']);
        }

        if (isset($_GET['pId']) && !empty($_GET['pId'])) {
        	if (isset($_POST['frmGalComAdd_PicId'])) {
                $this->addComment();
                header('location:index.php?section=gallery'.html_entity_decode($this->strCmd, ENT_QUOTES, CONTREXX_CHARSET).'&cid='.
                    intval($_POST['frmGalComAdd_GalId']).'&pId='.
                    intval($_POST['frmGalComAdd_PicId']));
                exit;
            }

            if (isset($_GET['mark'])) {
                $this->countVoting($_GET['pId'],$_GET['mark']);
                header('location:index.php?section=gallery'.html_entity_decode($this->strCmd, ENT_QUOTES, CONTREXX_CHARSET).'&cid='.
                    intval($_GET['cid']).'&pId='.intval($_GET['pId']));
                exit;
            }

            if ($this->arrSettings['enable_popups'] == "on" ) {
                $this->showPicture(intval($_GET['pId']));
            } else {
                $this->showPictureNoPop(intval($_GET['pId']));
            }
        } else {
		    $_GET['cid'] = isset($_GET['cid']) ? intval($_GET['cid']) : intval($_GET['cmd']);
		    $this->showCategoryOverview($_GET['cid']);
		}
        return $this->_objTpl->get();
    }


    /**
     * Show picture in [[CONTENT]] (no popup is used)
     *
     * @param integer $intPicId
     */
    function showPictureNoPop($intPicId)
    {
        global $objDatabase, $_ARRAYLANG, $_CONFIG, $objNavbar;

        $arrPictures = array();
        $intPicId    = intval($intPicId);
        $intCatId    = intval($_GET['cid']);
        $this->_objTpl->setTemplate($this->pageContent);

        // hide category list
        $this->_objTpl->hideBlock('galleryCategories');

        // get category description
        $query = "SELECT value FROM ".DBPREFIX."module_gallery_language ".
            "WHERE gallery_id=$intCatId AND lang_id=$this->langId AND name='desc' ".
            "LIMIT 1";
        $objResult = $objDatabase->Execute($query);
        $strCategoryComment = $objResult->fields['value'];

        $objResult = $objDatabase->Execute(
            "SELECT comment, voting ".
            "FROM ".DBPREFIX."module_gallery_categories WHERE id=$intCatId");
        $boolComment = $objResult->fields['comment'];
        $boolVoting = $objResult->fields['voting'];

        // get picture informations
        $objResult = $objDatabase->Execute(
            "SELECT id, path, link, size_show FROM ".DBPREFIX."module_gallery_pictures ".
            "WHERE id=$intPicId");

        $query = "SELECT p.name, p.desc FROM ".DBPREFIX."module_gallery_language_pics p ".
            "WHERE picture_id=$intPicId AND lang_id=$this->langId LIMIT 1";
        $objSubResult = $objDatabase->Execute($query);
// while? -> if!
        while (!$objResult->EOF) {
            $imageReso = getimagesize($this->strImagePath.$objResult->fields['path']);
            $strImagePath = $this->strImageWebPath.$objResult->fields['path'];
            $imageName = $objSubResult->fields['name'];
            $imageDesc = $objSubResult->fields['desc'];
            $imageSize = round(filesize($this->strImagePath.$objResult->fields['path'])/1024,2);
            $strImageWebPath = ASCMS_PROTOCOL .'://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.'/index.php?section=gallery'.$this->strCmd.'&amp;cid='.$intCatId.'&amp;pId='.$intPicId;
            $objResult->MoveNext();
        }

        // get pictures of the current category
        $objResult = $objDatabase->Execute(
            "SELECT id FROM ".DBPREFIX."module_gallery_pictures ".
            "WHERE status='1' AND validated='1' AND catid=$intCatId ".
            "ORDER BY sorting, id");
        while (!$objResult->EOF) {
            array_push($arrPictures,$objResult->fields['id']);
            $objResult->MoveNext();
        }

        // get next picture id
        if (array_key_exists(array_search($intPicId,$arrPictures)+1,$arrPictures)) {
            $intPicIdNext = $arrPictures[array_search($intPicId,$arrPictures)+1];
        } else {
            $intPicIdNext = $arrPictures[0];
        }

        // get previous picture id
        if (array_key_exists(array_search($intPicId,$arrPictures)-1,$arrPictures)) {
            $intPicIdPrevious = $arrPictures[array_search($intPicId,$arrPictures)-1];
        } else {
            $intPicIdPrevious = end($arrPictures);
        }

        // set language variables
        $this->_objTpl->setVariable(array(
            'TXT_GALLERY_PREVIOUS_IMAGE'        => $_ARRAYLANG['TXT_PREVIOUS_IMAGE'],
            'TXT_GALLERY_NEXT_IMAGE'            => $_ARRAYLANG['TXT_NEXT_IMAGE'],
            'TXT_GALLERY_BACK_OVERVIEW'         => $_ARRAYLANG['TXT_GALLERY_BACK_OVERVIEW'],
            'TXT_GALLERY_CURRENT_IMAGE'         => $_ARRAYLANG['TXT_GALLERY_CURRENT_IMAGE'],
        ));

        $intImageWidth  = '';
        $intImageHeigth = '';
        if ($this->arrSettings['image_width'] < $imageReso[0]) {
            $resizeFactor = $this->arrSettings['image_width'] / $imageReso[0];
            $intImageWidth = $imageReso[0] * $resizeFactor;
            $intImageHeigth = $imageReso[1] * $resizeFactor;

        }
        if (empty($imageDesc)) {
            $imageDesc = '-';
        }

        $strImageTitle = substr(strrchr($strImagePath, '/'), 1);
        // chop the file extension if the settings tell us to do so
        if ($this->arrSettings['show_ext'] == 'off') {
            $strImageTitle = substr($strImageTitle, 0, strrpos($strImageTitle, '.'));
        }
        // set variables
        $this->_objTpl->setVariable(array(
            'GALLERY_PICTURE_ID'        => $intPicId,
            'GALLERY_CATEGORY_ID'       => $intCatId,
            'GALLERY_IMAGE_TITLE'       => $strImageTitle,
            'GALLERY_IMAGE_PATH'        => $strImagePath,
            'GALLERY_IMAGE_PREVIOUS'    => '?section=gallery'.$this->strCmd.'&amp;cid='.$intCatId.'&amp;pId='.$intPicIdPrevious,
            'GALLERY_IMAGE_NEXT'        => '?section=gallery'.$this->strCmd.'&amp;cid='.$intCatId.'&amp;pId='.$intPicIdNext,
            'GALLERY_IMAGE_WIDTH'       => $intImageWidth,
            'GALLERY_IMAGE_HEIGHT'      => $intImageHeigth,
            'GALLERY_IMAGE_LINK'        => $strImageWebPath,
            'GALLERY_IMAGE_NAME'        => $imageName,
            'GALLERY_IMAGE_DESCRIPTION' => $imageDesc,
            'GALLERY_IMAGE_FILESIZE'    => $imageSize.'kB',
        ));

        if ($this->arrSettings['header_type'] == 'hierarchy') {
            $this->_objTpl->setVariable(array(
                'GALLERY_CATEGORY_TREE'     => $this->getCategoryTree(),
                'TXT_GALLERY_CATEGORY_HINT' => $_ARRAYLANG['TXT_GALLERY_CATEGORY_HINT_HIERARCHY'],
            ));
        } else {
            $this->_objTpl->setVariable(array(
                'GALLERY_CATEGORY_TREE'     => $this->getSiblingList(),
                'TXT_GALLERY_CATEGORY_HINT' => $_ARRAYLANG['TXT_GALLERY_CATEGORY_HINT_FLAT'],
            ));
        }

        //voting
        if ($this->arrSettings['show_voting'] == 'on' && $boolVoting) {
            $this->_objTpl->setVariable(array(
                'TXT_VOTING_TITLE'        => $_ARRAYLANG['TXT_VOTING_TITLE'],
                'TXT_VOTING_STATS_ACTUAL' => $_ARRAYLANG['TXT_VOTING_STATS_ACTUAL'],
                'TXT_VOTING_STATS_WITH'   => $_ARRAYLANG['TXT_VOTING_STATS_WITH'],
                'TXT_VOTING_STATS_VOTES'  => $_ARRAYLANG['TXT_VOTING_STATS_VOTES'],
            ));
            if (isset($_COOKIE['Gallery_Voting_'.$intPicId])) {
                //$this->_objTpl->hideBlock('showVotingBar');
                $this->_objTpl->setVariable(array(
                    'TXT_VOTING_ALREADY_VOTED'  => $_ARRAYLANG['TXT_VOTING_ALREADY_VOTED'],
                    'VOTING_ALREADY_VOTED_MARK' => intval($_COOKIE['Gallery_Voting_'.$intPicId])
                ));
            } else {
                $this->_objTpl->setVariable(array(
                    'TXT_VOTING_ALREADY_VOTED'  => '',
                    'VOTING_ALREADY_VOTED_MARK' => ''
                ));
                for ($i=1;$i<=10;$i++) {
                    $this->_objTpl->setVariable(array(
                        'VOTING_BAR_SRC'   => ASCMS_MODULE_IMAGE_WEB_PATH.'/gallery/voting/'.$i.'.gif',
                        'VOTING_BAR_ALT'   => $_ARRAYLANG['TXT_VOTING_RATE'].': '.$i,
                        'VOTING_BAR_MARK'  => $i,
                        'VOTING_BAR_CID'   => $intCatId,
                        'VOTING_BAR_PICID' => $intPicId
                    ));
                    //$this->_objTpl->parse('showVotingBar');
                }
            }

            $objResult = $objDatabase->Execute(
                "SELECT mark FROM ".DBPREFIX."module_gallery_votes ".
                "WHERE picid=$intPicId");
            if ($objResult->RecordCount() > 0) {
                $intCount = 0;
                $intMark  = 0;
                while (!$objResult->EOF) {
                    $intCount++;
                    $intMark = $intMark + intval($objResult->fields['mark']);
                    $objResult->MoveNext();
                }
                $this->_objTpl->setVariable(array(
                    'VOTING_STATS_MARK'  => number_format(round($intMark / $intCount,1),1,'.','\''),
                    'VOTING_STATS_VOTES' => $intCount
                ));
            } else {
                $this->_objTpl->setVariable(array(
                    'VOTING_STATS_MARK'  => 0,
                    'VOTING_STATS_VOTES' => 0
                ));
            }
        } else {
            $this->_objTpl->hideBlock('votingTab');
        }

        // comments
        if ($this->arrSettings['show_comments'] == 'on' && $boolComment) {
            $objResult = $objDatabase->Execute(
                "SELECT date, name, email, www, comment FROM ".DBPREFIX."module_gallery_comments ".
                "WHERE picid=$intPicId ORDER BY date ASC");

            $this->_objTpl->setVariable(array(
                'TXT_COMMENTS_TITLE'        => $objResult->RecordCount().'&nbsp;'.$_ARRAYLANG['TXT_COMMENTS_TITLE'],
                'TXT_COMMENTS_ADD_TITLE'    => $_ARRAYLANG['TXT_COMMENTS_ADD_TITLE'],
                'TXT_COMMENTS_ADD_NAME'     => $_ARRAYLANG['TXT_COMMENTS_ADD_NAME'],
                'TXT_COMMENTS_ADD_EMAIL'    => $_ARRAYLANG['TXT_COMMENTS_ADD_EMAIL'],
                'TXT_COMMENTS_ADD_HOMEPAGE' => $_ARRAYLANG['TXT_COMMENTS_ADD_HOMEPAGE'],
                'TXT_COMMENTS_ADD_TEXT'     => $_ARRAYLANG['TXT_COMMENTS_ADD_TEXT'],
                'TXT_COMMENTS_ADD_SUBMIT'   => $_ARRAYLANG['TXT_COMMENTS_ADD_SUBMIT'],
            ));

            if ($objResult->RecordCount() == 0) { // no comments, hide the block
                //$this->_objTpl->hideBlock('showComments');
            } else {
                $i=0;
                while (!$objResult->EOF) {
                    if ($i % 2 == 0) {
                        $intRowClass = '1';
                    } else {
                        $intRowClass = '2';
                    }

                    if ($objResult->fields['www'] != '') {
                        $strWWW = '<a href="'.$objResult->fields['www'].'"><img alt="'.$objResult->fields['www'].'" src="'.ASCMS_MODULE_IMAGE_WEB_PATH.'/gallery/www.gif" align="baseline" border="0" /></a>';
                    } else {
                        $strWWW = '<img src="'.ASCMS_MODULE_IMAGE_WEB_PATH.'/gallery/pixel.gif" width="16" height="16" alt="" align="baseline" border="0" />';
                    }
                    if ($objResult->fields['email'] != '') {
                        $strEMail = '<a href="mailto:'.$objResult->fields['email'].'"><img alt="'.$objResult->fields['email'].'" src="'.ASCMS_MODULE_IMAGE_WEB_PATH.'/gallery/email.gif" align="baseline" border="0" /></a>';
                    } else {
                        $strEMail = '<img src="'.ASCMS_MODULE_IMAGE_WEB_PATH.'/gallery/pixel.gif" width="16" height="16" alt="" align="baseline" border="0" />';
                    }
                    $this->_objTpl->setVariable(array(
                        'COMMENTS_NAME'     => html_entity_decode($objResult->fields['name']),
                        'COMMENTS_DATE'     => date($_ARRAYLANG['TXT_COMMENTS_DATEFORMAT'],$objResult->fields['date']),
                        'COMMENTS_WWW'      => $strWWW,
                        'COMMENTS_EMAIL'    => $strEMail,
                        'COMMENTS_TEXT'     => nl2br($objResult->fields['comment']),
                        'COMMENTS_ROWCLASS' => $intRowClass
                    ));

                    //$this->_objTpl->parse('showComments');
                    $objResult->MoveNext();
                    $i++;
                }
            }
        } else {
            $this->_objTpl->hideBlock('commentTab');
        }

        //$this->_objTpl->parse('galleryImage');
    }

    /**
    * Show the picture with the id $intPicId (with popup)
    *
    * @global     object        $objDatabase
    * @global     array        $_ARRAYLANG
    * @param     integer        $intPicId: The id of the picture which should be shown
    */
    function showPicture($intPicId)
    {
        global $objDatabase, $_ARRAYLANG;

        $arrPictures = array();
        $intPicId    = intval($intPicId);
        $intCatId    = intval($_GET['cid']);

        // POPUP Code
        $objTpl = &new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/gallery/template');
        $objTpl->loadTemplateFile('module_gallery_show_picture.html',true,true);

        // get category description
        $objResult = $objDatabase->Execute(
            "SELECT value FROM ".DBPREFIX."module_gallery_language ".
            "WHERE gallery_id=$intCatId AND lang_id=$this->langId ".
            "AND name='desc' LIMIT 1");
        $strCategoryComment = $objResult->fields['value'];

        $objResult = $objDatabase->Execute(
            "SELECT comment, voting ".
            "FROM ".DBPREFIX."module_gallery_categories ".
            "WHERE id=$intCatId");
        $boolComment = $objResult->fields['comment'];
        $boolVoting = $objResult->fields['voting'];

        // get picture informations
        $objResult = $objDatabase->Execute(
            "SELECT id, path, link, size_show ".
            "FROM ".DBPREFIX."module_gallery_pictures ".
            "WHERE id=$intPicId");
        $objSubResult = $objDatabase->Execute(
            "SELECT p.name, p.desc FROM ".DBPREFIX."module_gallery_language_pics p ".
            "WHERE picture_id=$intPicId AND lang_id=$this->langId LIMIT 1");
        while (!$objResult->EOF) {
            $imageReso = getimagesize($this->strImagePath.$objResult->fields['path']);
            $strImagePath = $this->strImageWebPath.$objResult->fields['path'];
            $imageName = $objSubResult->fields['name'];
            $imageDesc = $objSubResult->fields['desc'];
            $imageSize = round(filesize($this->strImagePath.$objResult->fields['path'])/1024,2);
            $strImageWebPath = ASCMS_PROTOCOL .'://'.$_SERVER['SERVER_NAME'].ASCMS_PATH_OFFSET.'/index.php?section=gallery'.$this->strCmd.'&amp;cid='.$intCatId.'&amp;pId='.$intPicId;
            $objResult->MoveNext();
        }

        // get pictures of the current category
        $objResult = $objDatabase->Execute(
            "SELECT id FROM ".DBPREFIX."module_gallery_pictures ".
            "WHERE status='1' AND validated='1' AND catid=$intCatId ".
            "ORDER BY sorting, id");
        while (!$objResult->EOF) {
            array_push($arrPictures,$objResult->fields['id']);
            $objResult->MoveNext();
        }

        // get next picture id
        if (array_key_exists(array_search($intPicId,$arrPictures)+1,$arrPictures)) {
            $intPicIdNext = $arrPictures[array_search($intPicId,$arrPictures)+1];
        } else {
            $intPicIdNext = $arrPictures[0];
        }

        // get previous picture id
        if (array_key_exists(array_search($intPicId,$arrPictures)-1,$arrPictures)) {
            $intPicIdPrevious = $arrPictures[array_search($intPicId,$arrPictures)-1];
        } else {
            $intPicIdPrevious = end($arrPictures);
        }

        $strImageTitle = substr(strrchr($strImagePath, '/'), 1);
        // chop the file extension if the settings tell us to do so
        if ($this->arrSettings['show_ext'] == 'off') {
            $strImageTitle = substr($strImageTitle, 0, strrpos($strImageTitle, '.'));
        }

        // set language variables
        $objTpl->setVariable(array(
            'TXT_CLOSE_WINDOW'    => $_ARRAYLANG['TXT_CLOSE_WINDOW'],
            'TXT_ZOOM_OUT'        => $_ARRAYLANG['TXT_ZOOM_OUT'],
            'TXT_ZOOM_IN'         => $_ARRAYLANG['TXT_ZOOM_IN'],
            'TXT_CHANGE_BG_COLOR' => $_ARRAYLANG['TXT_CHANGE_BG_COLOR'],
            'TXT_PRINT'           => $_ARRAYLANG['TXT_PRINT'],
            'TXT_PREVIOUS_IMAGE'  => $_ARRAYLANG['TXT_PREVIOUS_IMAGE'],
            'TXT_NEXT_IMAGE'      => $_ARRAYLANG['TXT_NEXT_IMAGE'],
            'TXT_USER_DEFINED'    => $_ARRAYLANG['TXT_USER_DEFINED']
        ));
        // set variables
        $objTpl->setVariable(array(
            'GALLERY_WINDOW_WIDTH'  => $imageReso[0] < 420 ? 500 : $imageReso[0]+80,
            'GALLERY_WINDOW_HEIGHT' => $imageReso[1]+120,
            'GALLERY_PICTURE_ID'    => $intPicId,
            'GALLERY_CATEGORY_ID'   => $intCatId,
            'GALLERY_TITLE'         => $strCategoryComment,
            'IMAGE_THIS'            => $strImagePath,
            'IMAGE_PREVIOUS'        => '?section=gallery'.$this->strCmd.'&amp;cid='.$intCatId.'&amp;pId='.$intPicIdPrevious,
            'IMAGE_NEXT'            => '?section=gallery'.$this->strCmd.'&amp;cid='.$intCatId.'&amp;pId='.$intPicIdNext,
            'IMAGE_WIDTH'           => $imageReso[0],
            'IMAGE_HEIGHT'          => $imageReso[1],
            'IMAGE_LINK'            => $strImageWebPath,
            'IMAGE_NAME'            => $strImageTitle, //$imageName,
            'IMAGE_DESCRIPTION'     => $_ARRAYLANG['TXT_IMAGE_NAME'].': '.$imageName.'<br />'.$_ARRAYLANG['TXT_FILESIZE'].': '.$imageSize.' kB<br />'.$_ARRAYLANG['TXT_RESOLUTION'].': '.$imageReso[0].'x'.$imageReso[1].' Pixel',
            'IMAGE_DESC'            => (!empty($imageDesc)) ? $imageDesc.'<br /><br />' : '',
        ));

        //voting
        if ($this->arrSettings['show_voting'] == 'on'    && $boolVoting) {
            $objTpl->setVariable(array(
                'TXT_VOTING_TITLE'        => $_ARRAYLANG['TXT_VOTING_TITLE'],
                'TXT_VOTING_STATS_ACTUAL' => $_ARRAYLANG['TXT_VOTING_STATS_ACTUAL'],
                'TXT_VOTING_STATS_WITH'   => $_ARRAYLANG['TXT_VOTING_STATS_WITH'],
                'TXT_VOTING_STATS_VOTES'  => $_ARRAYLANG['TXT_VOTING_STATS_VOTES'],
            ));
            if (isset($_COOKIE["Gallery_Voting_$intPicId"])) {
                //$objTpl->hideBlock('showVotingBar');

                $objTpl->setVariable(array(
                    'TXT_VOTING_ALREADY_VOTED'  => $_ARRAYLANG['TXT_VOTING_ALREADY_VOTED'],
                    'VOTING_ALREADY_VOTED_MARK' => intval($_COOKIE['Gallery_Voting_'.$intPicId])
                ));
            } else {
                $objTpl->setVariable(array(
                    'TXT_VOTING_ALREADY_VOTED'  => '',
                    'VOTING_ALREADY_VOTED_MARK' => ''
                ));
                for ($i=1;$i<=10;$i++) {
                        $objTpl->setVariable(array(
                            'VOTING_BAR_SRC'   => ASCMS_MODULE_IMAGE_WEB_PATH.'/gallery/voting/'.$i.'.gif',
                            'VOTING_BAR_ALT'   => $_ARRAYLANG['TXT_VOTING_RATE'].': '.$i,
                            'VOTING_BAR_MARK'  => $i,
                            'VOTING_BAR_CID'   => $intCatId,
                            'VOTING_BAR_PICID' => $intPicId
                        ));
                    //$objTpl->parse('showVotingBar');
                }
            }

            $objResult = $objDatabase->Execute(
                "SELECT mark FROM ".DBPREFIX."module_gallery_votes ".
                "WHERE picid=$intPicId");
            if ($objResult->RecordCount() > 0) {
                $intCount = 0;
                while (!$objResult->EOF) {
                    $intCount++;
                    $intMark = $intMark + intval($objResult->fields['mark']);
                    $objResult->MoveNext();
                }
                $objTpl->setVariable(array(
                    'VOTING_STATS_MARK'  => number_format(round($intMark / $intCount,1),1,'.','\''),
                    'VOTING_STATS_VOTES' => $intCount
                ));
            } else {
                $objTpl->setVariable(array(
                    'VOTING_STATS_MARK'  => 0,
                    'VOTING_STATS_VOTES' => 0
                ));
            }
        } else {
            $objTpl->hideBlock('votingTab');
        }
        //comments
        if ($this->arrSettings['show_comments'] == 'on' && $boolComment) {
            $objResult = $objDatabase->Execute(
                "SELECT date, name, email, www, comment FROM ".DBPREFIX."module_gallery_comments ".
                "WHERE picid=$intPicId ORDER BY date ASC");

            $objTpl->setVariable(array(
                'TXT_COMMENTS_TITLE'        => $objResult->RecordCount().'&nbsp;'.$_ARRAYLANG['TXT_COMMENTS_TITLE'],
                'TXT_COMMENTS_ADD_TITLE'    => $_ARRAYLANG['TXT_COMMENTS_ADD_TITLE'],
                'TXT_COMMENTS_ADD_NAME'     => $_ARRAYLANG['TXT_COMMENTS_ADD_NAME'],
                'TXT_COMMENTS_ADD_EMAIL'    => $_ARRAYLANG['TXT_COMMENTS_ADD_EMAIL'],
                'TXT_COMMENTS_ADD_HOMEPAGE' => $_ARRAYLANG['TXT_COMMENTS_ADD_HOMEPAGE'],
                'TXT_COMMENTS_ADD_TEXT'     => $_ARRAYLANG['TXT_COMMENTS_ADD_TEXT'],
                'TXT_COMMENTS_ADD_SUBMIT'   => $_ARRAYLANG['TXT_COMMENTS_ADD_SUBMIT'],
            ));

            if ($objResult->RecordCount() == 0) { // no comments, hide the block
                //$objTpl->hideBlock('showComments');
            } else {
                $i=0;
                while (!$objResult->EOF) {
                    if ($i % 2 == 0) {
                        $intRowClass = '1';
                    } else {
                        $intRowClass = '2';
                    }

                    if ($objResult->fields['www'] != '') {
                        $strWWW = '<a href="'.$objResult->fields['www'].'"><img alt="'.$objResult->fields['www'].'" src="'.ASCMS_MODULE_IMAGE_WEB_PATH.'/gallery/www.gif" align="baseline" border="0" /></a>';
                    } else {
                        $strWWW = '<img src="'.ASCMS_MODULE_IMAGE_WEB_PATH.'/gallery/pixel.gif" width="16" height="16" alt="" align="baseline" border="0" />';
                    }
                    if ($objResult->fields['email'] != '') {
                        $strEMail = '<a href="mailto:'.$objResult->fields['email'].'"><img alt="'.$objResult->fields['email'].'" src="'.ASCMS_MODULE_IMAGE_WEB_PATH.'/gallery/email.gif" align="baseline" border="0" /></a>';
                    } else {
                        $strEMail = '<img src="'.ASCMS_MODULE_IMAGE_WEB_PATH.'/gallery/pixel.gif" width="16" height="16" alt="" align="baseline" border="0" />';
                    }
                    $objTpl->setVariable(array(
                        'COMMENTS_NAME'     => html_entity_decode($objResult->fields['name']),
                        'COMMENTS_DATE'     => date($_ARRAYLANG['TXT_COMMENTS_DATEFORMAT'],$objResult->fields['date']),
                        'COMMENTS_WWW'      => $strWWW,
                        'COMMENTS_EMAIL'    => $strEMail,
                        'COMMENTS_TEXT'     => nl2br($objResult->fields['comment']),
                        'COMMENTS_ROWCLASS' => $intRowClass
                    ));

                    //$objTpl->parse('showComments');
                    $objResult->MoveNext();
                    $i++;
                }
            }
        } else {
            $objTpl->hideBlock('commentTab');
        }
        $objTpl->show();
        die;
    }

    /**
     * Shows the Category-Tree
     *
     * @global  array       $_ARRAYLANG     Language array
     * @global  mixed       $objDatabase    Database object
     * @return  string                      The category tree
     */
    function getCategoryTree()
    {
        global $_ARRAYLANG, $objDatabase;

        $strOutput = '<a href="?section=gallery" target="_self">'.$_ARRAYLANG['TXT_GALLERY'].'</a>';

        if (isset($_GET['cid'])) {
            $intCatId = intval($_GET['cid']);

            $objResult = $objDatabase->Execute(
                "SELECT value FROM ".DBPREFIX."module_gallery_language ".
                "WHERE gallery_id=$intCatId AND lang_id=$this->langId ".
                "AND name='name' LIMIT 1");
            $strCategory1 = $objResult->fields['value'];

            $objResult = $objDatabase->Execute(
                "SELECT pid FROM ".DBPREFIX."module_gallery_categories WHERE id=$intCatId");

            if ($objResult->fields['pid'] != 0) {
                $intParentId = $objResult->fields['pid'];
                $objResult = $objDatabase->Execute(
                    "SELECT value FROM ".DBPREFIX."module_gallery_language ".
                    "WHERE gallery_id=$intParentId AND lang_id=$this->langId ".
                    "AND name='name' LIMIT 1");
                $strCategory2 = $objResult->fields['value'];
            }

            if (isset($strCategory2)) { // this is a subcategory
                $strOutput .= ' / <a href="?section=gallery&amp;cid='.$intParentId.'" title="'.$strCategory2.'" target="_self">'.$strCategory2.'</a>';
                $strOutput .= ' / <a href="?section=gallery&amp;cid='.$intCatId.'" title="'.$strCategory1.'" target="_self">'.$strCategory1.'</a>';
            } else {
                $strOutput .= ' / <a href="?section=gallery&amp;cid='.$intCatId.'" title="'.$strCategory1.'" target="_self">'.$strCategory1.'</a>';
            }
        }
        return $strOutput;
    }

    /**
     * Not unlike {@link getCategoryTree()}, but instead of a tree, this returns
     * a list of siblings of the current gallery
     */
    function getSiblingList() {
        global $objDatabase;

        if (isset($_GET['cid'])) {
            $intCatId = intval($_GET['cid']);
            $objResult = $objDatabase->Execute(
                "SELECT pid FROM ".DBPREFIX."module_gallery_categories ".
                "WHERE id=$intCatId");
            if ($objResult) {
                $intParentId = intval($objResult->Fields('pid'));
                $query = "SELECT id, value FROM ".DBPREFIX."module_gallery_categories ".
                    "INNER JOIN ".DBPREFIX."module_gallery_language ON id=gallery_id ".
                    "WHERE lang_id=$this->langId AND name='name' AND pid=$intParentId";
                $objResult = $objDatabase->Execute($query);
                if ($objResult) {
                    $strOutput = '| ';
                    do {
                        $strOutput .= "<a href='?section=gallery&amp;cid=".
                            $objResult->Fields('id').
                            "' title='".$objResult->Fields('value').
                            "' target='_self'>".$objResult->Fields('value')."</a> | ";
                    } while ($objResult->MoveNext());
                    return $strOutput;
                }
            }
        }
        return '';
    }


    /**
     * Returns the name of the currently visible top level gallery
     * @return  string          The gallery name, or '' if not applicable
     */
    function getTopGalleryName() {
        global $objDatabase;

        if (isset($_GET['cid'])) {
            $intCatId = intval($_GET['cid']);

            $running = true;
            while ($running) {
                $query = "SELECT pid FROM ".DBPREFIX."module_gallery_categories ".
                    "WHERE id=$intCatId";
                $objResult = $objDatabase->Execute($query);
                if ($objResult) {
                    if ($objResult->Fields('pid') != 0) {
                        $intCatId = $objResult->Fields('pid');
                    } else {
                        $running = false;
                    }
                }
            }

            $query = "SELECT value FROM ".DBPREFIX."module_gallery_language ".
                "WHERE gallery_id=$intCatId AND lang_id=$this->langId ".
                "AND name='name' LIMIT 1";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
                $galleryName = $objResult->Fields('value');
                return $galleryName;
            }
        }
        // category is not set
        // we're not inside a subgallery nor showing a picture yet.
        return '';
    }


    /**
     * Shows the Overview of categories
     *
     * @global  array   $_ARRAYLANG
     * @global  object  $objDatabase
     * @param   var     $intParentId
     */
    function showCategoryOverview($intParentId=0) {
        global $objDatabase, $_ARRAYLANG;

        $intParentId = intval($intParentId);

        $this->_objTpl->setTemplate($this->pageContent, true, true);

        // hide image detail block
        //$this->_objTpl->hideBlock('galleryImage');

        if ($this->arrSettings['header_type'] == 'hierarchy') {
            $this->_objTpl->setVariable(array(
                'GALLERY_CATEGORY_TREE'     => $this->getCategoryTree(),
                'TXT_GALLERY_CATEGORY_HINT' => $_ARRAYLANG['TXT_GALLERY_CATEGORY_HINT_HIERARCHY'],
            ));
        } else {
            $this->_objTpl->setVariable(array(
                'GALLERY_CATEGORY_TREE'     => $this->getSiblingList(),
                'TXT_GALLERY_CATEGORY_HINT' => $_ARRAYLANG['TXT_GALLERY_CATEGORY_HINT_FLAT'],
            ));
        }

        $objResult = $objDatabase->Execute(
            "SELECT id, catid, path FROM ".DBPREFIX."module_gallery_pictures ".
            "ORDER BY catimg ASC, sorting ASC, id ASC");
        while (!$objResult->EOF) {
            $arrImageSizes[$objResult->fields['catid']][$objResult->fields['id']] = round(filesize($this->strImagePath.$objResult->fields['path'])/1024,2);
            $arrstrImagePaths[$objResult->fields['catid']][$objResult->fields['id']] = $this->strThumbnailWebPath.$objResult->fields['path'];
            $objResult->MoveNext();
        }

        if (isset($arrImageSizes) && isset($arrstrImagePaths)) {
            foreach ($arrImageSizes as $keyCat => $valueCat) {
                $arrCategorySizes[$keyCat] = 0;
                foreach ($valueCat as $keyImage => $valueImageSize) {
                    $arrCategorySizes[$keyCat] = $arrCategorySizes[$keyCat] + $valueImageSize;
                }
            }
            foreach ($arrstrImagePaths as $keyCat => $valueCat) {
                $arrCategoryImages[$keyCat] = 0;
                $arrCategoryImageCounter[$keyCat] = 0;
                foreach ($valueCat as $keyImage => $valuestrImagePath) {
                    $arrCategoryImages[$keyCat]    = $valuestrImagePath;
                    $arrCategoryImageCounter[$keyCat] = $arrCategoryImageCounter[$keyCat] + 1;
                }
            }
        }
        //$arrCategorySizes            ->        Sizes of all Categories
        //$arrCategoryImages        ->        The First Picture of each category
        //$arrCategoryImageCounter    ->        Counts all images in one group

        $query = "SELECT * FROM ".DBPREFIX."module_gallery_categories ".
            "WHERE pid=$intParentId AND status='1' ORDER BY sorting ASC";
        $objResult = $objDatabase->Execute($query);
        if ($objResult->RecordCount() == 0) {

            // no categories in the database, hide the output
            //$this->_objTpl->hideBlock('galleryCategoryList');
        } else {
            $i = 1;
            while (!$objResult->EOF) {
                $objSubResult = $objDatabase->Execute(
                    "SELECT name, value FROM ".DBPREFIX."module_gallery_language ".
                    "WHERE gallery_id=".$objResult->fields['id']." AND ".
                    "lang_id=".intval($this->langId)." ORDER BY name ASC");
                unset($arrCategoryLang);
                while (!$objSubResult->EOF) {
                    $arrCategoryLang[$objSubResult->fields['name']] = $objSubResult->fields['value'];
                    $objSubResult->MoveNext();
                }

                if (empty($arrCategoryImages[$objResult->fields['id']])) {
                    // no pictures in this gallery, show the empty-image
                    $strName     = $arrCategoryLang['name'];
                    $strDesc    = $arrCategoryLang['desc'];
                    $strImage     = '<a href="?section=gallery&amp;cid='.$objResult->fields['id'].$this->strCmd.'" target="_self">';
                    $strImage     .= '<img border="0" alt="'.$arrCategoryLang['name'].'" src="images/modules/gallery/no_images.gif" /></a>';
                    $strInfo     = $_ARRAYLANG['TXT_IMAGE_COUNT'].': 0';
                    $strInfo     .= '<br />'.$_ARRAYLANG['TXT_SIZE'].': 0kB';
                } else {
                    $strName    = $arrCategoryLang['name'];
                    $strDesc    = $arrCategoryLang['desc'];
                    $strImage     = '<a href="?section=gallery&amp;cid='.$objResult->fields['id'].$this->strCmd.'" target="_self">';
                    $strImage     .= '<img border="0" alt="'.$arrCategoryLang['name'].'" src="'.$arrCategoryImages[$objResult->fields['id']].'" /></a>';
                    $strInfo     = $_ARRAYLANG['TXT_IMAGE_COUNT'].': '.$arrCategoryImageCounter[$objResult->fields['id']];
                    $strInfo     .= '<br />'.$_ARRAYLANG['TXT_SIZE'].': '.$arrCategorySizes[$objResult->fields['id']].'kB';
                }

                $this->_objTpl->setVariable(array(
                    'GALLERY_STYLE'                => ($i % 2)+1,
                    'GALLERY_CATEGORY_NAME'        => $strName,
                    'GALLERY_CATEGORY_IMAGE'       => $strImage,
                    'GALLERY_CATEGORY_INFO'        => $strInfo,
                    'GALLERY_CATEGORY_DESCRIPTION' => $strDesc
                ));
                $this->_objTpl->parse('galleryCategories');
                $i++;

                $objResult->MoveNext();
            }
        }

        //images
        $this->_objTpl->setVariable(array(
            'GALLERY_JAVASCRIPT'    =>    $this->getJavascript()
            ));

        $objResult = $objDatabase->Execute(
            "SELECT value FROM ".DBPREFIX."module_gallery_language ".
            "WHERE gallery_id=$intParentId AND lang_id=$this->langId AND name='desc'");
        $strCategoryComment = $objResult->fields['value'];

        $objResult = $objDatabase->Execute(
            "SELECT comment,voting FROM ".DBPREFIX."module_gallery_categories ".
            "WHERE id=".intval($intParentId));
        $boolComment = $objResult->fields['comment'];
        $boolVoting = $objResult->fields['voting'];

        // paging
        $intPos = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;
        $objResult = $objDatabase->Execute(
            "SELECT id, path, link, size_show FROM ".DBPREFIX."module_gallery_pictures ".
            "WHERE status='1' AND validated='1' AND catid=$intParentId ".
            "ORDER BY sorting");
        $intCount = $objResult->RecordCount();
        $this->_objTpl->setVariable(array(
            'GALLERY_PAGING'     => getPaging($intCount, $intPos, '&amp;section=gallery&amp;cid='.$intParentId.$this->strCmd, '<b>'.$_ARRAYLANG['TXT_IMAGES'].'</b>',false,intval($this->arrSettings['paging']))
            ));
        // end paging

        $objResult = $objDatabase->SelectLimit(
            "SELECT id, path, link, size_show FROM ".DBPREFIX."module_gallery_pictures ".
            "WHERE status='1' AND validated='1' AND catid=$intParentId ".
            "ORDER BY sorting", intval($this->arrSettings["paging"]), $intPos);
        if ($objResult->RecordCount() == 0) {
            // No images in the category
            if (empty($strCategoryComment)) {
                $this->_objTpl->hideBlock('galleryImageBlock');
            } else {
                $this->_objTpl->setVariable(array('GALLERY_CATEGORY_COMMENT' =>    $strCategoryComment));
            }
        } else {
            $this->_objTpl->setVariable(array('GALLERY_CATEGORY_COMMENT' =>    $strCategoryComment));
            $intFillLastRow = 1;
            while (!$objResult->EOF) {
                $objSubResult = $objDatabase->Execute(
                    "SELECT p.name, p.desc FROM ".DBPREFIX."module_gallery_language_pics p ".
                    "WHERE picture_id=".$objResult->fields['id']." AND lang_id=$this->langId LIMIT 1");

                $imageFileSize = round(filesize($this->strImagePath.$objResult->fields['path'])/1024,2);
                $imageReso = getimagesize($this->strImagePath.$objResult->fields['path']);
                $strImagePath = $this->strImageWebPath.$objResult->fields['path'];
                $imageThumbPath = $this->strThumbnailWebPath.$objResult->fields['path'];
                $imageName = $objSubResult->fields['name'];
                $imageLinkName = $objSubResult->fields['desc'];
                $imageLink = $objResult->fields['link'];
                $imageSizeShow = $objResult->fields['size_show'];
                $imageLinkOutput = '';
                $imageSizeOutput = '';

                // chop the file extension if the settings tell us to do so
                if ($this->arrSettings['show_ext'] == 'off') {
                    $imageName = substr($imageName, 0, strrpos($imageName, '.'));
                }

                if ($this->arrSettings['enable_popups'] == "on") {
                    $strImageOutput = '<a href="javascript:openWindow(\'';
                    $strImageOutput .= '?section=gallery'.$this->strCmd.'&amp;cid='.$intParentId.'&amp;pId='.$objResult->fields['id'];
                    $strImageOutput .= '\',\'\',\'width=';
                    $strImageOutput .= $imageReso[0]+25;
                    $strImageOutput .= ',height=';
                    $strImageOutput .= $imageReso[1]+25;
                    $strImageOutput .= ',resizable=yes';
                    $strImageOutput .= ',status=no';
                    $strImageOutput .= ',scrollbars=yes';
                    $strImageOutput .= '\')"><img border="0" title="'.$imageName.'" src="';
                    $strImageOutput .= $imageThumbPath;
                    $strImageOutput .= '" alt="';
                    $strImageOutput .= $imageName;
                    $strImageOutput .= '" /></a>';
                } else {
                    $strImageOutput = '<a href=?section=gallery'.$this->strCmd.'&amp;cid='.$intParentId.'&amp;pId='.$objResult->fields['id'].'>';
                    $strImageOutput .= '<img border="0" title="'.$imageName.'" src="'.$imageThumbPath.'"';
                    $strImageOutput .= 'alt="'.$imageName.'" /></a>';

                }

                if ($this->arrSettings['show_names'] == 'on') {
                    $imageSizeOutput = $imageName;
                    if ($imageSizeShow == '1') {
                        // the size of the file has to be shown
                        $imageSizeOutput .= ' ('.$imageFileSize.' kB)<br />';
                    }
                }

                if ($this->arrSettings['show_comments'] == 'on' && $boolComment) {
                    $objSubResult = $objDatabase->Execute(
                        "SELECT id FROM ".DBPREFIX."module_gallery_comments ".
                        "WHERE picid=".$objResult->fields['id']);
                    if ($objSubResult->RecordCount() > 0) {
                        if ($objSubResult->RecordCount() == 1) {
                            $imageCommentOutput = '1 '.$_ARRAYLANG['TXT_COMMENTS_ADD_TEXT'].'<br />';
                        } else {
                            $imageCommentOutput = $objSubResult->RecordCount().' '.$_ARRAYLANG['TXT_COMMENTS_ADD_COMMENTS'].'<br />';
                        }
                    } else {
                        $imageCommentOutput = '';
                    }
                }

                if ($this->arrSettings['show_voting'] == 'on' && $boolVoting) {
                    $objSubResult = $objDatabase->Execute(
                        "SELECT mark FROM ".DBPREFIX."module_gallery_votes ".
                        "WHERE picid=".$objResult->fields["id"]);
                    if ($objSubResult->RecordCount() > 0) {
                        $intMark = 0;
                        while (!$objSubResult->EOF) {
                            $intMark = $intMark + $objSubResult->fields['mark'];
                            $objSubResult->MoveNext();
                        }
                        $imageVotingOutput = $_ARRAYLANG['TXT_VOTING_SCORE'].'&nbsp;&Oslash;'.number_format(round($intMark / $objSubResult->RecordCount(),1),1,'.','\'').'<br />';
                    } else {
                        $imageVotingOutput = '';
                    }
                }

                if (!empty($imageLinkName)) {
                    if (!empty($imageLink)) {
                        $imageLinkOutput = '<a href="'.$imageLink.'" target="_blank">'.$imageLinkName.'</a>';
                    } else {
                        $imageLinkOutput = $imageLinkName;
                    }
                } else {
                    if (!empty($imageLink)) {
                        $imageLinkOutput = '<a href="'.$imageLink.'" target="_blank">'.$imageLink.'</a>';
                    }
                }

                $this->_objTpl->setVariable(array(
                    'GALLERY_IMAGE_LINK'.$intFillLastRow => $imageSizeOutput.$imageCommentOutput.$imageVotingOutput.$imageLinkOutput,
                    'GALLERY_IMAGE'.$intFillLastRow      => $strImageOutput
                    ));

                if ($intFillLastRow == 3) {
                    // Parse the data after every third image
                    $this->_objTpl->parse('galleryShowImages');
                    $intFillLastRow = 1;
                } else {
                    $intFillLastRow++;
                }
                $objResult->MoveNext();
            }
            if ($intFillLastRow == 2) {
                $this->_objTpl->setVariable(array(
                    'GALLERY_IMAGE'.$intFillLastRow      => '',
                    'GALLERY_IMAGE_LINK'.$intFillLastRow => ''
                ));
                $intFillLastRow++;
            }
            if ($intFillLastRow == 3) {
                $this->_objTpl->setVariable(array(
                    'GALLERY_IMAGE'.$intFillLastRow      => '',
                    'GALLERY_IMAGE_LINK'.$intFillLastRow => ''
                ));
                $this->_objTpl->parse('galleryShowImages');
            }
        }

        $this->_objTpl->parse('galleryCategories');
    }


    /**
    * Writes the javascript-function into the template
    *
    */
    function getJavascript()
    {
        $javascript = <<<END
<script language="JavaScript" type="text/JavaScript">
function openWindow(theURL,winName,features) {
    galleryPopup = window.open(theURL,"gallery",features);
    galleryPopup.focus();
}
</script>
END;
        return $javascript;
    }


    /**
    * Add a new comment to database
    * @global     array        $_POST
    * @global     array        $_SERVER
    * @global     object        $objDatabase
    */
    function addComment()
    {
        global $objDatabase, $objCache;

        $intPicId    = intval($_POST['frmGalComAdd_PicId']);
        $strName     = htmlspecialchars(strip_tags($_POST['frmGalComAdd_Name']), ENT_QUOTES, CONTREXX_CHARSET);
        $strEMail    = $_POST['frmGalComAdd_Email'];
        $strWWW        = htmlspecialchars(strip_tags($_POST['frmGalComAdd_Homepage']), ENT_QUOTES, CONTREXX_CHARSET);
        $strComment = htmlspecialchars(strip_tags($_POST['frmGalComAdd_Text']), ENT_QUOTES, CONTREXX_CHARSET);

        if (!empty($strWWW) && $strWWW != 'http://') {
            if (substr($strWWW,0,7) != 'http://') {
                $strWWW = 'http://'.$strWWW;
            }
        } else {
            $strWWW = '';
        }

        if (!ereg("^.+@.+\\..+$", $strEMail)) {
            $strEMail = '';
        } else {
            $strEmail = htmlspecialchars(strip_tags($strEMail), ENT_QUOTES, CONTREXX_CHARSET);
        }

        if ($this->arrSettings['show_comments'] == 'on' &&
            $intPicId != 0 &&
            !empty($strName) &&
            !empty($strComment))
        {
            $strQuery = '
                    ';
            $objDatabase->Execute(
                'INSERT INTO '.DBPREFIX.'module_gallery_comments '.
                'SET picid='.$intPicId.', date='.time().', ip="'.$_SERVER['REMOTE_ADDR'].'", '.
                'name="'.$strName.'", email="'.$strEMail.'", www="'.$strWWW.'", comment="'.$strComment.'"');
            $objCache->deleteAllFiles();
        }
    }


    /**
    * Add a new voting to database
    * @global     array        $_SERVER
    * @global     array        $_COOKIE
    * @global     object        $objDatabase
    * @param     integer        $intPicId: The picture with this id will be rated
    * @param     integer        $intMark: This mark will be set for the picture
    */
    function countVoting($intPicId,$intMark)
    {
        global $objDatabase, $objCache;

        $intPicId = intval($intPicId);
        $intMark = intval($intMark);
        $strMd5 = md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);

        $intCookieTime = time()+7*24*60*60;
        $intVotingCheckTime = time()-(12*60*60);

        $objResult = $objDatabase->Execute(
            "SELECT id FROM ".DBPREFIX."module_gallery_votes ".
            "WHERE ip='".$_SERVER['REMOTE_ADDR']."' AND md5='".$strMd5.
            "' AND date > $intVotingCheckTime AND picid=$intPicId LIMIT 1");
        if ($objResult->RecordCount() == 1) {
            $boolIpCheck = false;
            setcookie('Gallery_Voting_'.$intPicId,$intMark,$intCookieTime);
        } else {
            $boolIpCheck = true;
        }

        if ($this->arrSettings['show_voting'] == 'on' &&
            $intPicId != 0    &&
            $intMark >= 1     &&
            $intMark <= 10    &&
            $boolIpCheck    &&
            !isset($_COOKIE['Gallery_Voting_'.$intPicId])) {
            $objDatabase->Execute(
                "INSERT INTO ".DBPREFIX."module_gallery_votes ".
                "SET picid=$intPicId, date=".time().", ip='".$_SERVER['REMOTE_ADDR']."', ".
                "md5='".$strMd5."', mark=$intMark");
            setcookie('Gallery_Voting_'.$intPicId,$intMark,$intCookieTime);

            $objCache->deleteAllFiles();
        }
    }
}
?>
