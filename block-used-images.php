<?php
/**
Plugin Name: Block used images
Description: Restricts users from using the same image from events that are currently happening
Version: 1.0
Author: Dessain Saraiva
Author URI: https://github.com/jdsaraiva
License: GPLv2
 */

// notify if the event's featured image is already being used
function bui_check( $post_id ) {

    // If this is just a revision ignore it
    if ( wp_is_post_revision( $post_id ) )
        return;
    // Or a Draft
    if ( get_post_status ( $post_id ) == 'draft')
        return;

    // fetch post thumbnail ID
    $post_thumb = get_post_thumbnail_id( $post_id );
    $event_ids_matches = null;

    // Retrieve upcoming events
    $events = tribe_get_events( array(
        'eventDisplay' => 'upcoming'
    ) );

    foreach ( $events as $event ) {

        // ignore draft posts with no image set
        if ( get_post_status (  $event->ID ) != 'draft')
            $loop_post_thumb = get_post_thumbnail_id( $event->ID );

        // ignore the current event and events with no image set
        if ($post_thumb == $loop_post_thumb && $post_id !== $event->ID) {

            // alert the user, hide the success message
            $error_message = '<style>div#message {display: none;}</style><div class="error"><p>EVENT NOT PUBLISHED, image is already being used at: ' . $event->post_title . '</p></div>';
            set_transient( 'image_error', $error_message, 28800 );

            // event stays in draft
            wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );

        }
    }
}

add_action( 'save_post', 'bui_check' );

function bui_notices(){
    if(get_transient( 'image_error' )) print get_transient( 'image_error' );
    delete_transient( 'image_error' );
}
add_action( 'admin_notices', 'bui_notices' );