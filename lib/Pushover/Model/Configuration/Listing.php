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

namespace Pushover\Model\Configuration;

use Pushover\Model\Configuration;
use Pimcore\Model;

/**
 * Class Listing
 * @package Pushover\Model\Configuration
 */
class Listing extends Model\Listing\JsonListing
{
    /**
     * Contains the results of the list. They are all an instance of Configuration.
     *
     * @var array
     */
    public $configurations = null;

    /**
     * Get Configurations.
     *
     * @return Configuration[]
     */
    public function getConfigurations()
    {
        if (is_null($this->configurations)) {
            $this->load();
        }

        return $this->configurations;
    }

    /**
     * Set Configuration.
     *
     * @param array $configurations
     */
    public function setConfigurations($configurations)
    {
        $this->configurations = $configurations;
    }
}
