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
     * The relativePath of the component.
     *
     * @var string
     */
    protected $relativePath;

    /**
     * Is the component located inside a child theme.
     *
     * @var boolean
     */
    protected $isFromChildTheme;

    /**
     * Is the component registered.
     *
     * @var boolean
     */
    protected $isRegistered;

    /**
     * Constructor.
     *
     * @param string $name The name of the component.
     * @param string $path The path to the component.
     * @param boolean $isFromChildTheme Is the component located inside a child theme.
     * @param boolean $isRegistered Is the component registered.
     *
     * @return void
     */
    public function __construct(string $name, string $path, bool $isFromChildTheme = false, bool $isRegistered = false)
    {
        $this->setName($name);
        $this->setPath($path);
        $this->setIsFromChildTheme($isFromChildTheme);
        $this->setIsRegistered($isRegistered);
        $this->setupRelativePath();
    }

    /**
     * Get the theme path that belongs to the component.
     *
     *
     * @return string
     */
    public function getThemePath()
    {
        return $this->isFromChildTheme() ? get_stylesheet_directory() : get_template_directory();
    }

    /**
     * Sets up the relativePath of the component, based on its path and its theme path.
     *
     *
     * @return void
     */
    public function setupRelativePath()
    {
        $componentPath = str_replace('/dist/', '/', $this->getPath());
        $relativePath = str_replace($this->getThemePath(), '', $componentPath);
        $this->setRelativePath($relativePath);
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

    /**
     * Check if a component is located inside a child theme.
     *
     * @return boolean
     */
    public function isFromChildTheme()
    {
        return $this->isFromChildTheme;
    }

    /**
     * Get the relativePath of the component.
     *
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * Set the relativePath of a component.
     *
     * @param string $relativePath The relative Path of the component.
     *
     * @return void
     */
    public function setRelativePath(string $relativePath)
    {
        $this->relativePath = $relativePath;
    }

    /**
     * Set wether a component is located inside a child theme.
     *
     * @param boolean $isFromChildTheme Is the component located inside a child theme.
     *
     * @return void
     */
    public function setIsFromChildTheme(bool $isFromChildTheme)
    {
        $this->isFromChildTheme = $isFromChildTheme;
    }

    /**
     * Check if a component is registered.
     *
     * @return boolean
     */
    public function isRegistered()
    {
        return $this->isRegistered;
    }

    /**
     * Set wether a component is registered.
     *
     * @param boolean $isRegistered Is the component registered.
     *
     * @return void
     */
    public function setIsRegistered(bool $isRegistered)
    {
        $this->isRegistered = $isRegistered;
    }
}