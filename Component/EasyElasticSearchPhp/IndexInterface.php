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
 * Index interface.
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
interface IndexInterface
{
    /**
     * Search over an index.
     *
     * @param SearchReQuestInterface $searchRequest
     *
     * @return array
     */
    public function search(SearchReQuestInterface $searchRequest);
}
