<?php
/**
 * Plugin Name: Book Search
 * Description: The Book Search Plugin is a WordPress plugin that allows users to search for books by title, author, publisher, rating, and price range, with a user-friendly search form and AJAX-powered search results.
 * Version: 1.1
 * Author: Hemant Jodhani
 */



 /**
 * BookSearchPlugin class
 */
class BookSearchPlugin {
    
     /**
     * Constructor function to initialize the plugin
     */
    public function __construct() {
        add_action('init', [$this, 'register_custom_post_type_and_taxonomies']);
        add_action('init', [$this, 'enqueue_styles']);
        add_action('add_meta_boxes', [$this, 'add_custom_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_box_data']);
        add_shortcode('book_search_form', [$this, 'book_search_shortcode']);
        add_action('pre_get_posts', [$this, 'filter_query']);
        add_action('wp_ajax_nopriv_book_search', [$this, 'ajax_book_search']);
        add_action('wp_ajax_book_search', [$this, 'ajax_book_search']);
        add_action('admin_notices', [$this, 'display_admin_notice']);
    }

    /**
     * Display admin notice to use shortcode
     */
    public function display_admin_notice() {
        ?>
        <div class="notice notice-info is-dismissible">
            <p><?php _e('Use this shortcode to use filters: [book_search_form]', 'textdomain'); ?></p>
        </div>
        <?php
    }

      /**
     * Register custom post type and taxonomies
     */
    public function register_custom_post_type_and_taxonomies() {
        $this->register_books_post_type();
        $this->register_author_taxonomy();
        $this->register_publisher_taxonomy();
    }

     /**
     * Register books post type
     */
    private function register_books_post_type() {
        $labels = array(
            'name'                  => _x('Books', 'Post type general name', 'textdomain'),
            'singular_name'         => _x('Book', 'Post type singular name', 'textdomain'),
            'menu_name'             => _x('Books', 'Admin Menu text', 'textdomain'),
            'name_admin_bar'        => _x('Book', 'Add New on Toolbar', 'textdomain'),
            'add_new'               => __('Add New', 'textdomain'),
            'add_new_item'          => __('Add New Book', 'textdomain'),
            'new_item'              => __('New Book', 'textdomain'),
            'edit_item'             => __('Edit Book', 'textdomain'),
            'view_item'             => __('View Book', 'textdomain'),
            'all_items'             => __('All Books', 'textdomain'),
            'search_items'          => __('Search Books', 'textdomain'),
            'parent_item_colon'     => __('Parent Books:', 'textdomain'),
            'not_found'             => __('No books found.', 'textdomain'),
            'not_found_in_trash'    => __('No books found in Trash.', 'textdomain'),
            'featured_image'        => _x('Book Cover Image', 'Overrides the “Featured Image” phrase for this post type.', 'textdomain'),
            'set_featured_image'    => _x('Set cover image', 'Overrides the “Set featured image” phrase for this post type.', 'textdomain'),
            'remove_featured_image' => _x('Remove cover image', 'Overrides the “Remove featured image” phrase for this post type.', 'textdomain'),
            'use_featured_image'    => _x('Use as cover image', 'Overrides the “Use as featured image” phrase for this post type.', 'textdomain'),
            'archives'              => _x('Book archives', 'The post type archive label used in nav menus.', 'textdomain'),
            'insert_into_item'      => _x('Insert into book', 'Overrides the “Insert into post” phrase.', 'textdomain'),
            'uploaded_to_this_item' => _x('Uploaded to this book', 'Overrides the “Uploaded to this post” phrase.', 'textdomain'),
            'filter_items_list'     => _x('Filter books list', 'Screen reader text for the filter links heading.', 'textdomain'),
            'items_list_navigation' => _x('Books list navigation', 'Screen reader text for the pagination heading.', 'textdomain'),
            'items_list'            => _x('Books list', 'Screen reader text for the items list heading.', 'textdomain'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'book'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
            'show_in_rest'       => true,
        );

        register_post_type('book', $args);
    }

    /**
     * Register author taxonomy
     * 
     * This function registers the 'author' taxonomy, which is non-hierarchical 
     * and associated with the 'book' post type.
     * 
     * @return void
     */
    private function register_author_taxonomy() {
        $labels = array(
            'name'              => _x('Authors', 'taxonomy general name', 'textdomain'),
            'singular_name'     => _x('Author', 'taxonomy singular name', 'textdomain'),
            'search_items'      => __('Search Authors', 'textdomain'),
            'all_items'         => __('All Authors', 'textdomain'),
            'parent_item'       => __('Parent Author', 'textdomain'),
            'parent_item_colon' => __('Parent Author:', 'textdomain'),
            'edit_item'         => __('Edit Author', 'textdomain'),
            'update_item'       => __('Update Author', 'textdomain'),
            'add_new_item'      => __('Add New Author', 'textdomain'),
            'new_item_name'     => __('New Author Name', 'textdomain'),
            'menu_name'         => __('Author', 'textdomain'),
        );

        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'author'),
            'show_in_rest'      => true,
        );

