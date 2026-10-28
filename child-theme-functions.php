<?php

// to import all styles from parent theme:

 add_action( 'wp_enqueue_scripts', 'my_child_theme_enqueue_styles' );
 function my_child_theme_enqueue_styles() {
 wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
 wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array(
 'parent-style' ) );
 }

// add functions below this:-

// i. preloader design and scripts___
add_action( 'wp_body_open', function() {
    ?>
<style>
  .rcl_preloader {
    position: fixed;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh;
    background: #ffffffae;
    backdrop-filter: blur(9px);
    z-index: 2000;
    display: none;
    align-items: center;
    justify-content: center;
    opacity: 1;
    transition: opacity 0.7s linear;
  }
  .preload_logo {
    position: absolute;
	border-radius: 100px!important;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation: preload-spin 1s linear infinite;
    background-color: white;
    padding: 3px;
  }
  .rcl_preloader img {
    border-radius: 100px!important;
    width: 47px;
    height: 47px;
  }
  @keyframes preload-spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
  }
</style>

    <div class="rcl_preloader" id="rcl_preloader" aria-hidden="true">
      <img class="preload_logo" src="/wp-content/uploads/2026/04/f-recycle-preloader.webp" alt="Loading...">
    </div>

<script data-no-optimize="1">
  (function() {
    const preloader = document.getElementById('rcl_preloader');
    let showPreloaderTimeout = setTimeout(() => {
      if (preloader) preloader.style.display = 'flex';
    }, 50);
	  
    window.addEventListener('load', function() {
          clearTimeout(showPreloaderTimeout);
          if (preloader) {
            preloader.style.opacity = '0';
            setTimeout(() => {
              preloader.style.display = 'none';
            }, 400);
          }
        });
  })();
</script>
    <?php
}, 1 );

