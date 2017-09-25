<?php
namespace CFDIReader\PostValidations;

class Validators implements \IteratorAggregate, \Countable
{
    /** @var ValidatorInterface[] */
    private $validators = [];

    /**
     * Append a validator into the list of validators,
     * this will search if the validator object does not exists
     * @param ValidatorInterface $validator
     */
    public function append(ValidatorInterface $validator)
    {
        if (false === $this->getIndex($validator)) {
            $this->validators[] = $validator;
        }
    }

    /**
     * Remove a validator from the list of validators
     * @param ValidatorInterface $validator
     */
    public function remove(ValidatorInterface $validator)
    {
        $index = $this->getIndex($validator);
        if (false !== $index) {
            unset($this->validators[$index]);
            $this->validators = array_values($this->validators);
        }
    }

    /**
     * Count of validators
     * @return int
     */
    public function count()
    {
        return count($this->validators);
    }

    /**
     * Return the index of a registered validator
     * @param ValidatorInterface $validator
     * @return int|false index of the validator, return FALSE if not found
     */
    public function getIndex(ValidatorInterface $validator)
    {
        return array_search($validator, $this->validators, true);
    }

    /**
     * Return a registered instance of a validator identified by index
     * @param int $index
     * @return ValidatorInterface
     */
    public function get($index)
    {
        if (! array_key_exists($index, $this->validators)) {
            throw new \OutOfBoundsException('Validator does not exists');
        }
        return $this->validators[$index];
    }

    /**
     * @return ValidatorInterface[]|\ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->validators);
    }
}
