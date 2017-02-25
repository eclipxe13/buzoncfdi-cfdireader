<?php
namespace CFDIReader\PostValidations;

/**
 * This class is a collection of messages of different types
 */
class Issues implements \IteratorAggregate
{
    /**
     * Messages array
     * @var Messages[]
     */
    private $messages = [];

    /**
     * Short cut to append a message string into the message collection
     * @param string $type
     * @param string $message
     */
    public function add($type, $message)
    {
        $this->messages($type)->add($message);
    }

    /**
     * Return a message collection of the selected type
     * If the type does not exists the object is created
     * @param string $type
     * @return Messages
     */
    public function messages($type)
    {
        if (! is_string($type) || empty($type)) {
            throw new \InvalidArgumentException('The type of messages must be a non-empty string');
        }
        if (! array_key_exists($type, $this->messages)) {
            $this->messages[$type] = new Messages();
        }
        return $this->messages[$type];
    }

    /**
     * The list of current registered types
     * @return array
     */
    public function types()
    {
        return array_keys($this->messages);
    }

    /**
     * Copy all messages from source to this object
     * @param Issues $issues
     */
    public function import(Issues $issues)
    {
        foreach ($issues->types() as $type) {
            $source = $issues->messages($type);
            $destination = $this->messages($type);
            foreach ($source as $message) {
                $destination->add($message);
            }
        }
    }

    public function all()
    {
        $contents = [];
        foreach ($this->messages as $type => $messages) {
            if ($messages->count()) {
                $contents[$type] = $messages->all();
            }
        }
        return $contents;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->messages);
    }
}
