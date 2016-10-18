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

namespace Pimcore\Model\Document;

use Pimcore\Model;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Pushover\Message;

/**
 * Class Pushover
 * @package Pimcore\Model\Document
 */
class Pushover extends Model\Document
{
    /**
     * Static type of the document
     *
     * @var string
     */
    public $type = "pushover";

    /**
     * Contains the title
     *
     * @var string
     */
    public $title = "";

    /**
     * Contains the message
     *
     * @var string
     */
    public $message = "";

    /**
     * Contains the recipient
     *
     * @var string
     */
    public $recipient = "";

    /**
     * Contains the sound
     *
     * @var string
     */
    public $sound = "";

    /**
     * Send Message
     *
     * @param $params
     */
    public function send($params) {
        $message = new Message();
        $message->setDocument($this);
        $message->setParams($params);
        $message->send();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param string $recipient
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * @return string
     */
    public function getSound()
    {
        return $this->sound;
    }

    /**
     * @param string $sound
     */
    public function setSound($sound)
    {
        $this->sound = $sound;
    }


}
