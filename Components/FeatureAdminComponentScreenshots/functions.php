<?php

namespace Flynt\Components\FeatureAdminComponentScreenshots;

use Flynt\ComponentManager;

add_action('admin_enqueue_scripts', function () {
    $componentManager = ComponentManager::getInstance();
    $templateDirectory = get_template_directory();
    $data = [
        'components' => json_encode($componentManager->getAllWithProperties()),
    ];
    wp_localize_script('Flynt/assets/admin', 'FlyntComponentScreenshots', $data);
});

if (class_exists('acf')) {
    if (is_admin()) {
        // add image to the flexible content component name
        add_filter('acf/fields/flexible_content/layout_title', function ($title, $field, $layout, $i) {
            $componentManager = ComponentManager::getInstance();
            $componentName = ucfirst($layout['name']);
            $component = $componentManager->get($componentName);

            $componentScreenshotPath = "{$component->getPath()}screenshot.png";
            $componentThemePath = $component->isFromChildTheme() ?  get_stylesheet_directory() : get_template_directory();
            $componentThemeUrl = $component->isFromChildTheme() ?  get_stylesheet_directory_uri() : get_template_directory_uri();
            $componentPath = str_replace($componentThemePath, '', $componentScreenshotPath);
            $componentScreenshotUrl = "{$componentThemeUrl}{$componentPath}";

            if (is_file($componentScreenshotPath)) {
                $newTitle = '<span class="flyntComponentScreenshot">';
                $newTitle .= '<img class="flyntComponentScreenshot-previewImageSmall" src="' . $componentScreenshotUrl . '" loading="lazy">';
                $newTitle .= '<span class="flyntComponentScreenshot-label">' . $title . '</span>';
                $newTitle .= '</span>';
                $title = $newTitle;
            }
            return $title;
        }, 11, 4);
    }
}
