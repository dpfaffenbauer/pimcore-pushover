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
 * @copyright  Copyright (c) 2015-2017 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 (GPLv3)
 */

/**
 * Class Pushover_SettingsController
 */
class Pushover_SettingsController extends \Pimcore\Controller\Action\Admin\Document
{
    public function getAction()
    {
        $valueArray = [];

        $config = new \Pushover\Model\Configuration\Listing();

        foreach ($config->getConfigurations() as $c) {
            $valueArray[$c->getKey()] = $c->getData();
        }

        $response = array(
            'settings' => $valueArray
        );

        $this->_helper->json($response);
        $this->_helper->json(false);
    }

    public function setAction()
    {
        $values = \Zend_Json::decode($this->getParam('settings'));
        $values = array_htmlspecialchars($values);

        foreach ($values as $key => $value) {
            \Pushover\Model\Configuration::set($key, $value);
        }

        $this->_helper->json(array('success' => true));
    }
}
