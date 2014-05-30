<?php

namespace Cx\Core\Setting\Model\Entity;
use \Cx\Core\Setting\Model\Entity\Engine;

class FileSystem implements Engine{
    
    /**
     * The array of currently loaded settings, like
     *  array(
     *    'name' => array(
     *      'section' => section,
     *      'group' => group,
     *      'value' => current value,
     *      'type' => element type (text, dropdown, ... [more to come]),
     *      'values' => predefined values (for dropdown),
     *      'ord' => ordinal number (for sorting),
     *    ),
     *    ... more ...
     *  );
     * @var     array
     * @static
     * @access  private
     */
    public static $arrSettings = null;
    
    /**
     * The group last used to {@see init()} the settings.
     * Defaults to null (ignored).
     * @var     string
     * @static
     * @access  private
     */
    private static $group = null;

    /**
     * The section last used to {@see init()} the settings.
     * Defaults to null (which will cause an error in most methods).
     * @var     string
     * @static
     * @access  private
     */
    private static $section = null;
    
     /**
     * Changed flag
     *
     * This flag is set to true as soon as any change to the settings is detected.
     * It is cleared whenever {@see updateAll()} is called.
     * @var     boolean
     * @static
     * @access  private
     */
    private static $changed = false;
    
    /**
     * Returns the current value of the changed flag.
     *
     * If it returns true, you probably want to call {@see updateAll()}.
     * @return  boolean           True if values have been changed in memory,
     *                            false otherwise
     */
    static function changed()
    {
        return self::$changed;
    }

    /**
     * Tab counter for the {@see show()} and {@see show_external()}
     * @var     integer
     * @access  private
     */
    public static $tab_index = 1;


    /**
     * Optionally sets and returns the value of the tab index
     * @param   integer             The optional new tab index
     * @return  integer             The current tab index
     */
    static function tab_index($tab_index=null)
    {
        if (isset($tab_index)) {
            self::$tab_index = intval($tab_index);
        }
        return self::$tab_index;
    }
    
    /**
     * Initialize the settings entries from the file with key/value pairs
     * for the current section and the given group
     *
     * An empty $group value is ignored.  All records with the section are
     * included in this case.
     * Note that all setting names *SHOULD* be unambiguous for the entire
     * section.  If there are two settings with the same name but different
     * $group values, the second one may overwrite the first!
     * @internal  The records are ordered by
     *            `group` ASC, `ord` ASC, `name` ASC
     * @param   string    $section    The section
     * @param   string    $group      The optional group.
     *                                Defaults to null
     * @return  boolean               True on success, false otherwise
     * @global  ADOConnection   $objDatabase
     */
    static function init($section, $group=null) {
        //File Path
        $filename=ASCMS_CORE_PATH .'/Setting/Data/'.$section.'.yml';
        self::flush();
        self::$section=$section;
        self::$group=$group;
       
        //call DataSet importFromFile method @return array
        $objDataSet = \Cx\Core_Modules\Listing\Model\Entity\DataSet::importFromFile(new \Cx\Core_Modules\Listing\Model\Entity\Yaml(), $filename);
        if(!empty($objDataSet))
        {
            foreach($objDataSet as $value)
            {
                self::$arrSettings[$value['name']]= $value;
             
            }
        }
    }
    
    /** 
     * Flush the stored settings
     *
     * Resets the class to its initial state.
     * Does *NOT* clear the section, however.
     * @return  void
     */
    static function flush()
    {
        self::$arrSettings = null;
        self::$section = null;
        self::$group = null;
        self::$changed = null;
    }
    
    /** 
     * Returns the settings array for the given section and group
     *
     * See {@see init()} on how the arguments are used.
     * If the method is called successively using the same $group argument,
     * the current settings are returned without calling {@see init()}.
     * Thus, changes made by calling {@see set()} will be preserved.
     * @param   string    $section    The section
     * @param   string    $group        The optional group
     * @return  array                 The settings array on success,
     *                                false otherwise
     */
    static function getArray($section, $group=null)
    {
        if (self::$section !== $section
         || self::$group !== $group) {
            if (!self::init($section, $group)) return false;
        }
        return self::$arrSettings;
    }


