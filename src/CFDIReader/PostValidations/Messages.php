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
     * Add a message string to the collection
     * @param string $message
     * @return void
     */
    public function add(string $message)
    {
        if ('' === $message) {
            throw new \InvalidArgumentException('Message must be a non-empty string');
        }
        $this->messages[] = $message;
    }

    public function count(): int
    {
        return count($this->messages);
    }

    /**
     * Get a message by index
     *
     * @param int $index
     * @return string
     * @throws \OutOfBoundsException if the message does not exists
     */
    public function get(int $index): string
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
    public function all(): array
    {
        return $this->messages;
    }
}
