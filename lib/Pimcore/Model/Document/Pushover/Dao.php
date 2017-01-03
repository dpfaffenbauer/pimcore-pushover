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

namespace Pimcore\Model\Document\Pushover;

use Pimcore\Model;
use Pimcore\Tool\Serialize;

/**
 * Class Dao
 * @package Pimcore\Model\Document\Pushover
 */
class Dao extends Model\Document\Dao
{
    /**
     * Get the data for the object by the given id, or by the id which is set in the object
     *
     * @param integer $id
     * @return void
     *
     * @throws \Exception
     */
    public function getById($id = null)
    {
        try {
            if ($id != null) {
                $this->model->setId($id);
            }

            $data = $this->db->fetchRow("SELECT documents.*, plugin_pushover_documents.*, tree_locks.locked FROM documents
                LEFT JOIN plugin_pushover_documents ON documents.id = plugin_pushover_documents.id
                LEFT JOIN tree_locks ON documents.id = tree_locks.id AND tree_locks.type = 'document'
                    WHERE documents.id = ?", $this->model->getId());

            if ($data["id"] > 0) {
                $this->assignVariablesToModel($data);
            } else {
                throw new \Exception("Pushover Document with the ID " . $this->model->getId() . " doesn't exists");
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create a new record for the object in the database
     *
     * @return void
     *
     * @throws \Exception
     */
    public function create()
    {
        try {
            parent::create();

            $this->db->insert("plugin_pushover_documents", [
                "id" => $this->model->getId()
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @throws \Exception
     */
    public function update()
    {
        parent::update();

        try {
            $typeSpecificTable = "plugin_pushover_documents";
            $validColumnsTypeSpecific = $this->getValidTableColumns($typeSpecificTable);

            $document = get_object_vars($this->model);

            $dataTypeSpecific = [];

            foreach ($document as $key => $value) {

                // check if the getter exists
                $getter = "get" . ucfirst($key);
                if (!method_exists($this->model, $getter)) {
                    continue;
                }

                // get the value from the getter
                if (in_array($key, $this->getValidTableColumns("documents")) || in_array($key, $validColumnsTypeSpecific)) {
                    $value = $this->model->$getter();
                } else {
                    continue;
                }

                if (is_bool($value)) {
                    $value = (int)$value;
                }
                if (is_array($value)) {
                    $value = Serialize::serialize($value);
                }

                if (in_array($key, $this->getValidTableColumns("documents"))) {
                    $dataDocument[$key] = $value;
                }
                if (in_array($key, $validColumnsTypeSpecific)) {
                    $dataTypeSpecific[$key] = $value;
                }
            }

            $this->db->insertOrUpdate($typeSpecificTable, $dataTypeSpecific);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Deletes the object (and data) from database
     *
     * @return void
     *
     * @throws \Exception
     */
    public function delete()
    {
        try {
            $this->deleteAllProperties();

            $this->db->delete("plugin_pushover_documents", $this->db->quoteInto("id = ?", $this->model->getId()));
            //deleting log files
            $this->db->delete("plugin_pushover_log", $this->db->quoteInto("documentId = ?", $this->model->getId()));

            parent::delete();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
