<?php

/**
 * Framework Validator
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.1
 * @package     contrexx
 * @subpackage  lib_framework
 * @todo        Edit PHP DocBlocks!
 */

/*
Another proposal for the e-mail regex from in here:

'/\s([_a-zA-Z0-9-]+(?:\.?[_a-zA-Z0-9-])*
@((?:[a-zA-Z0-9-]+\.)+(?:[a-zA-Z]{2,4})|localhost))\s/';

TODO: Find the up to date RFC and do it right.
*/
define('VALIDATOR_REGEX_EMAIL',
          '[a-z0-9]+([-._][a-z0-9]+)*'.       // user
          '@(?:'.
              '([a-z0-9]+([-.][a-z0-9]+)*)+'. //    domain
              '\.[a-z]{2,4}'.                 //    sld, tld
            '|'.
              'localhost'.                    // or localhost
          ')'
);

/**
 * Framework Validator
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.1
 * @package     contrexx
 * @subpackage  lib_framework
 * @todo        Edit PHP DocBlocks!
 */
class FWValidator
{
    /**
     * Validate an E-mail address
     *
     * @param  string $string
     * @return boolean
     * @access public
     */
    function isEmail($string)
    {
        return preg_match(
            '/^'.VALIDATOR_REGEX_EMAIL.'$/i',
            $string) ? true : false;
    }

    /**
     * Find all e-mail addresses in a string
     * @param   string  $string     String potentially containing email addresses
     * @return  array               Array with all e-mail addresses found
     * @access  public
     */
    function getEmailAsArray($string)
    {
        preg_match_all(
//          '/\s([_a-zA-Z0-9-]+(?:\.?[_a-zA-Z0-9-])*@((?:[a-zA-Z0-9-]+\.)+(?:[a-zA-Z]{2,4})|localhost))\s+/", $string, $matches);
            '/\s('.VALIDATOR_REGEX_EMAIL.')\.?\s/',
            $string, $matches);
        return $matches[0]; // include spaces
        // return $matches[1]; // exclude spaces
    }

    /**
     * Check if the given url has the leading HTTP protocol prefix.
     * If not then the prefix will be added.
     *
     * @access public
     * @param string url
     * @return string url
     */
    function getUrl($string)
    {
        if (preg_match("/^[a-z]+:\/\//i", $string) || empty($string)) {
            return $string;
        } else {
            return "http://".$string;
        }
    }
}

?>
