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
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @access      public
 * @package     cloudrexx
 * @subpackage  core_routing
 */
global $_ARRAYLANG;

// Act Variables
$_ARRAYLANG['TXT_CORE_ROUTING'] = 'Routing';
$_ARRAYLANG['TXT_CORE_ROUTING_ACT_DEFAULT'] = 'Übersicht';
$_ARRAYLANG['TXT_CORE_ROUTING_ACT_REDIRECT'] = 'Weiterleitungen';

$_ARRAYLANG['TXT_CORE_ROUTING_ACT_REWRITERULE'] = 'Weiterleitungsregeln';
$_ARRAYLANG['id'] = 'ID';
$_ARRAYLANG['regularExpression'] = 'Regulärer Ausdruck';
$_ARRAYLANG['orderNo'] = 'Sortiernummer';
$_ARRAYLANG['rewriteStatusCode'] = 'HTTP Statuscode der Weiterleitung';
$_ARRAYLANG['continueOnMatch'] = 'Fortfahren nach Treffer';

$_ARRAYLANG['TXT_CORE_ROUTING_TITLE'] = 'Weiterleitungsregeln';
$_ARRAYLANG['TXT_CORE_ROUTING_INTRODUCTION'] = 'Diese Funktion erlaubt es, Anfragen auf die Webseite global umzuleiten. So ist es beispielsweise möglich, Anfragen auf eine andere Domain zu einer Unterseite umzuleiten. Nachfolgend werden die einzelnen Einstellungsmöglichkeiten erklärt:';
$_ARRAYLANG['TXT_CORE_ROUTING_EXPLANATION'] = 'Die Regeln bestehen aus einem <a href="http://de.wikipedia.org/wiki/Regulärer_Ausdruck">Regulären Ausdruck</a>. Dieser wird für ein simples Suchen/Ersetzen auf die Anfrage-URL verwendet. Die einzelnen Regeln werden, sortiert nach der Sortiernummer, der Reihe nach abgearbeitet. Trifft eine der Regeln zu und "Fortfahren nach Treffer" ist auf "Nein" gesetzt, so werden keine weiteren Regeln abgearbeitet. Sind alle Regeln abgearbeitet und mindestens eine traf zu, so wird die Weiterleitung mit dem HTTP Statuscode der letzten Regel die zutraf vorgenommen.';
