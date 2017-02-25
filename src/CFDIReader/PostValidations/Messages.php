<?php
namespace CFDIReader\PostValidations;

/**
 * Simple array of string messages
 * @package CFDIReader\PostValidations
 */
class Messages implements \IteratorAggregate, \Countable
{
    /** @var string[] */
    private $messages = [];

    /**
     * @param string $message
     */
    public function add($message)
    {
        if (! is_string($message)) {
            throw new \InvalidArgumentException('Message must be a valid string');
        }
        if (empty($message)) {
            throw new \InvalidArgumentException('Message must be a non-empty string');
        }
        $this->messages[] = (string) $message;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->messages);
    }

    /**
     * @param int $index
     * @return string
     */
    public function get($index)
    {
        if (! array_key_exists($index, $this->messages)) {
            throw new \OutOfBoundsException('Message does not exists');
        }
        return $this->messages[$index];
    }

    /**
     * Get the first message, FALSE if none
     * @return string|false
     */
    public function getFirst()
    {
        return (count($this->messages)) ? $this->messages[0] : false;
    }

    /**
     * Get the last message, FALSE if none
     * @return string|false
     */
    public function getLast()
    {
        return (0 !== $count = count($this->messages)) ? $this->messages[$count - 1] : false;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->messages);
    }

    /**
     * Return an array of string messages
     * @return string[]
     */
    public function all()
    {
        return $this->messages;
    }
}
