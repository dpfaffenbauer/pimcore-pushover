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

use Capirussa\Pushover\Client;
use Pimcore\Model\Document\Pushover;
use Pimcore\Placeholder;
use Pushover\Model\Configuration;

/**
 * Class Message
 * @package Pushover
 */
class Message extends \Capirussa\Pushover\Message  {

    /**
     * @var object Pimcore_Placeholder
     */
    protected $placeholderObject;

    /**
     * Contains data that has to be stored temporary e.g. email receivers for logging
     *
     * @var array
     */
    protected $temporaryStorage = [];

    /**
     * If true - emails are logged in the database and on the file-system
     *
     * @var bool
     */
    protected $loggingEnable = true;

    /**
     * Contains the email document
     *
     * @var Pushover
     */
    protected $document;

    /**
     * Contains the dynamic Params for the Placeholders
     *
     * @var array
     */
    protected $params = [];

    /**
     * if true - the layout is enabled when document is rendered to a string
     * @var bool
     */
    protected $enableLayoutOnPlaceholderRendering = true;

    /**
     * @var string
     */
    protected $applicationToken;

    /**
     * @param null $options
     */
    public function __construct($options = null)
    {
        // using $charset as param to be compatible with \Zend_Mail
        if (is_array($options)) {
            parent::__construct($options["charset"] ? $options["charset"] : "UTF-8");

            if ($options["document"]) {
                $this->setDocument($options["document"]);
            }
            if ($options['params']) {
                $this->setParams($options['params']);
            }
            if ($options['message']) {
                $this->setMessage($options['message']);
            }
            if ($options['title']) {
                $this->setTitle($options['title']);
            }
        }

        $this->init();
    }

    /**
     * Initializes the placeholder and settings
     *
     * @return void
     */
    public function init()
    {
        $this->placeholderObject = new Placeholder();

        $applicationToken = Configuration::get("APPLICATION.TOKEN");

        if($applicationToken) {
            $this->setApplicationToken($applicationToken);
        }
    }

    /**
     * Send Message
     *
     * @return null|string
     *
     * @throws \Exception
     */
    public function send()
    {
        if(!$this->document instanceof Pushover) {
            throw new \Exception("Document needs to be instance of \\Pimcore\\Model\\Document\\Pushover");
        }

        $this->setTitle($this->getTitleRendered());
        $this->setMessage($this->getMessageRendered());
        $this->setRecipient($this->document->getRecipient());
        $this->setSound($this->document->getSound());

        if(!$this->getApplicationToken()) {
            throw new \Exception("Application Token is invalid");
        }

        $client = Client::init($this->getApplicationToken());

        return $client->send($this);
    }

    /**
     * Replaces the placeholders with the content and returns the rendered Subject
     *
     * @return string
     */
    public function getTitleRendered()
    {
        $title = $this->title;

        if (!$title && $this->getDocument()) {
            $title = $this->getDocument()->getTitle();
        }

        return $this->placeholderObject->replacePlaceholders($title, $this->getParams(), $this->getDocument(), $this->getEnableLayoutOnPlaceholderRendering());
    }

    /**
     * Replaces the placeholders with the content and returns the rendered Subject
     *
     * @return string
     */
    public function getMessageRendered()
    {
        $message = $this->message;

        if (!$message && $this->getDocument()) {
            $message = $this->getDocument()->getMessage();
        }

        return $this->placeholderObject->replacePlaceholders($message, $this->getParams(), $this->getDocument(), $this->getEnableLayoutOnPlaceholderRendering());
    }


    /**
     * @return object
     */
    public function getPlaceholderObject()
    {
        return $this->placeholderObject;
    }

    /**
     * @param object $placeholderObject
     */
    public function setPlaceholderObject($placeholderObject)
    {
        $this->placeholderObject = $placeholderObject;
    }

    /**
     * @return array
     */
    public function getTemporaryStorage()
    {
        return $this->temporaryStorage;
    }

    /**
     * @param array $temporaryStorage
     */
    public function setTemporaryStorage($temporaryStorage)
    {
        $this->temporaryStorage = $temporaryStorage;
    }

    /**
     * @return boolean
     */
    public function isLoggingEnable()
    {
        return $this->loggingEnable;
    }

    /**
     * @param boolean $loggingEnable
     */
    public function setLoggingEnable($loggingEnable)
    {
        $this->loggingEnable = $loggingEnable;
    }

    /**
     * @return Pushover
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param Pushover $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setEnableLayoutOnPlaceholderRendering($value)
    {
        $this->enableLayoutOnPlaceholderRendering = (bool)$value;

        return $this;
    }

    /**
     * @return bool
     */
    public function getEnableLayoutOnPlaceholderRendering()
    {
        return $this->enableLayoutOnPlaceholderRendering;
    }

    /**
     * @return string
     */
    public function getApplicationToken()
    {
        return $this->applicationToken;
    }

    /**
     * @param string $applicationToken
     */
    public function setApplicationToken($applicationToken)
    {
        $this->applicationToken = $applicationToken;
    }
}