<?php
/**
 * Pushover Documents.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2016 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 */

namespace Pushover;

use Pimcore\API\Plugin as PluginLib;
use Pimcore\Db;
use Pimcore\Model\Document;
use Pimcore\Version;

/**
 * Class Plugin
 * @package Pushover
 */
class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface
{
    /**
     * @var \Zend_Translate
     */
    protected static $_translate;

    /**
     *
     */
    public function init()
    {
        parent::init();

        Document::addDocumentType("pushover");
    }

    /**
     * @return string
     */
    public static function install()
    {
        if(Version::getRevision() >= 3993) {
            $db = Db::get();

            $db->query("CREATE TABLE `plugin_pushover_documents` (
          `id` int(11) unsigned NOT NULL DEFAULT '0',
          `recipient` varchar(255) DEFAULT NULL,
          `title` varchar(255) DEFAULT NULL,
          `message` text NOT NULL DEFAULT '',
          `sound` varchar(255) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) DEFAULT CHARSET=utf8;");

            $db->query("
            CREATE TABLE `plugin_pushover_log` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `documentId` int(11) DEFAULT NULL,
              `requestUri` varchar(500) DEFAULT NULL,
              `params` text,
              `recipient` longtext,
              `sound` varchar(255) DEFAULT NULL,
              `sentDate` bigint(20) DEFAULT NULL,
              `title` varchar(500) DEFAULT NULL,
              `message` text DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) DEFAULT CHARSET=utf8;");

            $db->query("
          ALTER TABLE `documents` CHANGE `type` `type` enum('page','link','snippet','folder','hardlink','email','newsletter','printpage','printcontainer', 'pushover');
        ");

            return self::getTranslate()->_('pushover_installed');
        }

        return self::getTranslate()->_('pushover_pimcore_requirement');
    }

    /**
     * @return bool
     */
    public static function uninstall()
    {
        $db = Db::get();

        $db->query("DROP TABLE `plugin_pushover_documents`;");
        $db->query("DROP TABLE `plugin_pushover_log`;");

        return self::getTranslate()->_('pushover_uninstalled');
    }

    /**
     * indicates wether this plugins is currently installed
     * @return boolean
     */
    public static function isInstalled() {
        if(Version::getRevision() >= 3993) {

            $result = null;

            try {
                $result = Db::get()->describeTable("plugin_pushover_documents");
                $result = Db::get()->describeTable("plugin_pushover_log");
            } catch (\Exception $e) {

            }

            return !empty($result);
        }

        return false;
    }

    /**
     * get translation directory.
     *
     * @return string
     */
    public static function getTranslationFileDirectory()
    {
        return PIMCORE_PLUGINS_PATH.'/Pushover/static/texts';
    }

    /**
     * get translation file.
     *
     * @param string $language
     *
     * @return string path to the translation file relative to plugin directory
     */
    public static function getTranslationFile($language)
    {
        if (is_file(self::getTranslationFileDirectory()."/$language.csv")) {
            return "/Pushover/static/texts/$language.csv";
        } else {
            return '/Pushover/static/texts/en.csv';
        }
    }

    /**
     * get translate.
     *
     * @param $lang
     *
     * @return \Zend_Translate
     */
    public static function getTranslate($lang = null)
    {
        if (self::$_translate instanceof \Zend_Translate) {
            return self::$_translate;
        }
        if (is_null($lang)) {
            try {
                $lang = \Zend_Registry::get('Zend_Locale')->getLanguage();
            } catch (\Exception $e) {
                $lang = 'en';
            }
        }

        self::$_translate = new \Zend_Translate(
            'csv',
            PIMCORE_PLUGINS_PATH.self::getTranslationFile($lang),
            $lang,
            array('delimiter' => ',')
        );

        return self::$_translate;
    }
}
