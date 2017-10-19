<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 09/07/17
 * Time: 18:05
 */

namespace EscapeHither\SearchManagerBundle\Tests\Utils;


class DataTest {
    private $age;
    private $name;
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