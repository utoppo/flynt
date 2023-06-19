<?php

namespace Flynt;

/**
 * A Component object stores information that is needed for
 * the Flynt component to be loaded inside WordPress backend and frontend.
 *
 */
class Component
{
    /**
     * The name of the component.
     *
     * @var string
     */
    protected $name;

    /**
     * The path of the component.
     *
     * @var string
     */
    protected $path;

    /**
     * Constructor.
     *
     * @param string $name The name of the component.
     * @param string $path The path to the component.
     *
     * @return void
     */
    public function __construct(string $name, string $path)
    {
        $this->setName($name);
        $this->setPath($path);
    }

    /**
     * Get the name of the component.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name of the component.
     *
     * @param string $name The name of the component.
     *
     * @return void
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get the path of the component.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the path of a component.
     *
     * @param string $path The path of the component.
     *
     * @return void
     */
    public function setPath(string $path)
    {
        $this->path = $path;
    }
}
