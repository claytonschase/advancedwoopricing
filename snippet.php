/**
 * 1. Display the custom "Length in feet" field on the product page
 *    for all products that have a valid _handrail_price_increment meta field.
 */
function add_custom_length_field() {
    global $product;

    // Only run on single product pages.
    if ( ! is_product() || ! $product instanceof WC_Product ) {
        return;
    }
    
    // Retrieve the custom per‑foot price from product meta.
    $price_increment = get_post_meta( $product->get_id(), '_handrail_price_increment', true );
    
    // If the meta field is empty or not numeric, do not output the custom field.
    if ( empty( $price_increment ) || ! is_numeric( $price_increment ) ) {
        return;
    }
    
    echo '<div class="custom-length-field">';
        echo '<label for="custom_length">' . esc_html__( 'Length in feet:', 'woocommerce' ) . '</label>';
        echo '<input type="number" id="custom_length" name="custom_length" min="1" max="20" step="1" value="1" required>';
        echo '<p class="custom-length-description">We will determine the number of brackets you will need to mount your handrail. Handrails over 8\' will be spliced. Max length is 20 feet.</p>';
    echo '</div>';
    
    // For variable products, get the minimum variation price for consistency.
    if ( $product->is_type( 'variable' ) ) {
        $base_price = $product->get_variation_price( 'min', true );
    } else {
        $base_price = $product->get_price();
    }
    echo '<input type="hidden" id="base_product_price" data-base-price="' . esc_attr( floatval( $base_price ) ) . '">';
    
    // Output a hidden field to store the custom per‑foot price increment.
    echo '<input type="hidden" id="price_increment" data-price-increment="' . esc_attr( $price_increment ) . '">';
}
add_action( 'woocommerce_before_add_to_cart_button', 'add_custom_length_field' );

/**
 * 2. Validate the custom length field input.
 */
function validate_custom_length_field( $passed, $product_id, $quantity ) {
    $price_increment = get_post_meta( $product_id, '_handrail_price_increment', true );
    if ( $price_increment === '' || ! is_numeric( $price_increment ) ) {
        return $passed;
    }
    
    if ( empty( $_POST['custom_length'] ) ) {
        wc_add_notice( __( 'Please enter a length in feet.', 'woocommerce' ), 'error' );
        return false;
    }
    
    $custom_length = $_POST['custom_length'];
    if ( ! ctype_digit( $custom_length ) || $custom_length < 1 || $custom_length > 20 ) {
        wc_add_notice( __( 'Please enter a whole number between 1 and 20 for the length in feet.', 'woocommerce' ), 'error' );
        return false;
    }
    
    return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'validate_custom_length_field', 10, 3 );

/**
 * 3. Save the custom field value to the cart item data.
 */
function save_custom_length_field( $cart_item_data, $product_id ) {
    if ( isset( $_POST['custom_length'] ) ) {
        $custom_length = absint( $_POST['custom_length'] );
        $price_increment = get_post_meta( $product_id, '_handrail_price_increment', true );
        
        // Instead of using empty(), check if the meta value is exactly an empty string.
        if ( $price_increment === '' || ! is_numeric( $price_increment ) ) {
            return $cart_item_data; // Do NOT store a custom price, leave WooCommerce default price.
        }
        
        $new_price = floatval( $price_increment ) * $custom_length;
        
        $cart_item_data['custom_length'] = $custom_length;
        $cart_item_data['custom_price'] = $new_price;
    }
    return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'save_custom_length_field', 10, 2 );

/**
 * 4. Adjust the product price in the cart.
 */
function add_custom_length_price_adjustment( $cart_object ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }
    
    foreach ( $cart_object->get_cart() as $cart_item_key => $cart_item ) {
        // If a custom price is set, apply it. Otherwise, keep the default price.
        if ( isset( $cart_item['custom_price'] ) && is_numeric( $cart_item['custom_price'] ) ) {
            $cart_item['data']->set_price( floatval( $cart_item['custom_price'] ) );
        }
    }
}
add_action( 'woocommerce_before_calculate_totals', 'add_custom_length_price_adjustment', 20 );

/**
 * 5. JavaScript to update the price dynamically on the product page.
 */
function custom_price_update_script() {
    // Only run this script on single product pages.
    if ( ! is_product() ) {
        return;
    }
    
    global $product;
    
    // Retrieve the per‑foot price from product meta.
    $price_increment = get_post_meta( $product->get_id(), '_handrail_price_increment', true );
    
    // If no price increment is set, do NOT modify the price.
    if ( empty( $price_increment ) || ! is_numeric( $price_increment ) ) {
        return;
    }
    
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($){
        function updatePrice(){
            var length = parseInt($('#custom_length').val());
            if ( isNaN(length) || length < 1 ) {
                length = 1;
            }
            if(length > 20){
                length = 20;
                $('#custom_length').val(20);
            }
            var price_increment = parseFloat($('#price_increment').data('price-increment'));
            if ( isNaN(price_increment) ) {
                price_increment = 0;
            }
            var new_price = price_increment * length;
            $('.summary .price').text(new Intl.NumberFormat('en-US', { style: 'currency', currency: '<?php echo get_woocommerce_currency(); ?>' }).format(new_price));
        }
        updatePrice();
        $('#custom_length').on('input change', updatePrice);
    });
    </script>
    <?php
}
add_action('wp_footer', 'custom_price_update_script');

/**
 * 9. Modify the price display on shop pages and related products.
 *
 * If the product has a valid _handrail_price_increment meta field,
 * this filter will change the default price output to display:
 * "Starting at {price}".
 */
function modify_product_price_html( $price, $product ) {
    // Retrieve the per‑foot price from product meta.
    $price_increment = get_post_meta( $product->get_id(), '_handrail_price_increment', true );
    
    // If the meta field is empty or not numeric, do not modify the price.
    if ( $price_increment === '' || ! is_numeric( $price_increment ) ) {
        return $price;
    }
    
    // Optionally, avoid modifying the price on single product pages.
    if ( is_singular( 'product' ) ) {
        return $price;
    }
    
    // Format the price increment as currency.
    $formatted_price = wc_price( floatval( $price_increment ) );
    
    return 'Starting at ' . $formatted_price;
}
add_filter( 'woocommerce_get_price_html', 'modify_product_price_html', 10, 2 );