// ii. to support svg file uploads___
function cc_mime_types($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');

// iii. CF field input filters and validation server lvl___
// Name, PIN, Date field if required and non required
add_filter('wpcf7_validate_text*', 'custom_form_validation_logic', 20, 2);
add_filter('wpcf7_validate_text', 'custom_form_validation_logic', 20, 2);
add_filter('wpcf7_validate_tel*', 'custom_form_validation_logic', 20, 2);
add_filter('wpcf7_validate_tel', 'custom_form_validation_logic', 20, 2);
add_filter('wpcf7_validate_date*', 'custom_form_validation_logic', 20, 2);
add_filter('wpcf7_validate_date', 'custom_form_validation_logic', 20, 2);

function custom_form_validation_logic($result, $tag) {
    $name = $tag->name;
    $value = isset($_POST[$name]) ? trim($_POST[$name]) : '';

    // 1. Full Name input filters (Letters and Spaces only)
    if ($name == 'qtf-name' && $value != '') {
        if (!preg_match('/^[a-zA-Z\s]+$/', $value)) {
            $result->invalidate($tag, "Name contains only letters and spaces");
        }
    }

    // 2. PIN Code Validation (Exactly 6 Digits)
    if ($name == 'qtf-pin' && $value != '') {
        if (!ctype_digit($value) || strlen($value) !== 6) {
            $result->invalidate($tag, "Please enter 6-digit PIN code");
        }
    }
	
	// 3. Phone number validation (10-digitis only)
	if ($name == 'qtf-tel' && $value != '') {
    if (!ctype_digit($value) || strlen($value) !== 10) {
        $result->invalidate($tag, "Please enter 10-digit phone number");
    }
	}

    // 4. Date Validation (Tomorrow to +13 Days)
    if ($name == 'qtf-date' && $value != '') {
        $selected_date = strtotime($value);
        $min_date = strtotime('tomorrow');
        $max_date = strtotime('+13 days');

        if ($selected_date < $min_date || $selected_date > $max_date) {
            $result->invalidate($tag, "Enter a date within next 14 days");
        }
    }

    return $result;
}

// iv. Integration of form logics and whatsapp___
function enqueue_cf7_custom_logic() {
    // This loads the script only in the footer
    wp_enqueue_script(
        'cf7-custom-logic', 
        get_stylesheet_directory_uri() . '/all_js/cf7-whatsapp-integration.js', 
        array(),
		'1.0.3',
        true 
    );
}
add_action('wp_enqueue_scripts', 'enqueue_cf7_custom_logic');

// v. Exclude current post from recent post grid__
add_action( 'pre_get_posts', function ( $query ) {
    // Only run on the front-end and for your post grid query
    if ( ! is_admin() && is_singular( 'post' ) && $query->is_main_query() === false ) {
        $query->set( 'post__not_in', array( get_the_ID() ) );
    }
} );

/** CORE LOGIC to extract and show the value of custom metas from post, in post grid or other widgets containing post metas. As all widget need pro to show custom filed metas by default **/
function pmw_get_compiled_event_meta( $post_id ) {
    $date     = get_post_meta( $post_id, 'event_date', true );
    $time     = get_post_meta( $post_id, 'event_time', true );
    $duration = get_post_meta( $post_id, 'event_duration', true );
    $society  = get_post_meta( $post_id, 'event_society', true );
    $loc      = get_post_meta( $post_id, 'event_location', true );

    // If every single meta field is empty, return empty string
    if ( empty( $date ) && empty( $time ) && empty( $duration ) && empty( $society ) && empty( $loc ) ) { 
        return ''; 
    }
    // On-the-fly Date conversion to DD-MM-YY for front-end readers
    $display_date = $date;
    if ( ! empty( $date ) ) {
        $timestamp = strtotime( $date );
        if ( $timestamp !== false ) {
            $display_date = date( 'd-m-y', $timestamp ); // Converts 2026-06-26 to 26-06-26
        }
    }

    // Build the location/society line smoothly
    $full_location = array();
    if ( ! empty( $society ) ) { $full_location[] = $society; }
    if ( ! empty( $loc ) )     { $full_location[] = $loc; }
    $location_text = implode( ', ', $full_location );

    $html = '';
    
    // Top Row: Date | Time | Duration
    if ( ! empty( $date ) )     { $html .= '<span class="ev-meta-item ev-date"><i class="fas fa-calendar-alt"></i> ' . esc_html( $display_date ) . '</span>'; }
    if ( ! empty( $time ) )     { $html .= ' <span class="ev-sep">|</span> <span class="ev-meta-item ev-time"><i class="fas fa-clock"></i> ' . esc_html( $time ) . '</span>'; }
    if ( ! empty( $duration ) ) { $html .= ' <span class="ev-sep">|</span> <span class="ev-meta-item ev-duration"><i class="fas fa-stopwatch"></i> ' . esc_html( $duration ) . '</span>'; }   
    // Bottom Row: Location / Society
    if ( ! empty( $location_text ) ) { 
        $html .= '<br><span class="ev-meta-item ev-loc"><i class="fas fa-map-marker-alt"></i> ' . esc_html( $location_text ) . '</span>'; 
    }

    return $html;
}

// vi. Event metas showing in EAEL post grids widget__
function pmw_replace_ea_date_with_event_meta( $the_date, $d, $post ) {
    if ( is_admin() && ! wp_doing_ajax() ) { return $the_date; }

    static $run_counter = array();
    $run_counter[$post->ID] = isset( $run_counter[$post->ID] ) ? $run_counter[$post->ID] + 1 : 1;
    if ( $run_counter[$post->ID] > 1 ) { return ''; }

    $meta_html = pmw_get_compiled_event_meta( $post->ID );
    return ! empty( $meta_html ) ? $meta_html : $the_date;
}
add_filter( 'get_the_date', 'pmw_replace_ea_date_with_event_meta', 999, 3 );

// Shortcodes __

// a. Custom metas for Single EVENT Structure--
function pmw_event_meta_shortcode() {
    if ( is_admin() ) { return ''; }
    $post_id = get_the_ID();
    return $post_id ? '<div class="single-post-ev-meta">' . pmw_get_compiled_event_meta( $post_id ) . '</div>' : '';
}
add_shortcode( 'event_meta', 'pmw_event_meta_shortcode' );

// b. Custom metas for Single BLOG Structure--
function pmw_custom_blog_meta_shortcode() {
    $post_id = get_the_ID();
    $date = get_the_date( 'F j, Y', $post_id );
    $taxonomy = 'blog_category'; 
    $terms    = get_the_terms( $post_id, $taxonomy );

    $html = '<div class="pmw-custom-blog-meta">';
    
    // Date Output with Calendar Icon
    $html .= '<span class="blog-meta-item blog-date">';
    $html .= '<i class="fas fa-calendar-alt"></i> ' . esc_html( $date );
    $html .= '</span>';

    // Category Output with Folder Icon (Plain Text Only)
    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        $cat_names = array();
        foreach ( $terms as $term ) {
            $cat_names[] = esc_html( $term->name );
        }

        if ( ! empty( $cat_names ) ) {
            $html .= ' <span class="blog-meta-sep">|</span> ';
            $html .= '<span class="blog-meta-item blog-cats">';
            $html .= '<i class="fas fa-folder"></i> ' . implode( ', ', $cat_names );
            $html .= '</span>';
        }
    }

    $html .= '</div>';

    return $html;
}
add_shortcode( 'custom_blog_meta', 'pmw_custom_blog_meta_shortcode' );

