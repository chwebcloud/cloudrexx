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
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG 
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @access      public 
 * @package     cloudrexx
 * @subpackage  coremodule_cache
 */ 
global $_ARRAYLANG; 
$_ARRAYLANG['TXT_CACHE_ERR_NOTWRITABLE'] = 'Le dossier choisi pour le cache est protégé en écriture. Veuillez affecter le privilège chmod 777 au dossier suivant:';
$_ARRAYLANG['TXT_CACHE_ERR_NOTEXIST'] = 'Le dossier suivant, prévu pour le cache, n"existe pas:';
$_ARRAYLANG['TXT_SETTINGS_MENU_CACHE'] = 'Cache';
$_ARRAYLANG['TXT_CACHE_STATS'] = 'Statistiques';
$_ARRAYLANG['TXT_CACHE_CONTREXX_CACHING'] = 'Cloudrexx caching';
$_ARRAYLANG['TXT_CACHE_USERCACHE'] = 'Database cache engine';
$_ARRAYLANG['TXT_CACHE_OPCACHE'] = 'Program code cache engine';
$_ARRAYLANG['TXT_CACHE_PROXYCACHE'] = 'Proxy cache';
$_ARRAYLANG['TXT_CACHE_EMPTY'] = 'Vider le cache';
$_ARRAYLANG['TXT_CACHE_APC'] = 'APC';
$_ARRAYLANG['TXT_CACHE_ZEND_OPCACHE'] = 'Zend OPCache';
$_ARRAYLANG['TXT_CACHE_XCACHE'] = 'xCache';
$_ARRAYLANG['TXT_CACHE_MEMCACHE'] = 'Memcache';
$_ARRAYLANG['TXT_CACHE_MEMCACHED'] = 'Memcached';
$_ARRAYLANG['TXT_CACHE_FILESYSTEM'] = 'FileSystem';
$_ARRAYLANG['TXT_CACHE_APC_ACTIVE_INFO'] = 'APC is active, as soon as the php directive "apc.enabled" has been set to "On".';
$_ARRAYLANG['TXT_CACHE_APC_CONFIG_INFO'] = 'If you want to use apc as a database cache engine, you have to set the php directive "apc.serializer" to "php".';
$_ARRAYLANG['TXT_CACHE_ZEND_OPCACHE_ACTIVE_INFO'] = 'Zend OPCache is active, as soon as the php directive "opcache.enable" has been set to "On".';
$_ARRAYLANG['TXT_CACHE_ZEND_OPCACHE_CONFIG_INFO'] = 'If you want to use the zend opcache as cache engine, the php directives "opcache.save_comments" and "opcache.load_comments" has to be set to "On".';
$_ARRAYLANG['TXT_CACHE_XCACHE_ACTIVE_INFO'] = 'xCache is active, as soon as the php directive "xcache.cacher" has been set to "On".';
$_ARRAYLANG['TXT_CACHE_XCACHE_CONFIG_INFO'] = 'If you want to use xCache as a database cache engine, you have to set the php directive "xcache.var_size" to a value bigger than 0. For the program code cache the php directive "xcache.size" has to be bigger than 0.';
$_ARRAYLANG['TXT_CACHE_MEMCACHE_ACTIVE_INFO'] = 'Memcache is active, as soon as the Memcache server is running and the configuration is correct.';
$_ARRAYLANG['TXT_CACHE_MEMCACHE_CONFIG_INFO'] = 'If you want to use Memcache, the configuration (IP address and port number) has to be correct.';
$_ARRAYLANG['TXT_CACHE_MEMCACHED_ACTIVE_INFO'] = 'Memcached is active, as soon as the Memcached server is running and the configuration is correct.';
$_ARRAYLANG['TXT_CACHE_MEMCACHED_CONFIG_INFO'] = 'If you want to use Memcached, the configuration (IP address and port number) has to be correct.';
$_ARRAYLANG['TXT_CACHE_ENGINE'] = 'Engine';
$_ARRAYLANG['TXT_CACHE_INSTALLATION_STATE'] = 'Installed';
$_ARRAYLANG['TXT_CACHE_ACTIVE_STATE'] = 'Active';
$_ARRAYLANG['TXT_CACHE_CONFIGURATION_STATE'] = 'Configured';
$_ARRAYLANG['TXT_SAVE'] = 'Enregistrer';
$_ARRAYLANG['TXT_ACTIVATED'] = 'Activé';
$_ARRAYLANG['TXT_DEACTIVATED'] = 'Déactivé';
$_ARRAYLANG['TXT_CACHE_SETTINGS_STATUS'] = 'Cache';
$_ARRAYLANG['TXT_CACHE_SETTINGS_STATUS_HELP'] = 'Statut du cache: (on | off)';
$_ARRAYLANG['TXT_CACHE_SETTINGS_EXPIRATION'] = 'Expiration';
$_ARRAYLANG['TXT_CACHE_SETTINGS_EXPIRATION_HELP'] = 'Les pages cachées expirent après cette durée (en secondes) et seront dès lors renouvellées.';
$_ARRAYLANG['TXT_CACHE_EMPTY_DESC'] = 'Cliquer sur ce bouton pour vider le cache des pages visitées. Il se reconstituera au fur et à mesure des prochaines pages visitées.';
$_ARRAYLANG['TXT_CACHE_EMPTY_DESC_FILES_AND_ENRIES'] = 'With a click on the button, you can remove the current cache content. The cached files and entries will be recreated while viewing the pages.';
$_ARRAYLANG['TXT_CACHE_EMPTY_DESC_FILES'] = 'With a click on the button, you can remove the current cache content. The cached files will be recreated while viewing the pages.';
$_ARRAYLANG['TXT_CACHE_EMPTY_DESC_MEMCACHE'] = 'With a click on the button, you can mark the current cache content as outdated. The cached entries will be updated while viewing the pages.';
$_ARRAYLANG['TXT_CACHE_STATS_FILES'] = 'Pages mises en cache';
$_ARRAYLANG['TXT_CACHE_STATS_FOLDERSIZE'] = 'Taille du dossier';
$_ARRAYLANG['TXT_STATS_CHACHE_SITE_COUNT'] = 'Cached Files';
$_ARRAYLANG['TXT_STATS_CHACHE_ENTRIES_COUNT'] = 'Cached Databaseentries';
$_ARRAYLANG['TXT_STATS_CACHE_SIZE'] = 'Ammount of stored Data';
$_ARRAYLANG['TXT_DISPLAY_CONFIGURATION'] = 'Konfiguration einblenden';
$_ARRAYLANG['TXT_HIDE_CONFIGURATION'] = 'Konfiguration ausblenden';
$_ARRAYLANG['TXT_CACHE_VARNISH'] = 'Varnish';
$_ARRAYLANG['TXT_CACHE_PROXY_IP'] = 'Proxy IP-Address';
$_ARRAYLANG['TXT_CACHE_PROXY_PORT'] = 'Proxy Port';
$_ARRAYLANG['TXT_SETTINGS_UPDATED'] = 'Paramètres mis à jour.';
$_ARRAYLANG['TXT_CACHE_FOLDER_EMPTY'] = 'Cache vidé.';
$_ARRAYLANG['TXT_CACHE_EMPTY_SUCCESS'] = 'Cache has been emptied.';