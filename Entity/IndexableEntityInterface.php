<?php
/**
 * This file is part of the Escape Hither Search Manager Bundle.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\SearchManagerBundle\Entity;

/**
 * Indexable Interface.
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
interface IndexableEntityInterface
{

    /**
     * Get the propety to update
     *
     * @return string
     */
    public function trackMe();
}
