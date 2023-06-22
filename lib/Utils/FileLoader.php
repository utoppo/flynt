<?php

namespace Flynt\Utils;

use DirectoryIterator;

/**
 * Provides a set of methods that are used to load files.
 */
class FileLoader
{
    /**
     * Array of already loaded relative filepaths.
     *
     * @var static array $loadedFiles
     */
    private static $loadedFiles = [];

    /**
     * Iterates through a directory and executes the provided callback function
     * on each file or folder in the directory (excluding dot files).
     *
     * @since 0.1.0
     *
     * @param string $dir Absolute path to the directory.
     * @param callable $callback The callback function.
     *
     * @return array An array of the callback results.
     */
    public static function iterateDir(string $dir, callable $callback)
    {
        $output = [];

        if (!is_dir($dir)) {
            return $output;
        }

        $directoryIterator = new DirectoryIterator($dir);

        foreach ($directoryIterator as $file) {
            if ($file->isDot()) {
                continue;
            }
            $callbackResult = call_user_func($callback, $file);
            array_push($output, $callbackResult);
        }

        return $output;
    }

    /**
     * Recursively require all files in a specific directory.
     *
     * By default, requires all php files in a specific directory once.
     * Optionally able to specify the files in an array to load in a certain order.
     * Starting and trailing slashes will be stripped for the directory and all files provided.
     *
     * @since 0.1.0
     *
     * @param string $dir Directory to search through.
     * @param array $files Optional array of files to include. If this is set, only the files specified will be loaded.
     * @param boolean $loadFromChildTheme  Are the files to be loaded located inside a child theme.
     *
     * @return void
     */
    public static function loadPhpFiles(string $dir, array $files = [], ?bool $loadFromChildTheme = false)
    {
        $dir = trim($dir, '/');

        if (count($files) === 0) {
            $themeDir = $loadFromChildTheme ? get_stylesheet_directory() : get_template_directory();

            $dir = $themeDir . '/' . $dir ;
            $phpFiles = [];

            self::iterateDir($dir, function ($file) use (&$phpFiles, &$themeDir, &$loadFromChildTheme) {
                if ($file->isDir()) {
                    $dirPath = trim(str_replace($themeDir, '', $file->getPathname()), '/');
                    self::loadPhpFiles($dirPath, [], $loadFromChildTheme);
                } elseif ($file->isFile() && $file->getExtension() === 'php') {
                    $filePath = $file->getPathname();
                    $phpFiles[] = $filePath;
                }
            });

            // Sort files alphabetically.
            sort($phpFiles);
            foreach ($phpFiles as $phpFile) {
                $fileRelative = trim(str_replace($themeDir, '', $phpFile), '/');
                if (!in_array($fileRelative, self::$loadedFiles)) {
                    require_once $phpFile;
                    $loadedFilesFlipped = array_flip(self::$loadedFiles);
                    $loadedFilesFlipped[$fileRelative] = 1;
                    self::$loadedFiles = array_keys($loadedFilesFlipped);
                }
            }
        } else {
            sort($files);

            foreach ($files as $file) {
                $filePath = $dir . '/' . ltrim($file, '/');

                if (!locate_template($filePath, true, true)) {
                    trigger_error(
                        sprintf(__('Error locating %s for inclusion', 'flynt'), $filePath),
                        E_USER_ERROR
                    );
                }
            }
        }
    }
}
