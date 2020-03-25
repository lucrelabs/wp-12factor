<?php

/**
 * EasyPost Carrier Services and subservices
 */
return array(

    // Domestic & International

    'USPS' => array(

        // Services which costs are merged if returned (cheapest is used). This gives us the best possible rate.

        'services' => array(

            "First" => "First-Class Mail",

            "Priority" => "Priority Mail&#0174;",

            "Express" => "Priority Mail Express&#8482;",

            "ParcelSelect" => "USPS Parcel Select",

            "LibraryMail" => "Library Mail Parcel",

            "MediaMail" => "Media Mail Parcel",

            "CriticalMail" => "USPS Critical Mail",

            "FirstClassMailInternational" => "First Class Mail International",

            "FirstClassPackageInternationalService" => "First Class Package Service&#8482; International",

            "PriorityMailInternational" => "Priority Mail International&#0174;",

            "ExpressMailInternational" => "Express Mail International"

        )

    ),
	'FedEx' => array(
	
		'services' => array(
		
			"FIRST_OVERNIGHT" => "First Overnight",
			
			"PRIORITY_OVERNIGHT" => "Priority Overnight",
			
			"STANDARD_OVERNIGHT" => "Standard Overnight",
			
			"FEDEX_2_DAY_AM" => "FedEx 2 Day AM",
			
			"FEDEX_2_DAY" => "FedEx 2 Day",
			
			"FEDEX_EXPRESS_SAVER" => "FedEx Express Saver",
            
            "GROUND_HOME_DELIVERY" => "FedEx Ground Home Delivery",
			
			"FEDEX_GROUND" => "FedEx Ground",
			
			"INTERNATIONAL_PRIORITY" => "FedEx International Priority",
			
			"INTERNATIONAL_ECONOMY" =>"FedEx International Economy",
			
			"INTERNATIONAL_FIRST" =>"FedEx International First"	
		)
		
	),
	'UPS' => array(
	
		'services' => array(
		
			"Ground" => "Ground (UPS)",
			
			"3DaySelect" => "3 Day Select (UPS)",
			
			"2ndDayAirAM" => "2nd Day Air AM (UPS)",
			
			"2ndDayAir" => "2nd Day Air (UPS)",
			
			"NextDayAirSaver" => "Next Day Air Saver (UPS)",
			
			"NextDayAirEarlyAM" => "Next Day Air Early AM (UPS)",
			
			"NextDayAir" => "Next Day Air (UPS)",
			
			"Express" => "Express (UPS)",
			
			"Expedited" => "Expedited (UPS)",
			
			"ExpressPlus" => "Express Plus (UPS)",
			
			"UPSSaver" => "UPS Saver (UPS)",
			
			"UPSStandard" => "UPS Standard (UPS)"
		)
	),
		

);

