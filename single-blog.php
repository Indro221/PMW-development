<?php

get_header();

// Render Elementor Container Template #6209
if ( class_exists( '\Elementor\Plugin' ) ) {
    echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( 6209, true );
} else {
    the_content();
}

get_footer();
