<?php

get_header();

if ( class_exists( '\Elementor\Plugin' ) ) {
    echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( 6262, true );
}

get_footer();