// vii. Events switching to upcoming and past automaticlly based on date custom field__
function pmw_automate_event_category_routing() {
    $current_date  = current_time( 'Y-m-d' );
    $upcoming_term = get_category_by_slug( 'upcoming-events' );
    $past_term     = get_category_by_slug( 'past-events' );

    if ( ! $upcoming_term || ! $past_term ) { return; }

    // Query all event posts across both categories at once
    $events = get_posts( array( 'post_type' => 'post', 'posts_per_page' => -1, 'category' => array( $upcoming_term->term_id, $past_term->term_id ) ) );

    foreach ( $events as $event ) {
        $event_date   = get_post_meta( $event->ID, 'event_date', true );
        if ( empty( $event_date ) ) { continue; }

        $current_cats = wp_get_post_categories( $event->ID );

        if ( $event_date < $current_date ) {
            // Expired: Remove upcoming, add past
            $updated = array_merge( array_diff( $current_cats, array( $upcoming_term->term_id ) ), array( $past_term->term_id ) );
        } else {
            // Future/Today: Remove past, add upcoming
            $updated = array_merge( array_diff( $current_cats, array( $past_term->term_id ) ), array( $upcoming_term->term_id ) );
        }

        wp_set_post_categories( $event->ID, array_unique( $updated ), false );
    }
}
add_action( 'init', 'pmw_automate_event_category_routing' );

// viii. Minimalist Custom Post Type for Blogs
function pmw_register_blog_system() {
    
    // 1. Register Blog Categories Taxonomy
    $cat_labels = array(
        'name'          => 'Blog categories',
        'singular_name' => 'Blog category',
        'search_items'  => 'Search blog categories',
        'all_items'     => 'All blog categories',
        'edit_item'     => 'Edit blog category',
        'update_item'   => 'Update blog category',
        'add_new_item'  => 'Add new blog category',
        'new_item_name' => 'New blog category name',
        'menu_name'     => 'Categories',
    );

    register_taxonomy( 'blog_category', array( 'blog' ), array(
        'hierarchical'      => true,
        'labels'            => $cat_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'blog-category', 'with_front' => false ),
        'show_in_rest'      => true, // Enables Gutenberg/Elementor support
    ));

    // 2. Register Blog Custom Post Type
    $cpt_labels = array(
        'name'               => 'Blogs',
        'singular_name'      => 'Blog',
        'menu_name'          => 'Blogs',
        'name_admin_bar'     => 'Blog',
        'add_new'            => 'Add new',
        'add_new_item'       => 'Add new blog',
        'new_item'           => 'New blog',
        'edit_item'          => 'Edit blog',
        'view_item'          => 'View blog',
        'all_items'          => 'All blogs',
        'search_items'       => 'Search blogs',
        'not_found'          => 'No blogs found.',
        'not_found_in_trash' => 'No blogs found in trash.',
    );

    $args = array(
        'labels'             => $cpt_labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'blogs', 'with_front' => false ),
        'capability_type'    => 'post',
        'has_archive'        => 'blogs',
        'hierarchical'       => false,
        'menu_position'      => 6, // Sits directly below standard Posts (Events)
        'menu_icon'          => 'dashicons-welcome-write-blog',
        'show_in_rest'       => true,
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
        'taxonomies'         => array( 'blog_category' ),
    );

    register_post_type( 'blog', $args );
}
add_action( 'init', 'pmw_register_blog_system' );

// **All Other codes above this**

// 1. ANALYTICS INJECTION HOOK
function pmw_add_analytics_tracking_tag() {
    ?>
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=gtagid"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'gtagid');
</script>
    <!-- End tracking script -->
    <?php
}
add_action( 'wp_head', 'pmw_add_analytics_tracking_tag', 1 );

// 2. Add Pinterest Domain Verification Meta Tag
function pmw_add_pinterest_meta_tag() {
    ?>
    <meta name="p:domain_verify" content="pinterestid"/>
    <?php
}
add_action( 'wp_head', 'pmw_add_pinterest_meta_tag', 2 );
