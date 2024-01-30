# AMA Plugin

AMA Plugin is a WordPress plugin that implements all Airtel Africa API available on their Developer Portal. It provides functionalities to handle currency, token retrieval, KYC information retrieval, payment processing, transaction status checking, and transaction listing. The plugin also includes various admin pages for managing settings, viewing transaction details, and generating reports.

## Installation

1. Download the plugin ZIP file from the [GitHub repository](https://github.com/Dina-Rabe/Airtel-Money-Africa).
2. Extract the ZIP file.
3. Upload the extracted folder to the `wp-content/plugins/` directory of your WordPress installation.
4. Activate the plugin through the WordPress admin dashboard.

## Usage

The plugin includes shortcodes that can be used in WordPress pages or posts:

- `[ama_amount]` - To capture the amount that will be payed by the user. Ensure to keep it as numbers only.
- `[ama_product_code]` - To capture the product code related to the product or services that the user will purchase.
- `[ama_form]` - Displays a payment confirmation form and also on page where the user would be able to check their transaction status and download the payment proof accordingly.
- `[ama_fetch_transaction_status]` - Displays a form where user can fetch their transaction history.

Additionally, the plugin adds a menu item "AMA Settings" in the WordPress admin dashboard, which allows you to configure the plugin settings and view transaction details and reports.

## Configuration

To configure the AMA Plugin, follow these steps:

1. Go to "AMA Settings" in the WordPress admin dashboard.
2. Fill in the required fields:
   - Switch Mode: Select the mode (Test Mode or Live Mode) for the Airtel Africa API.
   - Client ID: Enter the client ID provided by Airtel Africa.
   - Client Secret: Enter the client secret provided by Airtel Africa.
   - Country: Enter the country for the API requests.
   - Currency: Enter the currency for the API requests.
   - Email: Enter the email address for notifications and reports.
3. Save the settings.

## Admin Pages

The plugin includes the following admin page:

- **Dashboard**: Displays an overview of transaction statistics and information. There is a section for transaction lookup as well.

## License

This plugin is licensed under the [MIT License](https://github.com/Dina-Rabe/Airtel-Money-Africa/blob/main/LICENSE).

## Author

AMA Plugin is developed by Dina Rabenarimanitra. You can find more information about the author on [LinkedIn](https://www.linkedin.com/in/dina-rabenarimanitra-91aa0261/).

## Support

For support or any inquiries, please contact the author through the [GitHub repository](https://github.com/Dina-Rabe/Airtel-Money-Africa). Or by mail directly to dina@allo-win.net