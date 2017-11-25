<?php
/**
 * This file is part of search-manager bundle package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 25/11/17
 * Time: 13:54
 */

namespace EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp;


interface IndexInterface
{
    /**
     * @param SearchReQuestInterface $searchRequest
     * @return array
     */
    public function search(SearchReQuestInterface $searchRequest);

}