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

//CREATE SHORTCODE (FOOTER)
function sfm_function($type='sfm_function') {

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
                //'category__in'      => $current_cat // Only posts in current category (category.php)
            );

            $my_query = null;

            $my_query = new WP_Query($args);

            if( $my_query->have_posts() ) : ?>

                <div class="menu">

                    <div class="menu-section">

                        <header class="clearfix">

                            <h2>
                                <?php echo $tax_term->name; // Group name (taxonomy) ?>
                            </h2>

                            <a class="toggle-item-list">Show Items</a>

                        </header>

                        <div class="menu-item-list">

                            <?php while ( $my_query->have_posts() ) : $my_query->the_post(); ?>

                                <div class="menu-item">

                                    <div class="menu-item-top cf">

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

                                    <div class="menu-item-desc">

                                        <?php the_content(); ?>

                                    </div>

                                </div>


                            <?php endwhile; // end of loop ?>

                        </div>

                    </div>

                </div>

            <?php endif; // if have_posts()

            wp_reset_query();

        } // end foreach #tax_terms

    } // end if tax_terms

}


//CREATE TESTIMONIAL SHORTCODE + POST TYPE
function sfm_init() {
    add_shortcode('show_menu', 'sfm_function');

    register_post_type('simple_food_menu',
        array(  
            'public' => true, 
            'label' => 'Food Menu', 
            'supports' => array('title', 'editor'), 
            'menu_icon' => 'dashicons-format-quote', 
            'rewrite' => array('slug' => 'food-menu', 'with_front' => false), 
            'has_archive' => false

        )
    );

    register_taxonomy( 'simple_food_menu_categories', 

        array('simple_food_menu'),
        array('hierarchical' => true, 

            'labels' => array(
                'name' => __( 'Menu Item Categories', 'bonestheme' ),
                'singular_name' => __( 'Menu Item Category', 'bonestheme' ),
                'search_items' =>  __( 'Search Menu Item Categories', 'bonestheme' ),
                'all_items' => __( 'All Menu Item Categories', 'bonestheme' ),
                'parent_item' => __( 'Parent Menu Item Category', 'bonestheme' ),
                'parent_item_colon' => __( 'Parent Menu Item Category:', 'bonestheme' ),
                'edit_item' => __( 'Edit Menu Item Category', 'bonestheme' ),
                'update_item' => __( 'Update Menu Item Category', 'bonestheme' ),
                'add_new_item' => __( 'Add New Menu Item Category', 'bonestheme' ),
                'new_item_name' => __( 'New Custom Menu Item Name', 'bonestheme' )
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
        $text = isset( $values['my_meta_box_text'] ) ? esc_attr( $values['my_meta_box_text'][0] ) : ”;
        wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );

        echo '<label for="my_meta_box_text">How much does this item cost?</label>';
        echo '<input type="text" name="my_meta_box_text" id="my_meta_box_text" />';

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





}

//ADD ALL THE ACTIONS
add_action('init', 'sfm_init');
add_action('wp_print_scripts', 'sfm_register_scripts');
add_action('wp_print_styles', 'sfm_register_styles');
?>