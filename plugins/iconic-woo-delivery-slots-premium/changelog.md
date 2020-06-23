**v1.10.0** (15 Jun 2020)  
[new] 'Maximum Orders' setting for ASAP delivery  
[update] Add `iconic_wds_delivery_days_max_orders` filter  
[update] Compatiblity with `WooCommerce PDF Invoices & Packing Slips` plugin  
[fix] Fix maximum orders for 'ASAP' slot  
[fix] Prevent over-reserving slots  
[fix] Fix reservation table not showing all slots  
[fix] Ensure timestamps are checking the same timezone  
[fix] Sort timeslots in reservation calendar  
[fix] Enhance validation of date fields at checkout  
[fix] Further logic to prevent double-booking when order is placed at the same time  
[fix] Improved check for `iconic_wds_is_same_day_allowed` filter  
[fix] Improved check for `iconic_wds_is_next_day_allowed` filter  

**v1.9.2** (20 May 2020)  
[new] Change labels between delivery/collection globally and per shipping method via the settings  
[update] Update dependencies  
[update] Added new filter `iconic_wds_timeslot_shipping_method_allowed`  
[update] Remove redundant status transitions method  
[fix] Prevent simultaneous double-booking  
[fix] Slots overbooked returning false positive when minus numbers  
[fix] Max orders for day not visible in admin  
[fix] Datepicker short day label localization issue  
[fix] Fix warning when chosen_method is null  
[fix] Fix nested ternary operator warning  
[fix] Fix undefined function `determine_locale` error  
[fix] Ensure shipping methods are cached  

**v1.9.1** (6 May 2020)  
[update] Make time field type in admin "text"  
[update] Set slot duration AND slot frequency for dynamic time slots  
[fix] Unsupported opperand  

**v1.9.0** (5 May 2020)  
[new] New datepicker styling - none, light, and dark  
[new] Ability to create slots dynamically  
[new] Set maximum orders per day  
[update] Improve method of toggling the date/time fields based on shipping method  
[update] Updated Dutch translations  
[update] Chronological order for delivery months in admin  
[update] Admin: Use shipping method title instead of type  
[update] Update dependencies  
[update] Reduced queries when counting slots available  
[update] Change default position of date/time fields at checkout  
[update] Add parameters to iconic_wds_available_dates filter  
[update] Update POT file  
[fix] Ensure correct shipping method is loaded on first change  
[fix] Correctly check translated days/months in datepicker  
[fix] Ensure orders can't be placed while delivery date/time is loading  
[fix] Locale issue in AJAX calls  
[fix] Prevent loading time slots too often at checkout  
[fix] Prevent time slots from loading multiple times if the date and shipping method are the same  
[fix] Disable the place order button if the date fields are still loading  
[fix] Fix infinite load on reservation table when no settings saved  
[fix] Undefined name/email for guest reserved slots in admin  

**v1.8.2** (23 Apr 2020)  
[fix] First available date not matching  

**v1.8.1** (22 Apr 2020)  
[update] Add setting to show/hide unavailable dates in the reservation table  
[update] Updated dependencies  
[fix] Only one reservation was allowed at a time  
[fix] Match available dates in lowercase to prevent mismatches (firefox/ie)  

**v1.8.0** (21 Apr 2020)  
[new] Compatibility with CheckoutWC  
[new] [iconic-wds-get-order-date] shortcode  
[new] [iconic-wds-get-order-time] shortcode  
[new] [iconic-wds-get-order-date-time] shortcode  
[update] Update dependencies  
[fix] Fix `iconic_wds_max_delivery_date` filter  
[fix] Ensure chosen shipping method is cast as a string to prevent warning  
[fix] Make "All delivery dates" translatable in admin  
[fix] Fix CSS priority  

**v1.7.18** (18 Mar 2020)  
[update] Version compatibility  

**v1.7.17** (18 Dec 2019)  
[new] Add field to filter orders by delivery date  
[new] Add `All/Any` condition for Product and Category Exclude settings  
[update] Add `iconic_wds_get_cutoff` filter  
[update] Allow time slots to start and end at the same time. They'll be display as a single time instead of a range  
[update] Optimise is_timeslot_available_on_day()  
[update] Load reservation calender slots via AJAX to make compatibile with FastCGI and Redis cache  
[update] Add `iconic_wds_slots_available_on_date` filter  
[update] Update dependencies  
[update] Update POT file  
[fix] Fix instance of 'iconic_wds_next_day_date' filter  
[fix] Fix 'iconic_wds_allowed_days' filter  
[fix] Issue fetching meta when viewing reserved slots  

**v1.7.16** (1 July 2019)  
[fix] Freemius Fix  

