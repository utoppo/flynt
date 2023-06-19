<?php

namespace Flynt;

use Flynt\Component;

/**
 * Provides a set of methods that are used to register and get
 * information about components.
 */
class ComponentManager
{
    /**
     * The internal list (array) of components.
     *
     * @var array
     */
    protected $components = [];

    /**
     * The instance of the class.
     *
     * @var ComponentManager
     */
    protected static $instance = null;

    /**
     * Get the instance of the class.
     *
     * @return ComponentManager
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Clone
     *
     * Prevent cloning with 'protected' keyword
     *
     * @return void
     */
    protected function __clone()
    {
    }

    /**
     * Constructor.
     *
     * Prevent instantiation with 'protected' keyword
     *
     * @return void
     */
    protected function __construct()
    {
    }

    /**
     * Register a component.
     *
     * @param string $componentName The name of the component.
     * @param string $componentPath The path to the component.
     * @param boolean $isFromChildTheme Is the component located inside a child theme.
     *
     * @return boolean
     */
    public function registerComponent(string $componentName, ?string $componentPath = null, ?bool $isFromChildTheme = false)
    {
        // Check if component already registered.
        if ($this->isRegistered($componentName)) {
            trigger_error("Component {$componentName} is already registered!", E_USER_WARNING);
            return false;
        }

        // Register component / require functions.php.
        $componentPath = trailingslashit(apply_filters('Flynt/componentPath', $componentPath, $componentName));

        // Add component to internal list (array).
        $this->add($componentName, $componentPath, $isFromChildTheme);

        do_action('Flynt/registerComponent', $componentName);
        do_action("Flynt/registerComponent?name={$componentName}", $componentName);

        return true;
    }

    /**
     * Get the path to a component file.
     *
     * @param string $componentName The name of the component.
     * @param string $fileName The name of the file.
     *
     * @return string|boolean
     */
    public function getComponentFilePath(string $componentName, string $fileName = 'index.php')
    {
        $componentDir = $this->getComponentDirPath($componentName);

        if (false === $componentDir) {
            return false;
        }

        // Dir path already has a trailing slash.
        $filePath = $componentDir . $fileName;

        return is_file($filePath) ? $filePath : false;
    }

    /**
     * Get the path to a component directory.
     *
     * @param string $componentName The name of the component.
     *
     * @return string|boolean
     */
    public function getComponentDirPath(string $componentName)
    {
        $dirPath = $this->get($componentName)->getPath();

        // Check if dir exists.
        if (!is_dir($dirPath)) {
            return false;
        }

        return $dirPath;
    }

   /**
    * Add a component to the internal list (array).
    *
    * @param string $name The name of the component.
    * @param string $path The path to the component.
    * @param boolean $isFromChildTheme Is the component located inside a child theme.
    *
    * @return boolean
    */
    protected function add(string $name, string $path, ?bool $isFromChildTheme = false)
    {
        $component = new Component($name, $path, $isFromChildTheme);
        $this->components[$component->getName()] = $component;
        $this->components[$component->getName()]->setIsRegistered(true);
        return true;
    }

    /**
     * Get a component from the internal list (array).
     *
     * @param string $componentName The name of the component.
     *
     * @return string|boolean
     */
    public function get(string $componentName)
    {
        // Check if component exists / is registered.
        if (!$this->isRegistered($componentName)) {
            trigger_error("Cannot get component: Component '{$componentName}' is not registered!", E_USER_WARNING);
            return false;
        }

        return $this->components[$componentName];
    }

    /**
     * Remove a component from the internal list (array).
     *
     * @param string $componentName The name of the component.
     *
     * @return void
     */
    public function remove(string $componentName)
    {
        unset($this->components[$componentName]);
    }

    /**
     * Get all components from the internal list (array).
     *
     * @return array
     */
    public function getAll()
    {
        $allComponents = [];
        foreach ($this->components as $key => $value) {
            $allComponents[$key] = $value->getPath();
        }
        return $allComponents;
    }

    /**
     * Remove all components from the internal list (array).
     *
     * @return void
     */
    public function removeAll()
    {
        $this->components = [];
    }

    /**
     * Check if a component is registered.
     *
     * @param string $componentName The name of the component.
     *
     * @return boolean
     */
    public function isRegistered(string $componentName)
    {
        foreach ($this->components as $component) {
            if ($component->getName() === $componentName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all components that have a script.js file.
     *
     * @return array
     */
    public function getComponentsWithScript()
    {
        $componentsWithScripts = [];
        foreach ($this->components as $componentName => $component) {
            $componentPath = str_replace('/dist/', '/', $component->getPath());
            $relativeComponentPath = trim(str_replace(get_template_directory() . '/Components/', '', $componentPath), '/');
            if (file_exists($componentPath . '/script.js')) {
                $componentsWithScripts[$componentName] = $relativeComponentPath;
            }
        }
        return $componentsWithScripts;
    }
}