    /**
     * Returns the settings value stored in the object for the name given.
     *
     * If the settings have not been initialized (see {@see init()}), or
     * if no setting of that name is present in the current set, null
     * is returned.
     * @param   string    $name       The settings name
     * @return  mixed                 The settings value, if present,
     *                                null otherwise
     */
    static function getValue($name)
    {
        if (is_null(self::$arrSettings)) {
        \DBG::log("self::getValue($name): ERROR: no settings loaded");
            return null;
        }

        if (isset(self::$arrSettings[$name]['value'])) {
            return self::$arrSettings[$name]['value'];
        };

        return null;
    }

    /**
     * Add a new record to the settings
     *
     * The class *MUST* have been initialized by calling {@see init()}
     * or {@see getArray()} before this method is called.
     * The present $group stored in the class is used as a default.
     * If the current class $group is empty, it *MUST* be specified in the call.
     * @param   string    $name     The setting name
     * @param   string    $value    The value
     * @param   integer   $ord      The ordinal value for sorting,
     *                              defaults to 0
     * @param   string    $type     The element type for displaying,
     *                              defaults to 'text'
     * @param   string    $values   The values for type 'dropdown',
     *                              defaults to the empty string
     * @param   string    $group      The optional group
     * @return  boolean             True on success, false otherwise
     */
    static function add( $name, $value, $ord=false, $type='text', $values='', $group=null)
    {
        if (!isset(self::$section)) {
            // TODO: Error message
            \DBG::log("self::add(): ERROR: Empty section!");
            return false;
        }
        
        // Fail if the name is invalid
        if (empty($name)) {
            \DBG::log("self::add(): ERROR: Empty name!");
            return false;
        }

        // This can only be done with a non-empty group!
        // Use the current group, if present, otherwise fail
        if (!$group) {
            if (!self::$group) {
                \DBG::log("self::add(): ERROR: Empty group!");
                return false;
            }
            $group = self::$group;
        }
        
        // Initialize if necessary
        if (is_null(self::$arrSettings) || self::$group != $group){
            self::init(self::$section, $group);
        }
        
        // Such an entry exists already, fail.
        // Note that getValue() returns null if the entry is not present
        $old_value = self::getValue($name);
        if (isset($old_value)) {
            //DBG::log("self::add(): ERROR: Setting '$name' already exists and is non-empty ($old_value)");
            return false;
        }
        
        $filename=ASCMS_CORE_PATH .'/Setting/Data/'.self::$section.'.yml';
         
        $addValue[] =   Array(  'name'=> addslashes($name),
                                'section'=> addslashes(self::$section),
                                'group'=> addslashes($group),
                                'value'=> addslashes($value),
                                'type' => addslashes($type),
                                'values'=> addslashes($values),
                                'ord'=> intval($ord)
                            );
                              
        $objDataSet =new \Cx\Core_Modules\Listing\Model\Entity\DataSet($addValue);
        $objDataSet->exportToFile(new \Cx\Core_Modules\Listing\Model\Entity\Yaml(), $filename);
        
        return true;
    }


    /**
     * Delete one or more records from the database table
     *
     * For maintenance/update purposes only.
     * At least one of the parameter values must be non-empty.
     * It will fail if both are empty.  Mind that in this case,
     * no records will be deleted.
     * Does {@see flush()} the currently loaded settings on success.
     * @param   string    $name     The optional setting name.
     *                              Defaults to null
     * @param   string    $group      The optional group.
     *                              Defaults to null
     * @return  boolean             True on success, false otherwise
     */
    static function delete($name=null, $group=null)
    {
        $file = new \Cx\Lib\FileSystem\File(ASCMS_CORE_PATH .'/Setting/Data/'.self::$section.'.yml');
        $file->delete(); 
        self::flush();
        return true;
    }