        register_taxonomy('author', array('book'), $args);
    }

    /**
     * Register publisher taxonomy
     * 
     * This function registers the 'publisher' taxonomy, which is non-hierarchical 
     * and associated with the 'book' post type.
     * 
     * @return void
     */
    private function register_publisher_taxonomy() {
        $labels = array(
            'name'              => _x('Publishers', 'taxonomy general name', 'textdomain'),
            'singular_name'     => _x('Publisher', 'taxonomy singular name', 'textdomain'),
            'search_items'      => __('Search Publishers', 'textdomain'),
            'all_items'         => __('All Publishers', 'textdomain'),
            'parent_item'       => __('Parent Publisher', 'textdomain'),
            'parent_item_colon' => __('Parent Publisher:', 'textdomain'),
            'edit_item'         => __('Edit Publisher', 'textdomain'),
            'update_item'       => __('Update Publisher', 'textdomain'),
            'add_new_item'      => __('Add New Publisher', 'textdomain'),
            'new_item_name'     => __('New Publisher Name', 'textdomain'),
            'menu_name'         => __('Publisher', 'textdomain'),
        );

        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'publisher'),
            'show_in_rest'      => true,
        );

        register_taxonomy('publisher', array('book'), $args);
    }

    /**
     * Add custom meta boxes
     * 
     * This function adds custom meta boxes for 'Book Rating' and 'Book Price' to the 'book' post type.
     * 
     * @return void
     */
    public function add_custom_meta_boxes() {
        add_meta_box(
            'book_rating',
            __('Book Rating', 'textdomain'),
            [$this, 'rating_meta_box_callback'],
            'book',
            'side',
            'high'
        );

        add_meta_box(
            'book_price',
            __('Book Price', 'textdomain'),
            [$this, 'price_meta_box_callback'],
            'book',
            'side',
            'high'
        );
    }

    /**
     * Rating meta box callback
     * 
     * This function renders the meta box for the book rating.
     * 
     * @param WP_Post $post The post object.
     * @return void
     */

    public function rating_meta_box_callback($post) {
        $value = get_post_meta($post->ID, '_book_rating', true);
        echo '<label for="book_rating_field">' . __('Rating (1 to 5):', 'textdomain') . '</label>';
        echo '<select name="book_rating_field" id="book_rating_field" class="postbox">';
        for ($i = 1; $i <= 5; $i++) {
            echo '<option value="' . $i . '"' . selected($value, $i, false) . '>' . $i . '</option>';
        }
        echo '</select>';
    }

     /**
     * Price meta box callback
     * 
     * This function renders the meta box for the book price.
     * 
     * @param WP_Post $post The post object.
     * @return void
     */
    public function price_meta_box_callback($post) {
        $value = get_post_meta($post->ID, '_book_price', true);
        echo '<label for="book_price_field">' . __('Price ($):', 'textdomain') . '</label>';
        echo '<input type="text" name="book_price_field" id="book_price_field" value="' . esc_attr($value) . '" class="postbox" />';
    }

     /**
     * Save meta box data
     * 
     * This function saves the meta box data for book rating and price when the post is saved.
     * 
     * @param int $post_id The ID of the post being saved.
     * @return void
     */
    public function save_meta_box_data($post_id) {
        if (!isset($_POST['book_rating_field']) || !isset($_POST['book_price_field'])) {
            return;
        }

        $rating = sanitize_text_field($_POST['book_rating_field']);
        update_post_meta($post_id, '_book_rating', $rating);

        $price = sanitize_text_field($_POST['book_price_field']);
        update_post_meta($post_id, '_book_price', $price);
    }


    /**
     * Book search shortcode
     * 
     * This function generates the book search form using a shortcode.
     * 
     * @return string The HTML for the book search form.
     */
    public function book_search_shortcode() {
        ob_start();
        ?>
        <form role="search" method="get" id="searchform">
            <div>
                <div class="ba-a--wrap">
                    <div>
                        <label for="s"><?php _e('Book Name:', 'textdomain'); ?></label>
                        <input class="wbc-input" type="text" value="<?php echo get_search_query(); ?>" name="s" id="s" />
                    </div>
                    
                    <div>
                        <label for="author"><?php _e('Author:', 'textdomain'); ?></label>
                        <?php
                        wp_dropdown_categories(array(
                            'show_option_all' => __('All Authors', 'textdomain'),
                            'taxonomy'        => 'author',
                            'name'            => 'author',
                            'value_field'     => 'slug',
                        ));
                        ?>    
                    </div>
                </div>
    
                <div class="ba-a--wrap">
                    <div>
                        <label for="publisher"><?php _e('Publisher:', 'textdomain'); ?></label>
                        <?php
                        wp_dropdown_categories(array(
                            'show_option_all' => __('All Publishers', 'textdomain'),
                            'taxonomy'        => 'publisher',
                            'name'            => 'publisher',
                            'value_field'     => 'slug',
                        ));
                        ?>    
                    </div>
                    <div>
                        <label for="price_range"><?php _e('Price ($):', 'textdomain'); ?></label>
                        <input type="range" name="price" id="price" min="0" max="1000" step="1" value="500" />
                    </div>
                </div>
                <div id="price_display">
                    <?php _e('Selected Price: ', 'textdomain'); ?><span id="price_value">500</span>
                </div>
    
                <label for="rating"><?php _e('Rating:', 'textdomain'); ?></label>
                <select class="wbc-input" name="rating" id="rating">
                    <option value=""><?php _e('All Ratings', 'textdomain'); ?></option>
                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
    
                <input type="hidden" name="post_type" value="book" /> <br>
                <input type="submit" class="wbc-btn" id="searchsubmit" value="<?php _e('Search', 'textdomain'); ?>" />
            </div>
    
            <div id="book-results"></div>

            <script>
                document.getElementById('price').addEventListener('input', function () {
                    document.getElementById('price_value').textContent = this.value;
                });
            </script>
            <?php
            return ob_get_clean();
        }
    
    /**
     * Filter query
     * 
     * This function filters the WordPress query to apply the search filters for the book search.
     * 
     * @param WP_Query $query The WP_Query instance (passed by reference).
     * @return void
     */    

        public function filter_query($query) {
            if ($query->is_search() && $query->get('post_type') == 'book' && !is_admin()) {
                $meta_query = [];
        
                if (!empty($_GET['rating'])) {
                    $meta_query[] = array(
                        'key'     => '_book_rating',
                        'value'   => sanitize_text_field($_GET['rating']),
                        'compare' => '=',
                    );
                }
        
                if (!empty($_GET['price'])) {
                    $meta_query[] = array(
                        'key'     => '_book_price',
                        'value'   => sanitize_text_field($_GET['price']),
                        'type'    => 'numeric',
                        'compare' => '=',
                    );
                }
        
                $query->set('meta_query', $meta_query);
        
                if (!empty($_GET['author'])) {
                    $query->set('tax_query', array(
                        array(
                            'taxonomy' => 'author',
                            'field'    => 'slug',
                            'terms'    => sanitize_text_field($_GET['author']),
                        ),
                    ));
                }
        
                if (!empty($_GET['publisher'])) {
                    $query->set('tax_query', array(
                        array(
                            'taxonomy' => 'publisher',
                            'field'    => 'slug',
                            'terms'    => sanitize_text_field($_GET['publisher']),
                        ),
                    ));
                }
            }
        }
        

    public function enqueue_styles() {
        wp_enqueue_style('book-search-style', plugins_url('/assets/style.css', __FILE__));

        wp_enqueue_script('book-search-script', plugins_url('/assets/book-search.js', __FILE__), array('jquery'), null, true);

        wp_localize_script('book-search-script', 'bookSearchAjax', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    }

    /**
     * AJAX book search
     * 
     * This function handles the AJAX request for searching books and returns the search results.
     * 
     * @return void
     */
    public function ajax_book_search() {
        $args = array(
            'post_type' => 'book',
            's' => sanitize_text_field($_GET['s']),
            'meta_query' => array(),
            'tax_query' => array(),
        );
    
        if (!empty($_GET['rating'])) {
            $args['meta_query'][] = array(
                'key' => '_book_rating',
                'value' => sanitize_text_field($_GET['rating']),
                'compare' => '=',
            );
        }
    
        if (!empty($_GET['price'])) {
            $args['meta_query'][] = array(
                'key' => '_book_price',
                'value' => sanitize_text_field($_GET['price']),
                'type' => 'numeric',
                'compare' => '<=',
            );
        }
    
        if (!empty($_GET['author'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'author',
                'field' => 'slug',
                'terms' => sanitize_text_field($_GET['author']),
            );
        }
    
        if (!empty($_GET['publisher'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'publisher',
                'field' => 'slug',
                'terms' => sanitize_text_field($_GET['publisher']),
            );
        }
    
        $query = new WP_Query($args);
    
        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                echo '<div class="book-item">';
                echo '<h2>' . get_the_title() . '</h2>';
                echo '<p>' . get_the_excerpt() . '</p>';
                echo '<p>' . __('Price: $', 'textdomain') . get_post_meta(get_the_ID(), '_book_price', true) . '</p>';
                echo '<p>' . __('Rating: ', 'textdomain') . get_post_meta(get_the_ID(), '_book_rating', true) . '</p>';
                echo '</div>';
            endwhile;
        } else {
            echo '<p>' . __('No books found', 'textdomain') . '</p>';
        }
    
        wp_reset_postdata();
        
        die();
    }
    
    
}

new BookSearchPlugin();
