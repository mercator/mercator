====== Unirgy_Giftcert ======

Link to extension page:

[[http://www.magentocommerce.com/extension/751/gift-certificates--virtual-cards]]

Check the screenshots for functionality overview.

===== WARNING =====

**Extensions marked as "Beta" are generally not recommended for production deployment.
Please test every version release on development copy. **

===== Installing without MagentoConnect =====

If you have problem with downloads using MagentoConnect, unpack this file in Magento root folder:

[[http://unirgy.com/download/?f=Unirgy_Giftcert-latest.zip]]

You will not receive automatic upgrades, but the latest version will always be available at this link.

**RSS feed for extension updates:**

[[http://unirgy.com/download/?f=Unirgy_Giftcert.feed]]

This feed is also used for update notifications in admin.

===== Special thanks for feature sponsorship! =====

  * [[http://shop.totaldiving.com]] - Life is too stressfull on your computer - go underwater for a while!

===== Dontation page =====

Per your requests, there's a donation link available now!
  * [[http://unirgy.com/donate.html]]

===== Functionality not reflected on screenshots: =====

  * Create GC product by choosing "Gift Certificate" product type during usual product creation.
  * Allows multiple GCs per order, uses coupon code field on shopping cart.
  * Balance check page, with PIN validation
  * Optional real-time preview of custom message

===== Known issues =====
  * Shipping cost is calculated not by recipient address (use multi-address checkout)

===== To use with custom interfaces =====
  - copy ''layout/ugiftcert.xml'' and ''template/unirgy/giftcert/'' into your interface and customize
  - The following steps are required for Magento 1.2.x and 1.3.x only:
    - edit ''template/sales/order/items.phtml'' and ''template/sales/order/print.phtml''
    - add before Grand Total TR tag: <code><?php echo $this->getChild('ugiftcert_total')->setColspan($colspan)->toHtml() ?></code>
    - edit ''template/email/order/items.phtml''
    - add before Grand Total TR tag: <code><?php echo $this->getChildHtml('ugiftcert_total') ?></code>

===== To reach balance check page, use one of the following =====
  * Link to URL: ''/ugiftcert/customer/balance''
  * Use this block in your CMS page: <code>{{block type="ugiftcert/balance" template="unirgy/giftcert/balance.phtml"}}</code>

===== Upcoming =====
==== Roadmap ====
  * Add purchased/used GCs grid to customer accounts in admin.
  * Add admin notifications for new GCs.
  * Add preset GC code/pin pool option in addition to random

===== Release notes =====
==== 0.9.15 ====
  * Compatibility with Magento CE 1.4.x

==== 0.9.12 ====
  * Fixed adding GC totals on customer order view and emails

==== 0.9.11 ====
  * Fixed compatibility with CE 1.4

==== 0.9.6 ====
  * Fixed DB upgrade for CE 1.4

==== 0.9.5 ====
  * Fixed adding GC products in admin created orders
  * Fixed double calculation of GCs in cart (courtesy of John)
  * Fixed duplicate certificate number bug (courtesy of Sofasurfer)
  * Enabled "weight" attribute for GC products to allow different shipping rates
  * Updates for Magento 1.3.2.x compatibility for some configurations
  * Added ja_JP translation (courtesy of rodrigo423)
  * Added pt_PT translation (courtesy of atonefer)
  * Added it_IT translation (courtesy of Roberto P.)
  * Added configuration for GC to always be a virtual product

==== 0.9.4 ====
  * Added {{var sender_name}} in email template to show full name of the sender
  * Changed default email template to include sender_name instead of sender_firstname
  * Added "product" variable in email, to allow GC product attributes, such as {{var product.getImageUrl()}}
  * Updates for Magento 1.3.x compatibility

==== 0.9.3 ====
  * Minor fix to allow compatibility with Magento 1.1.6+
  * Fixed activating and sending new GC email notifications on online payment methods
  * Fixed GC currency code for admin created orders
  * Added setting emailed virtual cards as shipped, allowing marking the virtual order as complete

==== 0.9.2 ====
  * Fixed Paypal Standard amount when GC is used.
  * Fixed refunding GC on order cancel (including PayPal Standard/Express edit order).
  * Fixed remembering GC codes in cart when just logged in customer's cart is merged.
  * Fixed invoicing orders with GC balance for correct amount.
  * Added use of GC in admin created orders.
  * Added GC store property for proper locale configuration per GC.
  * Added capability to send emails for GCs created in admin.

==== 0.9.1 ====
  * Added tax class attribute for GC products.
  * Added removal of GCs from cart.
  * Added collapsed/expanded view of GCs in cart totals.
  * Fixed showing GCs as negative in cart totals.
  * Removed ambiguous letters from GC number and PIN.

New theme files: IMPORTANT, COPY TO YOUR THEME:
  * ''template/unirgy/giftcert/checkout/total.phtml''

==== 0.9.0 ====
  * Added Magento 1.3.0 compatibility

==== 0.8.5 ====
  * Fixed handling non-US date formats for GC expiration dates
  * Fixed ugiftcert_amount_config attribute to be not required
  * Added configurable PIN format in email confirmations

==== 0.8.4 ====
This release focused mostly on handling GCs and multiple currencies.

  * Fixed unlimited message length
  * Fixed GC grid filter by status
  * Fixed getting GC amount configuration from product attribute in catalog product list
  * Fixed logic working with multiple currencies
  * Fixed error message when sending emails generated in admin
  * Added different GC amount configuration per currency
  * Added purchasing GC amount and using GC balance in customer's currency
  * Added automatically generated product attribute for amount configuration (add to your attribute sets manually)
  * Added new GC confirmation emails on payment completion (thanks to Vincent [vmaillot])
  * Added changing GC status to Active on payment completion

Changed theme files:
  * ''layout/ugiftcert.xml'' - show correct price on product list pages

==== 0.8.3 ====
  * Fixed JS Error (print is null) when sending by post is disabled
  * Fixed free amount entry (empty amount_config) configuration
  * Added sending GC emails from admin (GC grid massaction)

Updated theme files:
  * ''template/unirgy/giftcert/product/type.phtml''

==== 0.8.2 ====
  * Fixed printing invoices with GCs in admin sales orders grid
  * Fixed showing correct GC numbers to multiple recipients in the same order
  * Fixed incorrect virtual status of shopping cart
  * Fixed ignoring custom message text in new order email if none entered (please update your custom transactional emails)
  * Added send virtual card to self
  * Added configuration for custom message preview
  * Added frontend amount input configuration (range, dropdown, fixed, any amount)
  * Added showing minimal price in product list
  * Added "Expire On" default timespan
  * Added expiration date on balance check page and in email templates
  * Added showing GC total line in frontend > my account > order view, new order email, order print
  * Added product view javascript and simplified initial layout

Changed theme files:
  * ''template/unirgy/giftcert/product/type.phtml''
  * ''template/unirgy/giftcert/product/media.phtml''
  * ''template/unirgy/giftcert/balance.phtml''
  * ''layout/ugiftcert.xml''

New theme files:
  * (*) ''template/unirgy/giftcert/order/total.phtml''


==== 0.8.1.1 ====
  * Added de_DE email templates
  * Added fr_FR translation
  * Minor translation fixes
  * Extension update notifications switched to disabled by default

====0.8.1====
  * Fixed Varien_Date for 1.1.6
  * Fixed checking for duplicate GC code
  * Fixed deleting GC
  * Added slashes for translated strings in javascript
  * Added <code>{{var amount}}</code> to email template
  * Added configuration for PIN to be optional
  * Added balance check link in customer account dashboard
  * Added optional extension update notifications in admin

Updated files in theme:
  * ''template/unirgy/giftcert/product/type.phtml''
  * ''template/unirgy/giftcert/balance.phtml''
  * ''layout/ugiftcert.xml''

WARNING: Please do extensive testing before deployment.

====0.8.0.1====
  * Added de_DE translation (great work of Chris Wiech)

====0.8.0====
  * Added translation file

====0.7.9====
  * Added separate email template for GCs sent to the purchaser him/hersef
  * Added PINs info to emails

====0.7.8====
  * Clearing recipient info fields when hidden to avoid confusion

Updated theme file:
  * ''template/unirgy/giftcert/product/type.phtml''

====0.7.7====
  * Improved checking whether quote was fully paid by giftcert

====0.7.6====
  * Added hidden recipient info by default - click checkbox to show
  * Added GC amount range to configuration
  * Optional use of product attributes for GC amount range - see comments in ''template/unirgy/giftcert/product/type.phtml''

Updated theme file:
  * ''template/unirgy/giftcert/product/type.phtml''

====0.7.5====
  * Fixed balance check POST params processing
  * Added preventing of add to cart from product list
  * Added optional unlimited custom message length
  * Added optional recipient name, email, postal address
  * Added optional sending email notification to recipients on order completion
  * Added showing GC information and certificate numbers in order details in frontend and admin
  * Certificate numbers are shown to purchaser only if recipient wasn't specified
  * Added treating "email only" GCs as virtual items (no shipping required&#41;
  * Moved recipient message from history table to cert

Updated theme file:
  * ''layout/ugiftcert.xml''
  * ''template/unirgy/giftcert/balance.phtml''
  * ''template/unirgy/giftcert/product/type.phtml''

 NOTE: Always test on development copy!

====0.7.4====
  * Restored admin_user FK - the issue appears to be a local environment

====0.7.3====
  * Removed admin_user FK
  * Fixed stock management qty
  * Added export GCs to CSV
  * Added permission resource for GC admin grid
  * Added GC expiration date