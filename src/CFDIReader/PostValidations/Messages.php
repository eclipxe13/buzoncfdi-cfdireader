<?php

namespace CFDIReader\PostValidations;

class Messages implements \IteratorAggregate, \Countable
{

    private $messages = [];

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

    public function count()
    {
        return count($this->messages);
    }

    public function get($index)
    {
        if (! array_key_exists($index, $this->messages)) {
            throw new \OutOfBoundsException("Message does not exists");
        }
        return $this->messages[$index];
    }

    public function getFirst()
    {
        return (count($this->messages)) ? $this->messages[0] : false;
    }

    public function getLast()
    {
        return (0 !== $count = count($this->messages)) ? $this->messages[$count - 1] : false;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->messages);
    }


    public function all()
    {
        return $this->messages;
    }


}