**v1.7.15** (2 Mar 2019)  
[fix] Security Fix  
[fix] Headers already sent notice  

**v1.7.14** (6 Dec 2018)  
[update] Compatibility with WP 5.0  
[update] Compatibility with Woo 3.5.2  
[update] Update dependencies  
[fix] Ensure fee is properly calculated when using date field only  
[fix] Restrict by category was not applied to variations  
[fix] Prevent infinite loop when there are no delivery days enabled  
[fix] Ensure date field is not translatable by Google Translate  

**v1.7.13** (26 Oct 2018)  
[new] Ability to add fees to days of the week  
[update] Ensured compatibility with Woo 3.5.0  
[update] add_filter for get_reservations  
[update] Check WC is active before running unnecessary code  
[update] POT updated  
[fix] Remove shipping method watcher to fix delivery slot fields toggle  
[fix] Ensure settings page permissions work and allow them to be filtered  
[fix] When WC is deactivated the settings file tries to use a WC function causing a fatal error  
[fix] Fix conflict with bootstrap-date plugin  

**v1.7.12** (18 Sep 2018)  
[update] add_filter for is_timeslot_available_on_day  
[fix] Calendar opens on wrong month when using mm/dd/yy format  
[fix] Ensure all dates use the correct formatting  
[fix] Fix issue when plugin loads via CLI/Cron  

**v1.7.11** (11 Sep 2018)  
[new] Add WooCommerce Table Rate Shipping by WooCommerce compatibility  
[update] Same day/next day key on deliveries page  
[update] Allow "next day" to be "next allowed delivery day"  
[update] Start calendar on first available date  
[update] Hide time slot col in deliveries tab if not enabled  
[update] Implement Iconic core classes  
[update] Always display field descriptions if enabled  
[fix] API data was not being added  
[fix] Validate required time slot field at checkout  
[fix] Fix available delivery days when min/max method is all days  
[fix] Time slot required when not enabled  
[fix] Update reservation correctly when final order date is different  
[fix] All dates were disabled in the order details page  
[fix] Infinite loop caused when timeslots or reservations are not returned by the ajax request get_slots_on_date  
[fix] Sometimes the calendar opens the wrong month when the value is empty  
[fix] When we change shipping method refresh timeslots faster to prevent customers from submitting the checkout form with wrong timeslot

**v1.7.10** (6 July 2018)  
[new] Add ASAP delivery same day cut off  
[new] Add ASAP delivery fee  
[new] Add same/next day fees to reservation table  
[change] Cache shipping method options  
[change] Add script debugging  
[change] Remove unnecessary update_checkout trigger  
[change] Change all hooks to use `iconic_wds_` prefix  
[change] Add `$order` object to some text filters  
[update] Update Freemius  
[fix] Use date format from settings when checking for fees  
[fix] Ensure fees in reservation table use `float` not `int`  
[fix] Add ASAP fee at checkout in dropdown  

**v1.7.9** (12 Feb 2018)  
[update] French translation files  
[update] German translation files  
[update] Update Freemius  
[update] Update settings framework  
[update] Ability to disable delivery slots if product from specific category is in the cart  
[update] Ability to disable delivery slots if a specific product is in the cart  
[update] Remove Envato checks  
[update] Add ASAP delivery time slot  
[update] Update POT file  
[fix] PHP Error for < 5.5 "Can't use function return value in write context"  
[fix] Issue with stripe validation when fields are hidden  
[fix] Cast shipping method to string for "WooCommerce Advanced Shipping" compatibility  

**v1.7.8** (19/12/2017)  
[update] Add delivery date meta to legacy API request  
[update] Change field validation method at checkout  
[update] Updated pot file  
[update] Disable time slots while loading  
[fix] Make sure fields are validated by Stripe Gateway  
[fix] Prevent current date being selected on field reset  
[fix] Incorrect name for DE .po file  
[fix] Blank page when creating a new order in the admin

**v1.7.7** (07/11/2017)  
[update] Allow order delivery date to be modified by admin  
[update] Trigger datepicker onSelect on checkout load  
[update] Validate shipping method settings  
[update] Add fees for same day/next day deliveries  
[update] Freemius  
[update] add_filter for date and time display  
[update] Add Flexible Shipping for WooCommerce compatibility  
[update] Add \[iconic-wds-next-delivery-date\] shortcode  
[update] Allow slot lockout to be "0"  
[update] Add some validation for min/max selectable date settings  
[update] Update POT file  
[fix] Remove reserved slot when order is cancelled  
[fix] Prevent dodgy characters in calendar  
[fix] Get correct timestamp for removing outdated reservations  
[fix] Make sure hidden field value is populated correctly at checkout  
[fix] Issue with deleting expired reservations  
[fix] Issue with duplicated slots at checkout

