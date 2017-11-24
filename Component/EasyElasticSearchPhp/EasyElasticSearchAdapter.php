<?php



namespace EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Class EasyElasticSearchAdapter
 * @package EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp
 */
class  EasyElasticSearchAdapter implements AdapterInterface
{
    /**
     * @var
     */
    private $query;

    /**
     * @var
     */
    private $resultSet;

    /**
     * @var
     */
    private $searchable;

    public function __construct(array $query)
    {
        $this->query = $query;
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
     * Returns the Elastica ResultSet. Will return null if getSlice has not yet been
     * called.
     *
     * @return
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
        $this->query['from'] = $offset;
        $this->query['size'] = $length;
        return $this->resultSet = $this->search();
    }
    protected function search(){
        $client = Indexer::ClientBuild();
        return $client->search($this->query);
    }
}