// https://yoursite.com/?run_bulk_category_assignment


function assign_parent_categories_bulk() {
    // Get all products
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1, // Fetch all products
        'fields' => 'ids', // Only get post IDs to save memory
    );

    $products = get_posts($args);

    foreach ($products as $product_id) {
        // Get the product categories
        $categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
        
        // Initialize an array to hold all categories
        $all_categories = array();

        // Loop through each category
        foreach ($categories as $category) {
            // Get the category object
            $cat = get_term($category, 'product_cat');

            // Add the current category to the array
            $all_categories[] = $cat->term_id;

            // Loop while there is a parent category
            while ($cat->parent != 0) {
                // Get the parent category
                $cat = get_term($cat->parent, 'product_cat');

                // Add the parent category to the array
                $all_categories[] = $cat->term_id;
            }
        }

        // Assign all categories (including parent categories) to the product
        wp_set_post_terms($product_id, array_unique($all_categories), 'product_cat');
    }
}

// Hook into WordPress init to run the function once
add_action('init', 'run_assign_parent_categories_bulk');

function run_assign_parent_categories_bulk() {
    if (isset($_GET['run_bulk_category_assignment']) && current_user_can('manage_options')) {
        assign_parent_categories_bulk();
        echo 'Parent categories have been assigned to all products.';
        exit;
    }
}
