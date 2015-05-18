<?php
/*
  Plugin Name: Simple Food Menu
  Description: Simple food menu manager
  Author: Alex Clarke
  Version: 0.1
*/

//REGISTER SCRIPTS
function sfm_register_scripts() {
    if (!is_admin()) {
        // register  
        wp_register_script('sfm_scripts', plugins_url('assets/simple-food-menu.js', __FILE__));
        // enqueue  
        wp_enqueue_script('sfm_scripts');
    }
}

//REGISTER STYLES
function sfm_register_styles() {
    // register  + enqueue
    if (!is_admin()) {
        wp_register_style('sfm_styles', plugins_url('assets/simple-food-menu.css', __FILE__));
        wp_enqueue_style('sfm_styles');
    }
}

//CREATE SHORTCODE (MAIN MENU)
function sfm_function($type='sfm_function') {

    echo '<div class="sfm-container">';

    // Get current Category
    $get_current_cat = get_term_by('name', single_cat_title('',false), 'simple_food_menu_categories');
    $current_cat = $get_current_cat->term_id;


    // List posts by the terms for a custom taxonomy of any post type
    $post_type = 'simple_food_menu';

    $tax = 'simple_food_menu_categories';

    $tax_terms = get_terms( $tax, 'orderby=name&order=ASC');

    if ($tax_terms) {
        foreach ($tax_terms as $tax_term) {
            $args = array(
                'post_type'         => $post_type,
                "$tax"              => $tax_term->slug,
                'post_status'       => 'publish',
                'posts_per_page'    => 99,
                'orderby'           => 'title',
                'order'             => 'ASC'
            );

            $my_query = null;

            $my_query = new WP_Query($args);


                if( $my_query->have_posts() ) : ?>

                        <div id="sfm-section-<?php echo $tax_term->slug; ?>" class="sfm-menu-section">

                            <header class="sfm-section-header clearfix">

                                <h2>
                                    <?php echo $tax_term->name; // Group name (taxonomy) ?>
                                    <a class="sfm-toggle-item-list">Show Items</a>
                                </h2>

                                <span>
                                    <?php echo $tax_term->description; ?>
                                </span>

                            </header>

                            <div class="sfm-item-list">

                                <?php while ( $my_query->have_posts() ) : $my_query->the_post(); ?>

                                    <div class="sfm-menu-item">

                                        <div class="sfm-menu-item-top cf">

                                            <h3><?php the_title(); ?></h3>

                                            <span>

                                                <?php 
                                                $item_price = get_post_meta( get_the_ID(), 'my_meta_box_text', true );
                                                // check if the custom field has a value
                                                if( ! empty( $item_price ) ) {
                                                    echo '<span>$';
                                                    echo $item_price;
                                                    echo '</span>';
                                                } 
                                                ?>

                                            </span>

                                        </div>

                                        <div class="sfm-menu-item-desc">

                                            <?php the_content(); ?>

                                        </div>

                                    </div>


                                <?php endwhile; // end of loop ?>

                            </div>

                        </div>

                <?php endif; // if have_posts()

            wp_reset_query();

        } // end foreach #tax_terms

    } // end if tax_terms

    echo '</div>';

}

//CREATE SHORTCODE (MENU LINKS)

function sfm_links($type='sfm_links') {

    echo '<div class="sfm-menu-links">';

    // Get current Category
    $get_current_cat = get_term_by('name', single_cat_title('',false), 'simple_food_menu_categories');
    $current_cat = $get_current_cat->term_id;


    // List posts by the terms for a custom taxonomy of any post type
    $post_type = 'simple_food_menu';

    $tax = 'simple_food_menu_categories';

    $tax_terms = get_terms( $tax, 'orderby=name&order=ASC');

    if ($tax_terms) {

        foreach ($tax_terms as $tax_term) {
            $args = array(
                'post_type'         => $post_type,
                "$tax"              => $tax_term->slug,
            );

            $my_query = null;

            $my_query = new WP_Query($args);

                if( $my_query->have_posts() ) : ?>

                    <a href="#sfm-section-<?php echo $tax_term->slug; ?>">

                        <?php echo $tax_term->name; // Group name (taxonomy) ?>
                        
                    </a>

                <?php endif;

            wp_reset_query();

        }

    }

    echo '</div>';

}

