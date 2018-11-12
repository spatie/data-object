<?php

declare(strict_types=1);

namespace Spatie\DataTransferObject;

use ReflectionClass;
use ReflectionProperty;

abstract class DataTransferObject
{
    /** @var array */
    protected $allValues = [];

    /** @var array */
    protected $exceptKeys = [];

    /** @var array */
    protected $onlyKeys = [];

    public function __construct(array $parameters)
    {
        // see http://php.net/manual/en/ini.core.php#ini.zend.assertions
        assert($this->isValid($parameters));
    }

    private function isValid(array $parameters): bool
    {
        $class = new ReflectionClass(static::class);

        $properties = $this->getPublicProperties($class);

        foreach ($properties as $property) {
            if (
                ! isset($parameters[$property->getName()])
                && ! $property->isNullable()
            ) {
                throw DataTransferObjectError::uninitialized($property);
            }

            $value = $parameters[$property->getName()] ?? null;

            $property->set($value);

            unset($parameters[$property->getName()]);

            $this->allValues[$property->getName()] = $property->getValue($this);
        }

        if (count($parameters)) {
            throw DataTransferObjectError::unknownProperties(array_keys($parameters), $class->getName());
        }

        return true;
    }

    public function all(): array
    {
        return $this->allValues;
    }

    /**
     * @param string ...$keys
     *
     * @return static
     */
    public function only(string ...$keys): DataTransferObject
    {
        $valueObject = clone $this;

        $valueObject->onlyKeys = array_merge($this->onlyKeys, $keys);

        return $valueObject;
    }

    /**
     * @param string ...$keys
     *
     * @return static
     */
    public function except(string ...$keys): DataTransferObject
    {
        $valueObject = clone $this;

        $valueObject->exceptKeys = array_merge($this->exceptKeys, $keys);

        return $valueObject;
    }

    public function toArray(): array
    {
        if (count($this->onlyKeys)) {
            return Arr::only($this->all(), $this->onlyKeys);
        }

        return Arr::except($this->all(), $this->exceptKeys);
    }

    /**
     * @param \ReflectionClass $class
     *
     * @return array|\Spatie\DataTransferObject\Property[]
     */
    protected function getPublicProperties(ReflectionClass $class): array
    {
        $properties = [];

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            $properties[$reflectionProperty->getName()] = Property::fromReflection($this, $reflectionProperty);
        }

        return $properties;
    }
}
