<?php

declare(strict_types=1);

namespace RulerZ\Context;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ObjectContext implements \ArrayAccess
{
    /**
     * @var mixed
     */
    private $object;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $accessor;

    /**
     * @param mixed $object The object to extract data from.
     */
    public function __construct($object)
    {
        $this->object = $object;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Returns the object of the context.
     *
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($id)
    {
        return $this->getValue($this->object, $id, true);
    }

    private function getValue($object, $id, bool $shouldWrap)
    {
        $thing = $object instanceof ObjectContext ? $object->getObject() : $object;

        if (is_array($thing) || $thing instanceof \Iterator) {
            $result = [];

            foreach ($thing as $element) {
                $value = $this->getValue($element, $id, false);

                if (null === $value) {
                    continue;
                }

                // this might be wrong but it solves todays problem
                if (is_iterable($value)) {
                    $result = array_merge($result, $value instanceof \Iterator ? iterator_to_array($value) : $value);
                } else {
                    $result[] = $value;
                }
            }

            return new static($result);
        }

        $value = $this->accessor->getValue($object instanceof ObjectContext ? $object->getObject() : $object, $id);

        if (! $shouldWrap || is_scalar($value) || $value instanceof UuidInterface || $value === null) {
            return $value;
        }

        return new static($value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($id)
    {
        return $this->accessor->isReadable($this->object, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($id, $value)
    {
        throw new \RuntimeException('Context is read-only.');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($id)
    {
        throw new \RuntimeException('Context is read-only.');
    }
}
