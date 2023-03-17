<?php

/**
 * This file is part of the margusk/accessors package.
 *
 * @author  Margus Kaidja <margusk@gmail.com>
 * @link    https://github.com/marguskaidja/php-accessors
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

declare(strict_types=1);

namespace margusk\Accessors;

use ReflectionException;
use LogicException;

/** @api */
trait Accessible
{
    /**
     * @param  string   $method
     * @param  mixed[]  $args
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function __call(string $method, array $args): mixed
    {
        return ClassConf::handleMagicCall($this, $method, $args);
    }
    
    /**
     * @return string
     * @throws ReflectionException
     */
    public function __toString() : string{
        return ClassConf::handleMagicToString($this);
    }

    /**
     * @param  string  $propertyName
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function __get(string $propertyName): mixed
    {
        return ClassConf::handleMagicGet($this, $propertyName);
    }

    /**
     * @param  string  $propertyName
     * @param  mixed   $propertyValue
     *
     * @return void
     * @throws ReflectionException
     */
    public function __set(string $propertyName, mixed $propertyValue): void
    {
        ClassConf::handleMagicSet($this, $propertyName, $propertyValue);
    }

    /**
     * @param  string  $propertyName
     *
     * @return bool
     * @throws ReflectionException
     */
    public function __isset(string $propertyName): bool
    {
        return ClassConf::handleMagicIsset($this, $propertyName);
    }

    /**
     * @param  string  $propertyName
     *
     * @return void
     * @throws ReflectionException
     */
    public function __unset(string $propertyName): void
    {
        ClassConf::handleMagicUnset($this, $propertyName);
    }

    /**  
     * @return void
     * @throws LogicException
     */
    public function __clone() : void{
        ClassConf::handleMagicClone($this);
    }

    /**  
     * @return array
     * @throws LogicException
     */
    public function __serialize() : array{
        ClassConf::handleMagicSerialize($this);
    }

    /**  
     * @param  array  $data
     * @return void
     * @throws LogicException
     */
    public function __unserialize(array $data) : void{
        ClassConf::handleMagicUnserialize($this);
    }
}
