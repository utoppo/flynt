<?php

namespace Flynt\Components\FeatureFrontendEditor;

use Timber\Timber;

add_action('init', function () {
    if (isset($_GET['hideAdminBar']) && $_GET['hideAdminBar']) {
        add_filter('show_admin_bar', '__return_false');
    }
});

add_filter('Flynt/addComponentData?name=FeatureFrontendEditor', function ($data) {
    $data['isSidebarOpen'] = isset($_GET['frontendEditorVisible']) && $_GET['frontendEditorVisible'];
    $data['spinnerUrl'] = get_admin_url(null, 'images/spinner-2x.gif');
    $postID = get_the_ID();

    $nonce = wp_create_nonce('post_preview_' . $postID);
    $query_args['preview_id'] = $postID;
    $query_args['preview_nonce'] = $nonce;
    $query_args['hideAdminBar'] = "true";
    $preview_link = get_preview_post_link($postID, $query_args);

    $data['jsonData'] = [
        'restUrl' => get_rest_url(),
        'postId' => $postID,
        'previewLink' => $preview_link
    ];

    return $data;
});

add_action('wp_footer', function () {
    if (!userCanEditPost()) {
        return;
    }

    $context = Timber::context();
    Timber::render_string('{{ renderComponent("FeatureFrontendEditor") }}', $context);
});

function userCanEditPost()
{
    global $post;

    if (!$post) {
        return false;
    }

    return current_user_can('edit_post', $post->ID);
}


add_action('admin_bar_menu', function ($wp_admin_bar) {
    if (!userCanEditPost() || is_admin() || !is_admin_bar_showing()) {
        return;
    }

    $args = [
        'id' => 'frontend-editing',
        'title' => __('Frontend Editor', 'flynt'),
        'meta' => [
            'class' => 'frontend-editing-button',
        ]
    ];
    $wp_admin_bar->add_node($args);
}, 80);

/*
add_action('rest_api_init', function () {
    register_rest_route('frontend/', '/post/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => function ($data) {
            $context = Timber::context();

            $res = [
                '__html' => Timber::compile('templates/page.twig', $context)
            ];

            return json_encode($res);
        },
    ));
});*/
