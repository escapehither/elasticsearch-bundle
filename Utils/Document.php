<?php
/**
 * This file is part of the search-manager-bundle package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 19/10/17
 * Time: 19:50
 */

namespace EscapeHither\SearchManagerBundle\Utils;

/**
 *
 * Class Document
 * @package EscapeHither\SearchManagerBundle\Utils
 */
class Document
{
    protected $type;
    protected $field;
    protected $mapping;
    protected $id;

    /**
     * @param $type
     * @param $field
     * @param null $id
     * @param array $mapping
     */
    public function __construct($type, $field, $id = NULL, $mapping=[])
    {
        $this->type = $type;
        $this->field = $field;
        $this->mapping = $mapping;
        $this->id= $id;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return mixed
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @param mixed $mapping
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


}