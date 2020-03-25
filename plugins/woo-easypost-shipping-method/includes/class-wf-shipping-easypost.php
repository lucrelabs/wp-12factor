<?php

/**
 * WF_USPS_Easypost class.
 *
 * @extends WC_Shipping_Method
 */
class WF_Easypost extends WC_Shipping_Method {

    private $domestic = array("US");
    private $found_rates;
    private $carrier_list;
   
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = WF_EASYPOST_ID;
        $this->method_title = __('EasyPost (BASIC)', 'wf-easypost');
        $this->method_description = __('The <strong>Easypost.com</strong> plugin obtains rates dynamically from the Easypost.com API during cart/checkout.', 'wf-easypost');
        $this->services = include( 'data-wf-services.php' );
        $this->set_carrier_list();
        $this->init();
    }

    /**
     * init function.
     *
     * @access public
     * @return void
     */
    private function init() {
        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->enabled              = isset($this->settings['enabled']) ? $this->settings['enabled'] : $this->enabled;
        $this->title                = !empty($this->settings['title']) ? $this->settings['title'] : $this->method_title;
        $this->zip                  = isset($this->settings['zip']) ? $this->settings['zip'] : '';
        $this->resid = isset($this->settings['show_rates']) && $this->settings['show_rates'] == 'residential' ? true : '';
        $this->availability = isset($this->settings['availability']) ? $this->settings['availability'] : 'all';
        $this->countries = isset($this->settings['countries']) ? $this->settings['countries'] : array();
        $this->senderCountry                = isset($this->settings['country']) ? $this->settings['country'] : '';
        $this->api_key                      = isset($this->settings['api_key']) ? $this->settings['api_key'] : WF_USPS_EASYPOST_ACCESS_KEY;
        $this->custom_services              = isset($this->settings['services']) ? $this->settings['services'] : array();
        $this->offer_rates                  = isset($this->settings['offer_rates']) ? $this->settings['offer_rates'] : 'all';
        $this->fallback                     = !empty($this->settings['fallback']) ? $this->settings['fallback'] : '';
        $this->mediamail_restriction        = isset($this->settings['mediamail_restriction']) ? $this->settings['mediamail_restriction'] : array();
        $this->mediamail_restriction        = array_filter((array) $this->mediamail_restriction);
        $this->enable_standard_services     = true;
        
        $this->debug                  = isset($this->settings['debug_mode']) && $this->settings['debug_mode'] == 'yes' ? true : false;
        $this->api_mode               = isset($this->settings['api_mode']) ? $this->settings['api_mode'] : 'Live';
        $this->carrier = (isset($this->settings['easypost_carrier']) && !empty($this->settings['easypost_carrier']))? $this->settings['easypost_carrier'] : array();
                
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'clear_transients'));
    }

    private function set_carrier_list(){
        foreach ( array_keys($this->services) as $key => $carrier) {
            $carrier_list[$carrier] = $carrier;
        }
        $this->carrier_list = $carrier_list;
    }
    /**
     * environment_check function.
     *
     * @access public
     * @return void
     */
    private function environment_check() {
        global $woocommerce;

        $admin_page = version_compare(WOOCOMMERCE_VERSION, '2.1', '>=') ? 'wc-settings' : 'woocommerce_settings';

        if (!$this->zip && $this->enabled == 'yes') {
            echo '<div class="error">
                <p>' . __('Easypost.com is enabled, but the zip code has not been set.', 'wf-easypost') . '</p>
            </div>';
        }

        $error_message = '';

        // Check for Easypost.com APIKEY
        if (!$this->api_key && $this->enabled == 'yes') {
            $error_message .= '<p>' . __('Easypost.com is enabled, but the Easypost.com API KEY has not been set.', 'wf-easypost') . '</p>';
        }

        if (!$error_message == '') {
            echo '<div class="error">';
            echo $error_message;
            echo '</div>';
        }
    }

    /**
     * admin_options function.
     *
     * @access public
     * @return void
     */
    public function admin_options() {
        // Check users environment supports this method
        $this->environment_check();
       // include('market.php');

        // Show settings
        parent::admin_options();
    }

    /**
     * generate_services_html function.
     */
    public function generate_services_html() {
        ob_start();
        include( 'html-wf-services.php' );
        return ob_get_clean();
    }

    /**
     * validate_services_field function.
     *
     * @access public
     * @param mixed $key
     * @return void
     */
    public function validate_services_field($key) {
        $services           = array();
        $posted_services    = isset($_POST['easypost_service']) ? $_POST['easypost_service'] : array();

        foreach ($posted_services as $code => $settings) {

            foreach ($this->services[$code]['services'] as $key => $name) {

                $services[$code][$key]['enabled']               = isset($settings[$key]['enabled']) ? true : false;
                $services[$code][$key]['order']                 = wc_clean($settings[$key]['order']);
            
            }
        }

        return $services;
    }

    /**
     * clear_transients function.
     *
     * @access public
     * @return void
     */
    public function clear_transients() {
        global $wpdb;

        $wpdb->query("DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_easypost_quote_%') OR `option_name` LIKE ('_transient_timeout_easypost_quote_%')");
    }
    
    public function generate_activate_box_html() {
		ob_start();
		$plugin_name = 'easypost';
		include( 'market.php' );
		return ob_get_clean();
	}

    public function generate_easypost_tabs_html()
        {
            $current_tab = (!empty($_GET['subtab'])) ? esc_attr($_GET['subtab']) : 'general';

                echo '
                <div class="wrap">
                    <script>
                    jQuery(function($){
                    show_selected_tab($(".tab_general"),"general");
                    $(".tab_general").on("click",function(){
                                                return show_selected_tab($(this),"general");
                                        });
                    $(".tab_rates").on("click",function(){
                                                return show_selected_tab($(this),"rates");
                                        });
                    $(".tab_labels").on("click",function(){
                    							$( ".tab_gopremium" ).trigger( "click" );
                                        });
                    $(".tab_packing").on("click",function(){
                    							$( ".tab_gopremium" ).trigger( "click" );
                                        });
                    $(".tab_gopremium").on("click",function(){                                                
                                                return show_selected_tab($(this),"gopremium");
                                        });
                    function show_selected_tab($element,$tab)
                    {
                        $(".nav-tab").removeClass("nav-tab-active");
                        $element.addClass("nav-tab-active");
                        $(".general_tab_field").closest("tr,h3").hide();
                        $(".general_tab_field").next("p").hide();
                                         
                        $(".rates_tab_field").closest("tr,h3").hide();
                        $(".rates_tab_field").next("p").hide();

                        $(".label_tab_field").closest("tr,h3").hide();
                        $(".label_tab_field").next("p").hide();

                        $(".package_tab_field").closest("tr,h3").hide();
                        $(".package_tab_field").next("p").hide();

                        $(".gopremium_tab_field").closest("tr,h3").hide();
                        $(".gopremium_tab_field").next("p").hide();
                        if($tab=="gopremium")
                        {   
                            $(".marketing_content").show();
                        }else{
                            $(".marketing_content").hide();
                        }
                        $("#woocommerce_wf_easypost_availability").trigger("change");
                        $("."+$tab+"_tab_field").closest("tr,h3").show();
                        $("."+$tab+"_tab_field").next("p").show();
                        
                         if($tab=="rates")
                        {
                        	if(document.getElementById("woocommerce_wf_easypost_id_availability").value=="specific")
							{
					            $("#woocommerce_wf_easypost_id_countries").closest("tr").show();
							}	
							else
							{
					            $("#woocommerce_wf_easypost_id_countries").closest("tr").hide();
							}
                        }
                        else
                        {
                        	$("#woocommerce_wf_easypost_id_countries").closest("tr").hide();
                        }

                        if($tab=="gopremium")
                        {
                            $(".woocommerce-save-button").hide();
                        }else
                        {
                            $(".woocommerce-save-button").show();
                        }
                        return false;
                    }   

                    });
                    </script>
                    <style>
                    .wrap {
                                min-height: 800px;
                            }
                    a.nav-tab{
                                cursor: default;
                    }
                    </style>
                    <hr class="wp-header-end">';
                    $tabs = array(
                        'general' => __("General <span style='vertical-align: super;color:green;font-size:12px'></span>", 'wf-easypost'),
                        'rates' => __("Rates & Services <span style='vertical-align: super;color:green;font-size:12px'></span>", 'wf-easypost'),
                        'labels' => __("Label Generation <span style='vertical-align: super;color:green;font-size:12px'>Premium</span>", 'wf-easypost'),
                        'packing' => __("Packaging <span style='vertical-align: super;color:green;font-size:12px'>Premium</span>", 'wf-easypost'),
                        'gopremium' => __("Go Premium <span style='vertical-align: super;color:green;font-size:12px'></span>", 'wf-easypost')
                    );
                    $html = '<h2 class="nav-tab-wrapper">';
                    foreach ($tabs as $stab => $name) {
                        $class = ($stab == $current_tab) ? 'nav-tab-active' : '';
                        $style = ($stab == $current_tab) ? 'border-bottom: 1px solid transparent !important;' : '';
                        $style = ($stab == 'gopremium')? $style.'color:red; !important;':'';
                        $html .= '<a style="text-decoration:none !important;' . $style . '" class="nav-tab ' . $class." tab_".$stab . '" >' . $name . '</a>';
                    }
                    $html .= '</h2>';
                    echo $html;

        }
    
    
    /**
     * init_form_fields function.
     *
     * @access public
     * @return void
     */
    public function init_form_fields() {
        global $woocommerce;

        $shipping_classes = array();
        $classes = ( $classes = get_terms('product_shipping_class', array('hide_empty' => '0')) ) ? $classes : array();

        foreach ($classes as $class)
            $shipping_classes[$class->term_id] = $class->name;

        if (WF_EASYPOST_ADV_DEBUG_MODE == "on") { // Test mode is only for development purpose.
            $api_mode_options = array(
                'Live' => __('Live', 'wf-easypost'),
                'Test' => __('Test', 'wf-easypost'),
            );
        } else {
            $api_mode_options = array(
                'Live' => __('Live', 'wf-easypost'),
            );
        }
        
        $this->form_fields = array(
            
            'easypost_wrapper'=>array(
                            'type'=>'easypost_tabs'
                        ),
            'gopremium'  => array(
				'type'			=> 'activate_box',
                                'class'                             =>'gopremium_tab_field'
                ),
            
            'enabled'               => array(
                'title'             => __('Realtime Rates', 'wf-easypost'),
                'type'              => 'checkbox',
                'label'             => __('Enable', 'wf-easypost'),
                'description'       => __( 'Enable realtime rates on Cart/Checkout page.', 'wf-easypost' ),
                'default'           => 'no',
                'desc_tip'          => true,
                'class'             =>'general_tab_field'
            ),
            'title'                 => array(
                'title'             => __('Method Title', 'wf-easypost'),
                'type'              => 'text',
                'description'       => __('This controls the title for fall-back rate which the user sees during Cart/Checkout.', 'wf-easypost'),
                'default'           => __($this->method_title, 'wf-easypost'),
                'placeholder'       => __($this->method_title, 'wf-easypost'),
                'desc_tip'          => true,
                'class'             =>'rates_tab_field'
            ),
            
             'availability' => array(
                'title' => __('Method Available to', 'wf-easypost'),
                'type' => 'select',
                'css'  => 'padding: 0px;',
                'default' => 'all',
                'description' => __('Select the countries','wf-easypost'),
                'desc_tip' => true,
                'class' => 'availability rates_tab_field',
                'options' => array(
                    'all' => __('All Countries', 'wf-easypost'),
                    'specific' => __('Specific Countries', 'wf-easypost'),
                ),
            ),
            'countries' => array(
                'title' => __('Specific Countries', 'wf-easypost'),
                'type' => 'multiselect',
                'class' => 'chosen_select rates_tab_field',
                'description' => __('Select specific countries','wf-easypost'),
                'desc_tip' => true,
                'css' => 'width: 450px;',
                'default' => '',
                'options' => $woocommerce->countries->get_allowed_countries(),
            ),
            
            'debug_mode'            => array(
                'title'             => __('Debug Mode', 'wf-easypost'),
                'label'             => __('Enable', 'wf-easypost'),
                'type'              => 'checkbox',
                'default'           => 'no',
                'description'       => __('Enable debug mode to show debugging information on your cart/checkout. Not recommended to enable this in live site with traffic.', 'wf-easypost'),
                'desc_tip'          => true,
                'class'             =>'general_tab_field'
            ),
            'api'                   => array(
                'title'             => __('Generic API Settings', 'wf-easypost'),
                'type'              => 'title',
                'description' => __('To obtain a Easypost.com API Key, Signup & Login to the ', 'wf-easypost'). '<a href="http://www.easypost.com" target="_blank">' . __('EasyPost.com', 'wf-easypost') . '</a>'.__(' and then go to the ', 'wf-easypost'). '<a href="https://www.easypost.com/account/api-keys" target="_blank">'.__('API Keys section.', 'wf-easypost'). '</a></br>'.__('You will find different API Keys for Live and Test mode.', 'wf-easypost'),
                'class'             =>'general_tab_field'
            ),
            
            'api_mode'              => array(
                'title'             => __('API Mode', 'wf-easypost'),
                'type'              => 'select',
                'css'               => 'padding: 0px;',
                'default'           => 'Live',
                'options'           => $api_mode_options,
                'description'       => __('Live mode is the strict choice for Customers as Test mode is strictly restricted for development purpose by Easypost.com.', 'wf-easypost'),
                'desc_tip'          => true,
                'class'             =>'general_tab_field'
            ),
            
            'api_key'               => array(
                'title'             => __('API-KEY', 'wf-easypost'),
                'type'              => 'password',
                'description'       => __('Live and Test mode APIs keys are different. Make sure to enter the right key based on API Mode.', 'wf-easypost'),
                'default'           => '',
                'desc_tip'          => true,
                'custom_attributes' => array(
                    'autocomplete' => 'off'
                    ),
                'class'             =>'general_tab_field'
            ),
            
           
            'zip'                   => array(
                'title'             => __('Zip Code', 'wf-easypost'),
                'type'              => 'text',
                'description'       => __('Enter the postcode for the sender', 'wf-easypost'),
                'default'           => '',
                'desc_tip'          => true,
                'class'             =>'rates_tab_field'
            ),
            'country' => array(
                'title' => __('Sender Country', 'wf-easypost'),
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'width: 450px;',
                'description' => __('Select the sender country', 'wf-easypost'),
                'desc_tip' => true,
                'default' => 'US',
                'options' => $woocommerce->countries->get_allowed_countries(),
                'class' =>'rates_tab_field',
            ),
           
            'selected_flat_rate_boxes' => array(
                'title' => __('Flat Rate Boxes <span style="vertical-align: super;color:green;font-size:12px">Premium</span>', 'wf-easypost'),
                'type' => 'multiselect',
                'class' => 'multiselect chosen_select selected_flat_rate_boxes rates_tab_field',
                'default' => '',
                'description' => __('Select flat rate boxes to make available.', 'wf-easypost'),
                'desc_tip' => false,
                'custom_attributes'=>array('disabled'=>'disabled')
            ),
            'flat_rate_fee' => array(
                'title' => __('Flat Rate Fee <span style="vertical-align: super;color:green;font-size:12px">Premium</span>', 'wf-easypost'),
                'type' => 'text',
                'description' => __('Fee per-box excluding tax. Enter an amount, e.g. 2.50, or a percentage, e.g. 5%. Leave blank to disable.', 'wf-easypost'),
                'default' => '',
                'desc_tip' => true,
                'class' =>'rates_tab_field',
                'custom_attributes'=>array('disabled'=>'disabled')
            ),
            
            'fallback'              => array(
                'title'             => __('Fallback', 'wf-easypost'),
                'type'              => 'text',
                'description'       => __('If Easypost.com returns no matching rates, offer this amount for shipping so that the user can still checkout. Leave blank to disable.', 'wf-easypost'),
                'default'           => '',
                'desc_tip'          => true,
                'class'             =>'rates_tab_field'
            ),
            'show_rates' => array(
                'title' => __('Rates Type', 'wf-easypost'),
                'type' => 'select',
                'css'  => 'padding: 0px;',
                'default' => 'commercial',
                'options' => array(
                    'residential' => __('Residential', 'wf-easypost'),
                    'commercial' => __('Commercial', 'wf-easypost'),
                ),
                'description' => __('Rates will be fetched based on the address type that you choose here. Please note this functionality will be available only for supported carriers.', 'wf-easypost'),
                'desc_tip' => true,
                'class' =>'rates_tab_field',
            ),
            'easypost_carrier'       => array(
                'title'           => __( 'Easypost Carrier(s)', 'wf-easypost' ),
                'type'            => 'multiselect',
                'description'     => __( 'Select your Easypost Carriers.', 'wf-easypost' ),
                'default'         => array('USPS'),
                'css'             => 'width: 450px;',
                'class'           => 'ups_packaging chosen_select rates_tab_field',
                'options'         => $this->carrier_list,
                'desc_tip'        => true,
                'custom_attributes' => array(
                    'autocomplete' => 'off'
                ),
            ),
            'services'              => array(
                'type'              => 'services',
                'class'             =>'rates_tab_field'
            ),
        );
    }
   

    /**
     * calculate_shipping function.
     *
     * @access public
     * @param mixed $package
     * @return void
     */
    public function calculate_shipping( $package=array() ) {
        global $woocommerce;
        $this->rates = array();
        $this->unpacked_item_costs = 0;
        $domestic = in_array($package['destination']['country'], $this->domestic) ? true : false;

        $this->debug(__('Easypost.com debug mode is on - to hide these messages, turn debug mode off in the settings.', 'wf-easypost'));
        
        if ($this->enable_standard_services) {
            // Get cart package details and proceed with GetRates.
            $package_requests = $this->get_package_requests($package);
            libxml_use_internal_errors(true);
            if ($package_requests) {

                if(!class_exists('EasyPost\EasyPost')){ 
                    require_once(plugin_dir_path(dirname(__FILE__)) . "/easypost.php");
                }
                \EasyPost\EasyPost::setApiKey($this->settings['api_key']);

                $responses = array();
                foreach ($package_requests as $key => $package_request) {
                    $responses[] = $this->get_result( $package_request);
                }
                if(!$responses){
                    return false;
                }

                $found_rates = array();

                foreach ($responses as $response_ele) {
                    $response_obj = $response_ele['response'];
                    if ( isset($response_obj->rates) && !empty($response_obj->rates) ) {
                        foreach ($this->carrier as $carrier_name) {
                            $flag_currency_convertion = false;
                            foreach ($response_obj->rates as $easypost_rate) {
                                if($carrier_name == $easypost_rate->carrier) {
                                    
                                    if( $flag_currency_convertion  == false) {
                                        $from_currency = $easypost_rate->currency;
                                        $to_currency = get_woocommerce_currency();
                                        $converted_currency = $this->xa_currency_converter($from_currency, $to_currency, $easypost_rate->carrier);
                                        $flag_currency_convertion = true;
                                    }
                                    
                                    if( !$converted_currency ) {
                                        break;
                                    }
                                    
                                    $service_type       = (string) $easypost_rate->service;
                                    $service_name = (string) ( isset($this->custom_services[$carrier_name][$service_type]['name']) && !empty($this->custom_services[$carrier_name][$service_type]['name']) ) ? $this->custom_services[$carrier_name][$service_type]['name'] :$this->services[$carrier_name]['services'][$service_type];
                                    $total_amount       = $response_ele['quantity'] * $easypost_rate->rate;
                                    $total_amount = $total_amount * $converted_currency;
                                    // Sort
                                    if ( isset( $this->custom_services[$carrier_name][$service_type]['order'] ) ) {
                                        $sort = $this->custom_services[$carrier_name][$service_type]['order'];
                                    } else {
                                        $sort = 999;
                                    }
                                    
                                    if (isset($found_rates[$service_type])) {
                                        $found_rates[$service_type]['cost']     = $found_rates[$service_type]['cost'] + $total_amount;
                                    } else {
                                        $found_rates[$service_type]['label']    = $service_name;
                                        $found_rates[$service_type]['cost']     = $total_amount;
                                        $found_rates[$service_type]['carrier']  = $easypost_rate->carrier;
                                        $found_rates[$service_type]['sort']     = $sort;
                                    }
                                }
                            }
                        }
                    } else {

                        $this->debug(__('Easypost.com - No rated returned from API.', 'wf-easypost'));
                        // return;
                    }
                }
                $rate_added = 0;
                if ($found_rates) {
                    uasort( $found_rates, array( $this, 'sort_rates' ) );
                    foreach ($this->carrier as $carrier_name) {
                        foreach ($found_rates as $service_type => $found_rate) {
                            // Enabled check
                            if($carrier_name == $found_rate['carrier']) {
                                if (isset($this->custom_services[$carrier_name][$service_type]) && empty($this->custom_services[$carrier_name][$service_type]['enabled'])) {
                                    continue;
                                }                           
                                $total_amount = $found_rate['cost'];
                                // Cost adjustment %
                                if (!empty($this->custom_services[$carrier_name][$service_type]['adjustment_percent'])) {
                                    $total_amount = $total_amount + ( $total_amount * ( floatval($this->custom_services[$carrier_name][$service_type]['adjustment_percent']) / 100 ) );
                                }
                                // Cost adjustment
                                if (!empty($this->custom_services[$carrier_name][$service_type]['adjustment'])) {
                                    $total_amount = $total_amount + floatval($this->custom_services[$carrier_name][$service_type]['adjustment']);
                                }
                                $labelName = !empty( $this->settings['services'][$carrier_name][$service_type]['name'] ) 
                                                ? $this->settings['services'][$carrier_name][$service_type]['name']
                                                : $this->services[$carrier_name]['services'][$service_type];
                                $rate = array(
                                    'id'        => (string) $this->id . ':' . $service_type,
                                    'label'     =>  (string)$labelName,
                                    'cost'      => (string) $total_amount,
                                    'calc_tax'  => 'per_item',
                                );
                                // Register the rate
                                $this->add_rate($rate);
                                $rate_added++;
                            }
                        }
                    }
                }
            }
        }

    }
    
    function xa_currency_converter( $from_currency, $to_currency, $carrier ) {
        if($from_currency == $to_currency) {
             return 1;
        }
        else {
            $from_currency = urlencode($from_currency);
            $to_currency = urlencode($to_currency);
            try {
            $result = @file_get_contents("https://www.alphavantage.co/query?function=CURRENCY_EXCHANGE_RATE&from_currency=$from_currency&to_currency=$to_currency&apikey=G1QF4V7WM07HNOB2");
             if ( $result === FALSE ) {
                throw new Exception("Unable to receive currency conversion response from Google Finance API call ( https://finance.google.com/finance/converter ). Skipping the shipping rates for the carrier $carrier as shop currency and the currency returned by Rates API differs.");
              }
            }
            catch(Exception $e) {
                 $this->debug(__($e->getMessage(),'wf-easypost'));
                 return 0;
            }
            $result = json_decode($result,true);
            $converted_currency = $result['Realtime Currency Exchange Rate']['5. Exchange Rate'];
            return $converted_currency;
        }
    }

    private function get_result( $package_request, $predefined_package='' ){
        // Get rates.
        try {
            $payload = array();
            $payload['from_address'] = array(
                            "zip"     => $this->zip,
                            "country"=>$this->senderCountry,
                        );
            $payload['to_address'] =  array(
                            // Name and Street1 are required fields for getting rates.
                            // But, at this point, these details are not available.
                            "name"    => "-",
                            "street1" => "-",
                            "residential" => $this->resid,
                            "zip"     => $package_request['request']['Rate']['ToZIPCode'],
                            "country" => $package_request['request']['Rate']['ToCountry']
                        );
            
            if(!empty($package_request['request']['Rate']['WeightLb']) && $package_request['request']['Rate']['WeightOz'] == 0.00) {
                $package_request['request']['Rate']['WeightOz'] = number_format($package_request['request']['Rate']['WeightLb']*16,0,'.','');
            }
                
            $payload['parcel'] = array(
                            'length'  => $package_request['request']['Rate']['Length'],
                            'width'   => $package_request['request']['Rate']['Width'],
                            'height'  => $package_request['request']['Rate']['Height'],
                            'weight'  => $package_request['request']['Rate']['WeightOz'],
            );
            if( !empty($predefined_package ) ){
                $payload['parcel']['predefined_package'] = strpos($predefined_package, "-") ? substr($predefined_package, 0, strpos($predefined_package, "-")) : $predefined_package;
            }
            
            $payload['options'] = array(
                    "special_rates_eligibility" => 'USPS.LIBRARYMAIL,USPS.MEDIAMAIL'
            );


            $this->debug( 'EASYPOST REQUEST: <pre>' . print_r( $payload , true ) . '</pre>' );
            $shipment           = \EasyPost\Shipment::create($payload);
            $response           = json_decode($shipment);
            $this->debug( 'EASYPOST RESPONSE: <pre>' . print_r( $response , true ) . '</pre>' );
            $response_ele       = array();
            
            $response_ele['response'] = $response;
            $response_ele['quantity'] = $package_request['quantity'];
        } catch (Exception $e) {
            
            if(strpos($e->getMessage(), 'Could not connect to EasyPost') !== false)
            {
                if ($this->fallback) {
            $this->debug(__('Easypost.com - Calculating fall back rates', 'wf-easypost'));
            $rate = array(
                'id' => (string) $this->id . ':_fallback',
                'label' => (string) $this->title,
                'cost' => $this->fallback,
                'calc_tax' => 'per_item',
            );
            // Register the rate
            $this->add_rate($rate);
                }
            }
            
            $this->debug(__('Easypost.com - Unable to Get Rates: ', 'wf-easypost') . $e->getMessage());
            if (WF_EASYPOST_ADV_DEBUG_MODE == "on") {
                $this->debug(print_r($e, true));
            }
            return false;
        }
        return $response_ele;
    }

    /**
     * prepare_rate function.
     *
     * @access private
     * @param mixed $rate_code
     * @param mixed $rate_id
     * @param mixed $rate_name
     * @param mixed $rate_cost
     * @return void
     */
    private function prepare_rate($rate_code, $rate_id, $rate_name, $rate_cost) {

        // Name adjustment
        if (!empty($this->custom_services[$rate_code]['name']))
            $rate_name = $this->custom_services[$rate_code]['name'];

        // Merging
        if (isset($this->found_rates[$rate_id])) {
            $rate_cost = $rate_cost + $this->found_rates[$rate_id]['cost'];
            $packages = 1 + $this->found_rates[$rate_id]['packages'];
        } else {
            $packages = 1;
        }

        // Sort
        if (isset($this->custom_services[$rate_code]['order'])) {
            $sort = $this->custom_services[$rate_code]['order'];
        } else {
            $sort = 999;
        }

        $this->found_rates[$rate_id] = array(
            'id'        => $rate_id,
            'label'     => $rate_name,
            'cost'      => $rate_cost,
            'sort'      => $sort,
            'packages'  => $packages
        );
    }

    /**
     * sort_rates function.
     *
     * @access public
     * @param mixed $a
     * @param mixed $b
     * @return void
     */
    public function sort_rates($a, $b) {
        if ($a['sort'] == $b['sort'])
            return 0;
        return ( $a['sort'] < $b['sort'] ) ? -1 : 1;
    }

    /**
     * get_request function.
     *
     * @access private
     * @return void
     */
    // WF - Changing function to public.
    public function get_package_requests($package) {
        $requests = $this->per_item_shipping($package);

        return $requests;
    }

    /**
     * per_item_shipping function.
     *
     * @access private
     * @param mixed $package
     * @return void
     */
    private function per_item_shipping($package) {
        global $woocommerce;
       
        $requests = array();    
        $domestic = in_array($package['destination']['country'], $this->domestic) ? true : false;

        // Get weight of order
        foreach ($package['contents'] as $item_id => $values) {
            $values['data'] = $this->wf_load_product( $values['data'] );

            if (!$values['data']->needs_shipping()) {
                $this->debug(sprintf(__('Product # is virtual. Skipping.', 'wf-easypost'), $item_id));
                continue;
            }

            if (!$values['data']->get_weight()) {
                $this->debug(sprintf(__('Product # is missing weight. Using 1lb.', 'wf-easypost'), $item_id));

                $weight = 1;
                $weightoz = 1; // added for default
            } else {
                $weight = wc_get_weight($values['data']->get_weight(), 'lbs');
                $weightoz = wc_get_weight($values['data']->get_weight(), 'oz');
            }

            $size = 'REGULAR';

            if ($values['data']->length && $values['data']->height && $values['data']->width) {

                $dimensions = array(wc_get_dimension($values['data']->length, 'in'), wc_get_dimension($values['data']->height, 'in'), wc_get_dimension($values['data']->width, 'in'));

                sort($dimensions);

                if (max($dimensions) > 12) {
                    $size = 'LARGE';
                }

                $girth = $dimensions[0] + $dimensions[0] + $dimensions[1] + $dimensions[1];
            } else {
                $dimensions = array(0, 0, 0);
                $girth = 0;
            }

            $quantity = $values['quantity'];

            if ('LARGE' === $size) {
                $rectangular_shaped = 'true';
            } else {
                $rectangular_shaped = 'false';
            }

            $dest_postal_code = !empty( $package['destination']['postcode'] ) ? $package['destination']['postcode'] : (isset($package['destination']['zip'])?$package['destination']['zip']:'');
            if ($domestic) {
                $request['Rate'] = array(
                    'FromZIPCode'       => str_replace(' ', '', strtoupper($this->settings['zip'])),
                    'ToZIPCode'         => strtoupper(substr($dest_postal_code, 0, 5)),
                    'ToCountry'         => $package['destination']['country'],
                    'WeightLb'          => floor($weight),
                    'WeightOz'          => number_format($weightoz,0,'.',''),
                    'PackageType'       => 'Package',
                    'Length'            => $dimensions[2],
                    'Width'             => $dimensions[1],
                    'Height'            => $dimensions[0],
                    'ShipDate'          => date("Y-m-d", ( current_time('timestamp') + (60 * 60 * 24))),
                    'InsuredValue'      => $values['data']->get_price(),
                    'RectangularShaped' => $rectangular_shaped
                );
            } else {
                $request['Rate'] = array(
                    'FromZIPCode'       => str_replace(' ', '', strtoupper($this->settings['zip'])),
                    'ToZIPCode'         => $dest_postal_code,
                    'ToCountry'         => $package['destination']['country'],
                    'Amount'            => $values['data']->get_price(),
                    'WeightLb'          => floor($weight),
                    'WeightOz'          => number_format($weightoz,0,'.',''),
                    'PackageType'       => 'Package',
                    'Length'            => $dimensions[2],
                    'Width'             => $dimensions[1],
                    'Height'            => $dimensions[0],
                    'ShipDate'          => date("Y-m-d", ( current_time('timestamp') + (60 * 60 * 24))),
                    'InsuredValue'      => $values['data']->get_price(),
                    'RectangularShaped' => $rectangular_shaped
                );
            }
            
            $request['unpacked']    = array();
            $request['packed']      = array($values['data']);

            $request_ele                = array();
            $request_ele['request']     = $request;
            $request_ele['quantity']    = $quantity;

            $requests[]                 = $request_ele;
        }
        return $requests;
    }

    /**
     * box_shipping function.
     *
     * @access private
     * @param mixed $package
     * @return void
     */

    public function debug($message, $type = 'notice') {
        if ($this->debug && !is_admin()) { //WF: is_admin check added.
            if (version_compare(WOOCOMMERCE_VERSION, '2.1', '>=')) {
                wc_add_notice($message, $type);
            } else {
                global $woocommerce;
                $woocommerce->add_message($message);
            }
        }
    }
    
    
    private function wf_load_product( $product ){
        if( !$product ){
            return false;
        }
        if( !class_exists('wf_product') ){
            include_once('class-wf-legacy.php');
        }
        if($product instanceof wf_product){
            return $product;
        }
        return ( WC()->version < '2.7.0' ) ? $product : new wf_product( $product );
    }

}
