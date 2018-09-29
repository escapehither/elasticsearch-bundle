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
 * Interface SearchReQuestInterface
 * 
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
interface SearchReQuestInterface
{
    /**
     * @return array
     */
    public function generateRequest();

    /**
     * @param int $size
     */
    public function setSize($size);

    /**
     * @param int $from
     */
    public function setFrom($from);
}
