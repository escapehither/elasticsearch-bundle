<?php
/**
 * This file is part of the search bundle manager package.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp;

/**
 * Class Document
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class Document
{
    protected $type;
    protected $field;
    protected $mapping;
    protected $id;

    /**
     * The Es Document.
     *
     * @param string $type    The type of this document.
     * @param array  $field   The field of this document.
     * @param null   $id      The id of this document.
     * @param array  $mapping The mapping of this document.
     */
    public function __construct($type, $field, $id = null, $mapping = [])
    {
        $this->type = $type;
        $this->field = $field;
        $this->mapping = $mapping;
        $this->id = $id;
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
