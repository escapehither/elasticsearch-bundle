<?php
/**
 * This file is part of the search bundle manager package.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp;

use Pagerfanta\Adapter\AdapterInterface;

/**
 * Class EasyElasticSearchAdapter
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class EasyElasticSearchAdapter implements AdapterInterface
{
    /**
     * @var
     */
    private $request;

    /**
     * @var
     */
    private $index;

    /**
     * @var
     */
    private $resultSet;

    /**
     * @var
     */
    private $searchable;

    /**
     * Es adapter constructor.
     *
     * @param SearchReQuestInterface $request The request.
     * @param Index                  $index   The index to search upon.
     */
    public function __construct(SearchReQuestInterface $request, Index $index)
    {
        $this->request = $request;
        $this->index = $index;
    }

    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     */
    public function getNbResults()
    {
        if (!$this->resultSet) {
            $this->resultSet = $this->search();

            return $this->resultSet["hits"]['total'];
        }

        return $this->resultSet["hits"]['total'];
    }

    /**
     * Returns the search ResultSet. Will return null if getSlice has not yet been
     * called.
     *
     * @return mixed
     */
    public function getResultSet()
    {
        return $this->resultSet;
    }

    /**
     * Returns an slice of the results.
     *
     * @param integer $offset The offset.
     * @param integer $length The length.
     *
     * @return array|\Traversable The slice.
     */
    public function getSlice($offset, $length)
    {
        $this->request->setFrom($offset);
        $this->request->setSize($length);

        return $this->resultSet = $this->search();
    }
    /**
     * Search resources.
     *
     * @return void
     */
    protected function search()
    {
        return $this->index->search($this->request);
    }
}
