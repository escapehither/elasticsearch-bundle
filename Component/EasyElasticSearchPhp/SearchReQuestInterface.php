<?php
/**
 * This file is part of the search-manager-bundle package.
 * (c) Georden Gaël LOUZAYADIO <geordenmaster@hotmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 18/10/17
 * Time: 22:59
 */

namespace EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp;

/**
 * Interface SearchReQuestInterface
 * @package EscapeHither\SearchManagerBundle\Component\EasyElasticSearchPhp
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