**v1.7.6** (07/07/2017)  
[update] Reselect timeslot on order details refresh

**v1.7.5** (07/07/2017)  
[update] Implement new licence system  
[update] Add compatibility for BE cart based shipping  
[update] Add compatibility for Distance rate Shipping by WPShowCase  
[fix] Cancelled deliveries showing as dashes on deliveries admin page

**v1.7.4** (02/04/2017)  
[update] Compatibility with WooCommerce 3.0.0  
[update] Compatibility with WooCommerce Advanced Free Shipping  
[update] Add delivery data to the API response  
[update] Moved ajax functions to their own class  
[fix] Use WordPress date format in deliveries tab

**v1.7.3** (22/12/2016)  
[update] Strip out old code for postcode functionality  
[update] Settings framework  
[update] Envato market updater  
[update] Update minimum selectable date logic to account for current day if it is non-deliverable  
[update] Add filters for min/max delivery date  
[update] Hebrew translation (thanks Guy)  
[update] Add filters to text strings  
[fix] Remove data-icon CSS  
[fix] Option to calculate tax on timeslot fee  
[fix] Fix German language files and update

**v1.7.2** (28/07/2016)  
[update] Add "Allow Bookings up to X Minutes Before Slot" to each timeslot. Overrides default.  
[update] Delete reservation when order is cancelled or deleted  
[update] Compatibility with "Table Rate Shipping Plus" by "mangohour"  
[update] Reduce database interactions for slot lookup  
[update] Update settings framework   
[fix] Add new parameter to email_order_delivery_details  
[update] Add new actions/filters to the checkout fields template

**v1.7.1** (07/07/2016)  
[fix] Compatibility with latest Multi Step Checkout plugin  
[update] Compatibility with latest "Table Rate Shipping" plugin  
[fix] Compatibility with latest "WooCommerce Advanced Shipping" plugin

**v1.7.0** (27/06/2016)  
[update] Compatibility with new Shipping Zones  
[update] New time slot conditional - show slots for specific shipping zones only  
[update] Selectable dates will change based on selected shipping method  
[update] Allow holidays to be entered as a range of days  
[update] Set calendar to open on first available date

**v1.6.3** (16/06/2016)  
[fix] Issue with far out timezones and same/next day deliveries  
[update] Settings framework  
[update] Set calendar to reflect last day of the week setting  
[update] Move same day / next day cut off to date tab, instead of time slot tab  
[fix] Issue where date only wasn't working if no time slots were present

**v1.6.2** (17/05/2016)  
[fix] Allowed days not setting correctly  
[update] Restrict dates to current week  
[update] Allow admin orders to be sorted by delivery date (new orders only)  
[fix] Allow bookings up to x minutes before slot was only accounting for the current day

**v1.6.1** (22/04/2016)  
[update] Add WooShip compatibility  
[fix] Issue when using wpcli  
[fix] Not working on multisite  
[update] Add option to format reservation table date heading  
[fix] Add class if fields are disabled on load to make the initial check more accurate

**v1.6.0** (19/04/2016)  
[fix] Sometimes an issue loading datepicker at checkout - changed to $(window).load();  
[update] Update to new settings framework

**v1.5.10** (14/01/2016)  
[fix] Allow more than 66 for max date  
[fix] Trigger change event is timeslots are not in use

**v1.5.9** (08/12/2015)  
[fix] Remove nonce check on ajax methods to avoid cache issues  
[update] Trigger select change when loading new slots  
[update] Add version to enqueued scripts

**v1.5.8** (08/12/2015)  
[fix] Optimise get_timeslot_data as it was slowing down with a lot of timeslots  
[fix] Only select reservation if it's available in checkout dropdown  
[fix] min/Max were ignoring timezone  
[fix] Remove forward slash on some includes  
[update] Multi Step Checkout compatibility

**v1.5.7** (25/11/2015)  
[fix] Sunday being ignored as allowed day

**v1.5.6** (25/11/2015)  
[fix] Email order meta, and better styling  
[update] Remove : from time slot fee text  
[fix] Orders with 'date only' now show in deliveries tab  
[fix] Issue if user places delivery for slot they've already used, unlikely, but avoided the issue just in case  
[fix] Trashed orders were showing in the deliveries tab  
[fix] Issue where you could proceed through checkout if slots hadn't finished loading  
[update] Add validation to "Allow Bookings up to..." field  
[update] Min/Max selectable date methods - now you can choose from allowed days, weekdays, or all days  
[update] Disable same day/next day if current time is after (x)  
[update] Change wording to time slot instead of timeslot in some strings  
[update] POT file

