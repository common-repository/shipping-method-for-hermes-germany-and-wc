 <?php

 class Hermes_Germany_Shipping extends WC_Shipping_Method {
	 /**
	  * @var mixed
	  */
	 private $debug;
	 /**
	  * @var mixed
	  */
	 private $weight;
	 /**
	  * @var mixed
	  */
	 private $length;
	 /**
	  * @var mixed
	  */
	 private $width;
	 /**
	  * @var mixed
	  */
	 private $height;

	 /**
	  * Constructor for your shipping class
	  * @access public
	  *
	  * @param int $instance_id
	  */
    public function __construct($instance_id = 0)
    {

        $this->id = WPRUBY_HERMES_PLUGIN_ID;
        $this->method_title = __('Hermes Shipping', "shipping-method-for-hermes-germany-and-wc");
        $this->method_description = __("A Hermes Germany shipping calculator", "shipping-method-for-hermes-germany-and-wc");
        $this->instance_id = absint( $instance_id );
        $this->supports  = ['shipping-zones', 'instance-settings'];
        $this->init();

    }

    /**
     * Init your settings
     *
     * @access public
     * @return void
     */
    public function init()
    {
	    // Load the settings API
	    $this->init_form_fields();
	    $this->init_settings();

	    $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'yes';
	    $this->title = isset($this->settings['title']) ? $this->settings['title'] : __('Hermes Shipping', "shipping-method-for-hermes-germany-and-wc");


	    $this->debug = $this->get_option('debug');
	    $this->weight = $this->get_option('weight');
	    $this->length = $this->get_option('length');
	    $this->width = $this->get_option('width');
	    $this->height = $this->get_option('height');


        // Save settings in admin if you have any defined
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    public function admin_options() {
        $this->check_store_requirements();
        parent::admin_options();
    }

	/**
     * Define settings field for this shipping
     * @return void
     */
    public function init_form_fields()
    {

        $this->instance_form_fields = array(
            'enabled' => array(
                'title' => __('Enable', "shipping-method-for-hermes-germany-and-wc"),
                'type' => 'checkbox',
                'description' => __('Enable Hermes Germany', "shipping-method-for-hermes-germany-and-wc"),
                'default' => 'yes'
            ),

            'title' => array(
                'title' => __('Title', "shipping-method-for-hermes-germany-and-wc"),
                'type' => 'text',
                'description' => __('Title to be display in the Cart & Checkout pages.', "shipping-method-for-hermes-germany-and-wc"),
                'default' => __('Hermes Shipping', "shipping-method-for-hermes-germany-and-wc")
            ),

            'debug' => array(
                'title' => __('Debug Mode', "shipping-method-for-hermes-germany-and-wc"),
                'type' => 'checkbox',
                'description' => __('Enable debug mode.', "shipping-method-for-hermes-germany-and-wc"),
                'default' => 'no'
            ),

            'weight' => array(
                'title' => __('Default Package Weight (g)', "shipping-method-for-hermes-germany-and-wc"),
                'type' => 'number',
                'description' => __('Default Hermes package weight to be use (in case product weight is not specified)', "shipping-method-for-hermes-germany-and-wc"),
                'default' => 1000
            ),

            'length' => array(
                'title' => __('Default Package Length (mm)', "shipping-method-for-hermes-germany-and-wc"),
                'type' => 'number',
                'description' => __('Default Hermes package length to be use (in case product length is not specified)', "shipping-method-for-hermes-germany-and-wc"),
                'default' => 120
            ),

            'width' => array(
                'title' => __('Default Package Width (mm)', "shipping-method-for-hermes-germany-and-wc"),
                'type' => 'number',
                'description' => __('Default Hermes package width to be use (in case product width is not specified)', "shipping-method-for-hermes-germany-and-wc"),
                'default' => 100
            ),

            'height' => array(
                'title' => __('Default Package Height (mm)', "shipping-method-for-hermes-germany-and-wc"),
                'type' => 'number',
                'description' => __('Default Hermes package height to be use (in case product height is not specified)', "shipping-method-for-hermes-germany-and-wc"),
                'default' => 40
            ),
        );

        $packages = include 'includes/packages.php';

        foreach ($packages as $key => $package) {
            $package = (object)$package;
            $packageField =
                [
                	'title'         => __($package->title, "shipping-method-for-hermes-germany-and-wc"),
                    'description'   => __($package->description, "shipping-method-for-hermes-germany-and-wc"),
	                'type'          => $package->type,
	                'default'       => $package->default
                ];
            $this->instance_form_fields[$package->id . '_enabled'] = $packageField;
        }

    }

    /**
     * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
     *
     * @access public
     * @param mixed $package
     * @return void
     */
    public function calculate_shipping($package = array())
    {

       try {

			$data = $this->process_shipping($package['contents']);
			$this->debug('Plugin Settings: <pre>' . print_r($this->instance_settings, true) . '</pre>');
			$this->debug('Packing Details: <pre>' . print_r($data, true) . '</pre>');
			if ($package['destination']['country'] != 'DE') {
				return;
			}


			$this->add_rate(array(
				'id' => $this->id,
				'label' => $this->title,
				'cost' => $data['shippingCost']
			));

		} catch (Exception $exception) {
			error_log(print_r($exception, true));
		}
    }


	private function debug($message, $type = 'notice')
	{
		if ($this->get_option('debug') == 'yes' && current_user_can('manage_options')) {
			wc_add_notice($message, $type);
		}
	}


	 /**
     * This function is used to filter active packages
     * @param $package
     * @return mixed
     */
    private function filter_active_packages($package)
    {
        $package = (object)$package;
        return $this->get_option($package->id . '_enabled') === 'yes';
    }

    /**
     * @param $items
     * @return array
     */
    private function process_shipping($items)
    {

        $packages = include 'includes/packages.php';
        $boxAllowed = array_filter($packages, array($this, "filter_active_packages"));
        $boxPacker = new WPRuby_Hermes_Calculate_Shipping_Price($boxAllowed);

        $defaults['weight'] = $this->weight;
        $defaults['height'] = $this->height;
        $defaults['width'] = $this->width;
        $defaults['length'] = $this->length;


        $boxPacker->add_items($items, $defaults)->pack_items()->calculate_total_shipping_amount();

        $shippingCost = $boxPacker->get_shipping_amount();
        $totalCost = $boxPacker->get_order_amount();
        $maximumLiabilityAllowed = $boxPacker->maximum_liability_allowed();

        return [
        	'shippingCost' => $shippingCost,
	        'totalCost' => $totalCost,
	        'maximumLiabilityAllowed' => $maximumLiabilityAllowed
        ];
    }

    /**
     * check_store_requirements function.
     * @access public
     * @return void
     */
    public function check_store_requirements()
    {
        if (get_woocommerce_currency() != "EUR") {
            $this->display_error_message(__('In order to use the Hermes Shipping extension to work, the store currency must be Euro.', "shipping-method-for-hermes-germany-and-wc"));
        }

        if (WC()->countries->get_base_country() != "DE") {
            $this->display_error_message(__('In order to the Hermes Shipping extension to work, the base country/region must be set to Germany.', "shipping-method-for-hermes-germany-and-wc"));
        }
    }

    /**
     * @param $message string
     */
    private function display_error_message($message)
    {
        $url = esc_url(self_admin_url('admin.php?page=wc-settings&tab=general'));
        $link = "<a href='$url'>" . esc_html(__('Click here to change the setting.', "shipping-method-for-hermes-germany-and-wc")) . '</a>';
        $message = esc_html($message);

        echo "<div class='error'><p>$message $link</p></div>";
    }
}