    /**
     * Ensures that a valid template is available
     *
     * Die()s if the template given is invalid, and settingDb.html cannot be
     * loaded to replace it.
     * @param   \Cx\Core\Html\Sigma $objTemplateLocal   The template,
     *                                                  by reference
     */
    static function verify_template(&$objTemplateLocal)
    {
        // "instanceof" considers subclasses of Sigma to be a Sigma, too!
        if (!($objTemplateLocal instanceof \Cx\Core\Html\Sigma)) {
            $objTemplateLocal = new \Cx\Core\Html\Sigma(ASCMS_DOCUMENT_ROOT.'/core/Setting/View/Template/Generic');
        }
        
        if (!$objTemplateLocal->blockExists('core_settingdb_row')) {
            $objTemplateLocal->setRoot(ASCMS_DOCUMENT_ROOT.'/core/Setting/View/Template/Generic');
            //$objTemplateLocal->setCacheRoot('.');
            if (!$objTemplateLocal->loadTemplateFile('Form.html'))
                die("Failed to load template Form.html");
            //die(nl2br(contrexx_raw2xhtml(var_export($objTemplateLocal, true))));
        }
    }


    /**
     * Update and store all settings found in the $_POST array
     *
     * Note that you *MUST* call {@see init()} beforehand, or your settings
     * will be unknown and thus not be stored.
     * Sets up an error message on failure.
     * @return  boolean                 True on success, null on noop,
     *                                  or false on failure
     */
    static function storeFromPost()
    {
        global $_CORELANG;

        //echo("self::storeFromPost(): POST:<br />".nl2br(htmlentities(var_export($_POST, true)))."<hr />");
        //echo("self::storeFromPost(): FILES:<br />".nl2br(htmlentities(var_export($_FILES, true)))."<hr />");
        // There may be several tabs for different groups being edited, so
        // load the full set of settings for the module.
        // Note that this is why setting names should be unique.
        // TODO: You *MUST* call this yourself *before* in order to
        // properly initialize the section!
        //        self::init();
        unset($_POST['bsubmit']);
        $result = true;
        // Compare POST with current settings and only store what was changed.
        foreach (array_keys(self::$arrSettings) as $name) {
            $value = (isset ($_POST[$name])
                ? contrexx_input2raw($_POST[$name])
                : null);
            //            if (preg_match('/^'.preg_quote(CSRF::key(), '/').'$/', $name))
            //                continue;
            switch (self::$arrSettings[$name]['type']) {
              case \Cx\Core\Setting\Controller\Setting::TYPE_FILEUPLOAD:
                // An empty folder path has been posted, indicating that the
                // current file should be removed
                if (empty($value)) {
            //echo("Empty value, deleting file...<br />");
                    if (self::$arrSettings[$name]['value']) {
                        if (\File::delete_file(self::$arrSettings[$name]['value'])) {
            //echo("File deleted<br />");
                            $value = '';
                        } else {
            //echo("Failed to delete file<br />");
                            \Message::error(\File::getErrorString());
                            $result = false;
                        }
                    }
                } else {
                    // No file uploaded.  Skip.
                    if (empty($_FILES[$name]['name'])) continue;
                    // $value is the target folder path
                    $target_path = $value.'/'.$_FILES[$name]['name'];
            // TODO: Test if this works in all browsers:
                    // The path input field name is the same as the
                    // file upload input field name!
                    $result_upload = \File::upload_file_http(
                        $name, $target_path,
                        \Filetype::MAXIMUM_UPLOAD_FILE_SIZE,
                        // The allowed file types
                        self::$arrSettings[$name]['values']
                    );
                    // If no file has been uploaded at all, ignore the no-change
                    // TODO: Noop is not implemented in File::upload_file_http()
                    //                    if ($result_upload === '') continue;
                    if ($result_upload === true) {
                        $value = $target_path;
                    } else {
                    //echo("self::storeFromPost(): Error uploading file for setting $name to $target_path<br />");
                    // TODO: Add error message
                        \Message::error(\File::getErrorString());
                        $result = false;
                    }
                }
                break;
              case \Cx\Core\Setting\Controller\Setting::TYPE_CHECKBOX:
                  break;
              case \Cx\Core\Setting\Controller\Setting::TYPE_CHECKBOXGROUP:
                $value = (is_array($value)
                    ? join(',', array_keys($value))
                    : $value);
                // 20120508
              case \Cx\Core\Setting\Controller\Setting::TYPE_RADIO:
                  break;
              default:
                // Regular value of any other type
                break;
            }
            self::set($name, $value);
        }
                //echo("self::storeFromPost(): So far, the result is ".($result ? 'okay' : 'no good')."<br />");
        $result_update = self::updateAll();
        if ($result_update === false) {
            \Message::error($_CORELANG['TXT_CORE_SETTINGDB_ERROR_STORING']);
        } elseif ($result_update === true) {
            \Message::ok($_CORELANG['TXT_CORE_SETTINGDB_STORED_SUCCESSFULLY']);
        }
        // If nothing bad happened above, return the result of updateAll(),
        // which may be true, false, or the empty string
        if ($result === true) {
            return $result_update;
        }
        // There has been an error anyway
        return false;
    }


