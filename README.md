# WooCommerce Dynamic Length-Based Pricing

## Overview
This plugin customizes WooCommerce to enable dynamic length-based pricing for products. It allows customers to specify a custom length (in feet), which affects the total price based on a configurable per-foot price. Additionally, for variable products, the price per foot can be set per variation.

## Features
- Adds a custom "Length in feet" field to eligible products.
- Dynamically calculates the product price based on the specified length and per-foot pricing.
- Supports simple and variable products.
- Allows variations to have unique per-foot pricing.
- Updates the price dynamically on the product page when a variation is selected.
- Adjusts the price in the cart and checkout pages based on the selected length.
- Displays "Starting at" pricing on shop and related product pages for eligible products.

## Installation
### 1. Upload the Plugin Files
1. Download or clone this repository.
2. Upload the plugin files to your WordPress installation:
   - Place the PHP functions in your theme's `functions.php` file **or**
   - Create a custom plugin and include the script.

### 2. Enable the Plugin (if using a custom plugin method)
1. Navigate to `Plugins > Add New` in your WordPress dashboard.
2. Upload and activate the custom plugin containing these functions.

## Usage
### Setting Up a Product
1. Edit or create a WooCommerce product.
2. Add a custom meta field `_handrail_price_increment` with the desired per-foot price.
3. If the product is variable, set `_handrail_price_increment` on each variation to enable different pricing per variation.

### How It Works
- The "Length in feet" field appears on product pages when `_handrail_price_increment` is set.
- When the user enters a length, the price updates dynamically.
- For variable products, selecting a variation updates the per-foot price.
- The cart and checkout display the total price based on the selected length.
- Shop and related product pages display "Starting at {per-foot price}".

## Customization
To adjust functionality:
- Modify `woocommerce_get_price_html` filter to change how prices display on shop pages.
- Adjust validation rules in `validate_custom_length_field` if needed.
- Update the JavaScript in `custom_price_update_script` to modify price behavior.

## Contributing
Contributions are welcome! To contribute:
1. Fork this repository.
2. Create a new feature branch (`git checkout -b feature-name`).
3. Commit your changes (`git commit -m 'Add new feature'`).
4. Push to the branch (`git push origin feature-name`).
5. Open a pull request.

## Issues & Support
If you encounter any issues or have feature requests, please open an issue in this repository.

## License
This project is open-source and licensed under the MIT License.