**v1.5.5** (23/11/2015)  
[fix] Current day not showing in upcoming deliveries tab  
[fix] Missing text domain on one string

**v1.5.4** (13/11/2015)  
[fix] Lock time slot on current day if passed

**v1.5.3** (11/11/2015)  
[update] Reservation table - group timeslots with the same time so you can have different prices on different days  
[update] Dutch translation  
[fix] Holidays not working if not in English

**v1.5.2** (03/11/2015)  
[update] Add "any" shipping method option, to always display fields  
[update] Use billing postcode for lookup if shipping is disabled or missing  
[fix] Fix timeslot display if logged out on checkout  
[update] German translation

**v1.5.1** (26/10/2015)  
[fix] Change "add fee" priority so it works with storefront and other themes  
[fix] Checkout fields when only one shipping method, or no shipping method

**v1.5.0** (25/10/2015)  
[update] Fee per timeslot  
[fix] Fixed reservation table  
[update] Reservations can now be made by logged out users (Note: ID format changed, so may not work well with existing reservations)  
[fix] Show/hide for radio or select options  
[fix] Use WordPress time functions so if the timezone is UTC it does not cause any issues  
[update] Add delivery details column to admin order listing  
[update] New icons  
[update] Compatibility with WooCommerce Advanced Shipping  
[update] Compatibility with WooCommerce Table Rate Shipping  
[update] Min/max bookable dates account for allowed days only now  
[update] Allow date and time to be moved in checkout  
[fix] Email encoding

**v1.4.0** (10/10/2015)  
[update] Show/hide based on shipping method  
[update] Refactor and tidy javascript

**v1.3.0** (10/07/15)  
[update] Postcode Restrictions (See "Postcode Restrictions" https://jamesckemp.ticksy.com/article/4560/) special thanks to dullejohn  
[update] Added da_DK translation special thanks to dullejohn  
[update] Translation files updated

**v1.2.3** (10/07/15)  
[update] French - Updated translation

**v1.2.2** (07/07/15)  
[update] Portuguese - Brazil translation

**v1.2.1** (06/07/15)  
[fix] Missing languages folder  
[update] New translations available

**v1.2.0** (26/06/15)  
[update] New po file for translations  
[update] More strings available to translate  
[update] esc_attr  
[update] z-index for datepicker  
[fix] in_array notice  
[update] Moved labels to translatable strings for convenience  
[fix] Remove text domain as variable  
[update] Check PHP version  
[update] Added some settings validation to prevent common issues  
[update] Disable timeslot field while loading  
[update] hide timeslot field until date is chosen

**v1.1.1** (10/06/15)  
[Update] Allow HTTPS

**v1.1.0** (11/05/15)  
[Fix] Allow shop managers to save options  
[Update] Convert to SCSS - Dev only  
[Update] Move dynamic styles to head tag for speed  
[Update] Add note about where to view themes for datepicker

**v1.0.9** (23/02/15)  
[Fix] Change error output function to fix checkout issue

**v1.0.8** (11/11/14)  
[fix] Change indexOf method so it works in IE8  
[fix] Add check for WooCommerce so no errors when updating  
[fix] Validation of fields at checkout

**v1.0.7** (28/10/14)  
[Update] Change date field to be read only

**v1.0.6** (27/10/14)  
[Fix] Delivery settings page permissions  
[Fix] Delivery times chosen at checkout will now appear on the Deliveries tab

**v1.0.5** (08/08/14)  
[Update] Only use ui styles on checkout page  
[Fix] Fixed timezone issue. Make sure this is set in WP Settings to a string.

**v1.0.4** (07/08/14)  
[Fix] Fixed checkbox issue not saving certain days in slots

**v1.0.3** (14/07/14)  
[Update] Added "time blocking". if the time has passed for the current day, the slot becomes unavailable.  
[Update] Added the ability to set slots to apply for specific days only.  
[Update] Added "Allow Bookings Up To (x) Minutes Before Slot" functionality.  
[Update] Updated table shortcode to allow logged out users to see how many slots are remaining for each timeslot.  
[Fix] Updated Table shortcode to prevent border glitch when loading icon is displayed.

**v1.0.2** (29/06/14)  
[Update] Added PO files for translation

**v1.0.1** (06/05/14)  
[Update] Time format option  
[Update] Upcoming Deliveries page  
[Fix] Order meta labels in customer emails  
[Update] Added trigger to body after timeslots are loaded in checkout  
[Update] Added triggers on body to reservation table after remove and add

**v1.0.0** (29/03/14)  
Initial Release