    /**
     * Deletes all entries for the current section
     *
     * This is for testing purposes only.  Use with care!
     * The static $section determines the module affected.
     * @return    boolean               True on success, false otherwise
     */
    static function deleteModule()
    {
        global $objDatabase;

        if (empty(self::$section)) {
        // TODO: Error message
            return false;
        }
        $objResult = $objDatabase->Execute("
            DELETE FROM `".DBPREFIX."core_setting`
             WHERE `section`='".self::$section."'");
        if (!$objResult) return self::errorHandler();
        return true;
    }


    /**
     * Splits the string value at commas and returns an array of strings
     *
     * Commas escaped by a backslash (\) are ignored and replaced by a
     * single comma.
     * The values themselves may be composed of pairs of key and value,
     * separated by a colon.  Colons escaped by a backslash (\) are ignored
     * and replaced by a single colon.
     * Leading and trailing whitespace is removed from both keys and values.
     * Note that keys *MUST NOT* contain commas or colons!
     * @param   string    $strValues    The string to be split
     * @return  array                   The array of strings
     */
    static function splitValues($strValues)
    {
        /*
        Example:
        postfinance:Postfinance Card,postfinanceecom:Postfinance E-Commerce,mastercard:Mastercard,visa:Visa,americanexpress:American Express,paypal:Paypal,invoice:Invoice,voucher:Voucher
        */
        $arrValues = array();
        $match = array();
        foreach (
            preg_split(
                '/\s*(?<!\\\\),\s*/', $strValues,
                null, PREG_SPLIT_NO_EMPTY) as $value
        ) {
            $key = null;
            if (preg_match('/^(.+?)\s*(?<!\\\\):\s*(.+$)/', $value, $match)) {
                $key = $match[1];
                $value = $match[2];
        //DBG::log("Split $key and $value");
            }
            str_replace(array('\\,', '\\:'), array(',', ':'), $value);
            if (isset($key)) {
                $arrValues[$key] = $value;
            } else {
                $arrValues[] = $value;
            }
        //DBG::log("Split $key and $value");
        }
        //DBG::log("Array: ".var_export($arrValues, true));
        return $arrValues;
    }


    /**
     * Joins the strings in the array with commas into a single values string
     *
     * Commas within the strings are escaped by a backslash (\).
     * The array keys are prepended to the values, separated by a colon.
     * Colons within the strings are escaped by a backslash (\).
     * Note that keys *MUST NOT* contain either commas or colons!
     * @param   array     $arrValues    The array of strings
     * @return  string                  The concatenated values string
     * @todo    Untested!  May or may not work as described.
     */
    static function joinValues($arrValues)
    {
        $strValues = '';
        foreach ($arrValues as $key => $value) {
            $value = str_replace(
                array(',', ':'), array('\\,', '\\:'), $value);
            $strValues .=
                ($strValues ? ',' : '').
                "$key:$value";
        }
        return $strValues;
    }


    /**
     * Should be called whenever there's a problem with the settings
     *
     * Tries to fix or recreate the settings.
     * @return  boolean             False, always.
     * @static
     */
    static function errorHandler()
    {
        $file = new \Cx\Lib\FileSystem\File(ASCMS_CORE_PATH .'/Setting/Data/'.self::$section.'.yml');
        $file->touch();
        return false;
    }
    
    /**
     * Updates a setting
     *
     * If the setting name exists and the new value is not equal to
     * the old one, it is updated, and $changed set to true.
     * Otherwise, nothing happens, and false is returned
     * @see init(), updateAll()
     * @param   string    $name       The settings name
     * @param   string    $value      The settings value
     * @return  boolean               True if the value has been changed,
     *                                false otherwise, null on noop
     */
    static function set($name, $value)
    {
        if (!isset(self::$arrSettings[$name])) {
        //DBG::log("self::set($name, $value): Unknown, changed: ".self::$changed);
            return false;
        }
        if (self::$arrSettings[$name]['value'] == $value) {
        //DBG::log("self::set($name, $value): Identical, changed: ".self::$changed);
            return null;
        }
        self::$changed = true;
        self::$arrSettings[$name]['value'] = $value;
        //DBG::log("self::set($name, $value): Added/updated, changed: ".self::$changed);
        return true;
    }
    
    /**
     * Stores all settings entries present in the $arrSettings object
     * array variable
     *
     * Returns boolean true if all records were stored successfully,
     * null if nothing changed (noop), false otherwise.
     * Upon success, also resets the $changed class variable to false.
     * The class *MUST* have been initialized before calling this
     * method using {@see init()}, and the new values been {@see set()}.
     * Note that this method does not work for adding new settings.
     * See {@see add()} on how to do this.
     * @return  boolean                   True on success, null on noop,
     *                                    false otherwise
     */
    static function updateAll()
    {
        //        global $_CORELANG;

        if (!self::$changed) {
        // TODO: These messages are inapropriate when settings are stored by another piece of code, too.
        // Find a way around this.
        //            Message::information($_CORELANG['TXT_CORE_SETTINGDB_INFORMATION_NO_CHANGE']);
            return null;
        }
        $success = true;
        
        foreach (self::$arrSettings as $name => $arrSetting) {
            
            self::set($name, self::$arrSettings[$name]['value']);
        }
         $success &= self::updateFileData();
       // $success &= self::update(self::$arrSettings);
        if ($success) {
            self::$changed = false;
        //            return Message::ok($_CORELANG['TXT_CORE_SETTINGDB_STORED_SUCCESSFULLY']);
            return true;
        }
        //        return Message::error($_CORELANG['TXT_CORE_SETTINGDB_ERROR_STORING']);
        return false;
    }
    
    /**
     * Updates the value for the given name in the settings table
     *
     * The class *MUST* have been initialized before calling this
     * method using {@see init()}, and the new value been {@see set()}.
     * Sets $changed to true and returns true if the value has been
     * updated successfully.
     * Note that this method does not work for adding new settings.
     * See {@see add()} on how to do this.
     * Also note that the loaded setting is not updated, only the database!
     * @param   string    $name   The settings name
     * @return  boolean           True on successful update or if
     *                            unchanged, false on failure
     * @static
     * @global  mixed     $objDatabase    Database connection object
     */
    static function update($name)
    {
        
        // TODO: Add error messages for individual errors
        if (empty(self::$section)) {
            \DBG::log("self::update(): ERROR: Empty section!");
            return false;
        }
        // Fail if the name is invalid
        // or the setting does not exist
        if (empty($name)) {
            \DBG::log("self::update(): ERROR: Empty name!");
            return false;
        }
        if (!isset(self::$arrSettings[$name])) {
            \DBG::log("self::update(): ERROR: Unknown setting name '$name'!");
            return false;
        }
        self::set($name, self::$arrSettings[$name]['value']);
      
      self::updateFileData();  
    }
    
    /**
     * Updates the value for the given name in the settings file
     *
     * Sets $changed to true and returns true if the value has been
     * updated successfully.
     * Note that this method does not work for adding new settings.
     */
    static private function updateFileData()
    {
        if(!empty(self::$arrSettings))
        {
            $filename=ASCMS_CORE_PATH .'/Setting/Data/'.self::$section.'.yml';
         
            $objFile = new \Cx\Lib\FileSystem\File($filename);
            $objFile->delete(); 
            
            foreach(self::$arrSettings as $value)
            {
                $objDataSet =new \Cx\Core_Modules\Listing\Model\Entity\DataSet(array($value));
                $objDataSet->exportToFile(new \Cx\Core_Modules\Listing\Model\Entity\Yaml(), $filename);
            }
            
            return true;
        }else{
            return false;
        }
    }

}
