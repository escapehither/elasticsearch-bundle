<?php
/**
 * This file is part of the search bundle manager package.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\SearchManagerBundle\Tests\Utils;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * data test.
 */
class DataTest
{
    /**
     * @Groups({"index"})
     */
    private $age;
    /**
     * @Groups({"index"})
     */
    private $name;
    /**
     * @Groups({"index"})
     */
    private $sportsman;

    // Getters
    public function getName()
    {
        return $this->name;
    }

    public function getAge()
    {
        return $this->age;
    }

    // Issers
    public function isSportsman()
    {
        return $this->sportsman;
    }

    // Setters
    public function setName($name)
    {
        $this->name = $name;
    }

    public function setAge($age)
    {
        $this->age = $age;
    }

    public function setSportsman($sportsman)
    {
        $this->sportsman = $sportsman;
    }
}
