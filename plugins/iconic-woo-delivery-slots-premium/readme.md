## Documentation

For full documentation, please visit http://docs.iconicwp.com/category/29-delivery-slots

## Installation

To install the plugin:

1. Open wp-admin and navigate to `Plugins > Add New > Upload`.
2. Click Choose File, and choose the file `jck_woo_deliveryslots.zip` that you downloaded earlier. This is *inside* the zip you downloaded from CodeCanyon.
3. Once uploaded, click activate plugin.
4. The plugin is now installed and activated.

## Configuration

Configuring the Delivery Slots plugin couldn’t be easier.

Navigate to `WooCommerce > Delivery Slots`. Here you will find a variety of Options, namely General Settings, Date Settings, Time Settings, Holidays, and Reservation Table.

### General Settings

* **Checkout Fields Position**  
  Where would you like the date and time slot picker to apepar during checkout.
* **Checkout Fields Position Priority**  
  Move the date and time picker fields above/below other fields at the same position. Lower numbers (1) are higer up the page, higher numbers (60) are further down the page.
* **Shipping Methods**  
  Enable delivery slots *only* for the checked methods. To enable for all methods, just select `Any Method`.

### Date Settings

#### Date Setup

These options relate specifically to the datepicker field at the checkout.

* **Mandatory Field?**  
  When enabled, this field will be required at checkout.
* **Show Description?**  
  Show the date field description at checkout.
* **Theme**  
  Select a datepicker theme. It uses [jQuery UI](http://jqueryui.com/themeroller/).

#### Date Settings

These options are for setting up date selection conditions.

* **Delivery Days**  
  Select the days that you deliver on.
* **Minimum/Maximum Selectable Date Method**  
  When setting a minimum/maximum selectable date, you can choose if that applies to all days, allowed days only, or weekdays only. See below for minimum/maximum selectable date explanations.
* **Skip Current Day if Not an Allowed Delivery Day?**  
  When checked, this will start the minimum date calculation from the first *allowed* delivery day.
* **Minimum Selectable Date**  
  The minimum selectable date is the first day from now that is selectable at checkout. Same day is 0, next day is 1, and so on.
* **Maximum Selectable Date**  
  The maximum selectable date is the last day from now that is selectable at checkout. If you only allow deliveries to be booked up to 2 weeks in advance, you can set this to 14 (days).
* **Only allow deliveries within the current week?**  
  Sometimes you only want deliveries to be selectable during the current week only. If so, enable this option.
* **Last Day of the Week**  
  Used in conjunction with the above, select the last day of your delivery week.
* **Date Format**  
  Format the date at checkout and in the final order. [View available formats](http://api.jqueryui.com/datepicker/#utility-formatDate).

### Time Settings

#### Time Setup

These options relate specifically to the time slot field at the checkout.

* **Enable Time Slots**  
  You can use the datepicker by itself, or enable time slots by checking this option.
* **Mandatory Field?**  
  When enabled, this field will be required at checkout.
* **Show Description?**  
  Show the time picker description at checkout.
* **Time Format**
  Select the time format, used at checkout and in all order details.

#### Time Slot Configuration

These options relate specifically to your time slots.

* **Allow Bookings Up To (x) Minutes Before Slot**  
  Disable a slot if there is less than (x) minutes before the slot's start time. This can span multiple days.
* **Disable Same Day Delivery if Current Time is After (x)**  
  If current time is after (x), disable same day deliveries.
* **Disable Next Day Delivery if Current Time is After (x)**  
  If current time is after (x), disable next day deliveries.
* **Timeslots**
  * **From**  
    The start time of this time slot.
  * **To**  
    The end time of this time slot.
  * **Allow Bookings Up To (x) Minutes Before Slot**  
    Overrides the default "Allow Bookings Up To (x) Minutes Before Slot" setting for this slot. Leave blank to use the default value.
  * **Lockout**  
    Lockout if a timeslot has received (x) bookings on any one day.
  * **Postcodes** *(WooCommerce v2.5.5 and below)*  
    Restrict this timeslot to a postcode, postcode range, or postcode wildcard. Enter multiple postcode options by separating with a comma.  
    * **Range**: Enter a range by using the hyphen (-) symbol, e.g. 1000-2000.  
    * **Wildcard**: Use the astericks symbol as a wilcard, e.g. CV2*. This will allow all postcodes starting with CV2.
  * **Shipping Methods** *(WooCommerce v2.6.0 and above)*  
    You can now restrict a timeslot to a specific shipping method, based on zones. As the new zones feature of WooCommerce allows for postcode parameters, this has been stripped from Delivery Slots in favour of selecting a shipping method. Select "Any Method" to have the timeslot show for all shipping methods.
  * **Fee**  
    If this timeslot is chosen, add an additional fee at checkout.
  * **Days**  
    Restrict this timeslot to specific days. Select all days for no restriction.

### Holidays

* **Holidays**  
  Enter any holidays where deliveries cannot be made.
  * **From**  
    The start date of the holiday period.
  * **To**  
    The end date of a holiday period. For a single date, leave this field empty and just enter a "From" date. Holidays are up to and including this date.
  * **Name**  
    A name for this holiday. Admin reference only.

### Reservation Table

WooCommerce Delivery Slots comes with a reservation table shortcode `[jckwds]`. This allows your customers to reserve a slot while they do their shopping. **Note**: Time slots are required for the reservation table to work.

#### Reservations

* **Expiration**  
  A reserved slot will expire after (x) minutes.
* **Date Columns**  
  How many date columns to show at one time.
* **Selection Type**  
  The timeslot cell can show the fee or a checkbox.
* **Header Date Format**  
  The format of the date heading for each column.

#### Table Styling

Styling options for the reservation table.

* **Header Cell Colour**  
* **Header Cell Border Colour**  
* **Header Cell Font Colour**
* **Arrow Icon Colour**
* **Arrow Icon Hover Colour**
* **Reserve Cell Colour**
* **Reserve Cell Border Colour**
* **Reserve Icon Colour**
* **Reserve Icon Hover Colour**
* **Unavailable Cell Colour**
* **Reserved Cell Colour**
* **Reserved Cell Border Colour**
* **Reserved Icon Colour**
* **Loading Icon Colour**
* **Lock Icon Colour**

## WooCommerce API

When requesting an order via the API (as of v1.7.4) you can get the delivery data from the JSON response. Under the property `iconic_delivery_meta` you will find the order date, time slot, and timestamp.