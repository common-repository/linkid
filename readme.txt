=== linkID ===
Contributors: linkID
Tags: login, payment
Requires at least: 3.5
Tested up to: 4.3
Stable tag: 0.1.2
License: BSD 3-Clause license (see LICENSE.txt in the root folder of the plugin)

== Description ==

The [linkID](http://service.linkid.be) Wordpress plugin provides the following functionality:

* the plugin adds linkID login and registration functionality to your Wordpress site. If Woocommerce is installed, the linkID plugin can also be used for customer login and registrations inside your Woocommerce store.
* it adds a linkID payment gateway to your Woocommerce store (if Woocommerce is installed), allowing your customers to pay for their orders using linkID.

== Installation ==

1. The plugin can be installed through the dashboard by searching for "linkID" on the plugin page and clicking "install now", or it can be installed manually by unzipping the plugin archive and copying its contents to the wp-content/plugins/linkid folder on your Wordpress install.
2. Configure the plugin using the steps below.

= General configuration =

1. Go to the linkID setting page in your Wordpress Dashboard by going to Settings > linkID.
2. In the general settings section of the page, enter the credentials of your linkID application. If you do not have a linkID application yet, please contact sales@lin-k.net for more information and to get started.

= linkID Login configuration =

1. The linkID login settings section allows you to either enable or disable the linkID login functionality.

= linkID payment configuration =

The linkID payment configuration only shows up when WooCommerce is installed. The linkID payment gateway configuration
is done through the Woocommerce payment gateway settings interface.

1. Open the Woocommerce payment gateway settings: Woocommerce > Settings > Checkout > Payment Gateways > linkID Settings.
2. Enter a title and a description of the payment gateway.
3. Choose whether or not you want a logo to show up and choose its background color.

*Important*: Permalinks should be enabled on your Wordpress site in order for the payment gateway to work properly!

== Frequently Asked Questions ==

== Screenshots ==

1. When linkID login is enabled, the linkID login button is added automatically to the Wordpress login form.
2. When linkID login is enabled, the linkID login button is added automatically to the WooCommerce login form on the "My account" page (provided Woocommerce is installed).
3. When linkID login is enabled, the linkID login button is added automatically to the WooCommerce login form on the "Checkout" page (provided Woocommerce is installed).
4. When clicked, the linkID plugin shows a QR code which is then scanned by the customer allowing them to log in.
5. The linkID plugin can be used as payment gateway inside your Woocommerce store.
6. The customer is redirected to the payment page when linkID is selected as payment method. The payment page shows a QR code your customers can scan using the linkID app on their smartphone, allowing them to pay for their order.

== Changelog ==

= 0.1.1 =
* Small styling improvement

= 0.1 =
* Initial plugin version. Plugin provides support for logging in to Wordpress using linkID. The plugin also functions
as a payment gateway if WooCommerce is installed.

== Upgrade Notice ==