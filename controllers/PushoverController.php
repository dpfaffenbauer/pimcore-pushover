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

use Pimcore\Model\Element;
use Pimcore\Model\Document;
use Pimcore\Logger;

/**
 * Class Pushover_PushoverController
 */
class Pushover_PushoverController extends \Pimcore\Controller\Action\Admin\Document
{
    public function getDataByIdAction()
    {

        // check for lock
        if (Element\Editlock::isLocked($this->getParam("id"), "document")) {
            $this->_helper->json([
                "editlock" => Element\Editlock::getByElement($this->getParam("id"), "document")
            ]);
        }
        Element\Editlock::lock($this->getParam("id"), "document");

        $pushover = Document\Pushover::getById($this->getParam("id"));
        $pushover = clone $pushover;

        $pushover->idPath = Element\Service::getIdPath($pushover);
        $pushover->userPermissions = $pushover->getUserPermissions();
        $pushover->setLocked($pushover->isLocked());
        $pushover->setParent(null);

        $this->addTranslationsData($pushover);
        $this->minimizeProperties($pushover);

        //Hook for modifying return value - e.g. for changing permissions based on object data
        //data need to wrapped into a container in order to pass parameter to event listeners by reference so that they can change the values
        $returnValueContainer = new \Pimcore\Model\Tool\Admin\EventDataContainer(object2array($pushover));
        \Pimcore::getEventManager()->trigger("admin.document.get.preSendData", $this, [
            "document" => $pushover,
            "returnValueContainer" => $returnValueContainer
        ]);

        if ($pushover->isAllowed("view")) {
            $this->_helper->json($returnValueContainer->getData());
        }

        $this->_helper->json(false);
    }

    public function saveAction()
    {
        try {
            if ($this->getParam("id")) {
                $pushover = Document\Pushover::getById($this->getParam("id"));
                $this->setValuesToDocument($pushover);

                $pushover->setModificationDate(time());
                $pushover->setUserModification($this->getUser()->getId());

                if ($this->getParam("task") == "unpublish") {
                    $pushover->setPublished(false);
                }
                if ($this->getParam("task") == "publish") {
                    $pushover->setPublished(true);
                }

                // only save when publish or unpublish
                if (($this->getParam("task") == "publish" && $pushover->isAllowed("publish")) || ($this->getParam("task") == "unpublish" && $pushover->isAllowed("unpublish"))) {
                    $pushover->save();

                    $this->_helper->json(["success" => true]);
                }
            }
        } catch (\Exception $e) {
            Logger::log($e);
            if (\Pimcore\Tool\Admin::isExtJS6() && $e instanceof Element\ValidationException) {
                $this->_helper->json(["success" => false, "type" => "ValidationException", "message" => $e->getMessage(), "stack" => $e->getTraceAsString(), "code" => $e->getCode()]);
            }
            throw $e;
        }

        $this->_helper->json(false);
    }

    /**
     * @param Document\Link $pushover
     */
    protected function setValuesToDocument(Document\Pushover $pushover)
    {
        // data
        if ($this->getParam("data")) {
            $data = \Zend_Json::decode($this->getParam("data"));

            $pushover->setValues($data);
        }

        $this->addPropertiesToDocument($pushover);
    }
}