//CREATE TESTIMONIAL SHORTCODE + POST TYPE
function sfm_init() {
    add_shortcode('show_menu', 'sfm_function');
    add_shortcode('menu_links', 'sfm_links');

    register_post_type('simple_food_menu',
        array(  
            'public' => true, 
            'label' => 'Food Menu', 
            'supports' => array('title', 'editor'), 
            'menu_icon' => 'dashicons-carrot', 
            'rewrite' => array('slug' => 'food-menu', 'with_front' => false), 
            'has_archive' => false,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,

        )
    );

    register_taxonomy( 'simple_food_menu_categories', 

        array('simple_food_menu'),
        array('hierarchical' => true, 

            'labels' => array(
                'name' => __( 'Food Types', 'bonestheme' ),
                'singular_name' => __( 'Menu Item Category', 'bonestheme' ),
                'search_items' =>  __( 'Search Food Types', 'bonestheme' ),
                'all_items' => __( 'All Food Types', 'bonestheme' ),
                'parent_item' => __( 'Parent Food Types', 'bonestheme' ),
                'parent_item_colon' => __( 'Parent Food Type:', 'bonestheme' ),
                'edit_item' => __( 'Edit Food Type', 'bonestheme' ),
                'update_item' => __( 'Update Food Type', 'bonestheme' ),
                'add_new_item' => __( 'Add New Food Type', 'bonestheme' ),
                'new_item_name' => __( 'New Food Type', 'bonestheme' )
            ),

            'show_admin_column' => true, 
            'show_in_menu' => true,
            'menu_position' => 5,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' => 'menu-category' ),
            'publicly_queryable'  => false,
            'exclude_from_search' => true,

        )
    );

    /* metabox bidness */

    add_action( 'add_meta_boxes', 'cd_meta_box_add' );

    function cd_meta_box_add(){

        add_meta_box( 'my-meta-box-id', 'Item Cost', 'cd_meta_box_cb', 'simple_food_menu', 'normal', 'high' );

    }

    function cd_meta_box_cb($post){


        global $post;
        $values = get_post_custom( $post->ID );
        $text = isset( $values['my_meta_box_text'] ) ? esc_attr( $values['my_meta_box_text'][0] ) : '';
        wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );

        echo '<label for="my_meta_box_text">How much does this item cost?</label>';
        echo '<input type="text" name="my_meta_box_text" value="'.$text.'" id="my_meta_box_text" />';

    }


    add_action( 'save_post', 'cd_meta_box_save' );
    function cd_meta_box_save( $post_id ){
        // Bail if we're doing an auto save
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
         
        // if our nonce isn't there, or we can't verify it, bail
        if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'my_meta_box_nonce' ) ) return;
         
        // if our current user can't edit this post, bail
        if( !current_user_can( 'edit_post' ) ) return;
         
        // now we can actually save the data
        $allowed = array( 
            'a' => array( // on allow a tags
                'href' => array() // and those anchors can only have href attribute
            )
        );
         
        // Make sure your data is set before trying to save it
        if( isset( $_POST['my_meta_box_text'] ) )
            update_post_meta( $post_id, 'my_meta_box_text', wp_kses( $_POST['my_meta_box_text'], $allowed ) );
             
    }

    // administration page

    /*

    add_action( 'admin_menu', 'sfm_submenu' );

    function sfm_submenu() {
        add_submenu_page('edit.php?post_type=simple_food_menu', 'Custom Post Type Admin', 'Settings', 'edit_posts', basename(__FILE__), 'sfm_menu_options');
    }

    function sfm_menu_options() {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        echo '<div class="wrap">';
        echo '<h2>Here is where the form would go if I actually had options.</h2>';
        echo '<p>Lol dicks</p>';
        echo '</div>';
    }

    */



}

//ADD ALL THE ACTIONS
add_action('init', 'sfm_init');
add_action('wp_print_scripts', 'sfm_register_scripts');
add_action('wp_print_styles', 'sfm_register_styles');
?>