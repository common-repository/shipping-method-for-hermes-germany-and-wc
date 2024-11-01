<?php

use WPRuby\Hermes\DVDoug\BoxPacker\Packer;

require_once plugin_dir_path(__FILE__) . '/class-wpruby-hermes-box.php';
require_once plugin_dir_path(__FILE__) . '/class-wpruby-hermes-item.php';

/**
 * Class Calculate_Shipping_Price
 */
class WPRuby_Hermes_Calculate_Shipping_Price
{

    /**
     * @var Packer
     */
    private $packer;

    /**
     * @var
     */
    private $packedBoxes;

    /**
     * @var array
     */
    private $allowedBoxes = array();

    /**
     * @var int
     */
    private $shippingAmount = 0;

    /**
     * @var int
     */
    private $orderAmount = 0;

    /**
     * @var int
     */
    private $maximumLiabilityAmountAllowed = 0;

    /**
     * Calculate_Shipping_Price constructor.
     * @param $allowedBoxes
     */
    public function __construct($allowedBoxes)
    {
        $this->allowedBoxes = $allowedBoxes;
        $this->packer = new Packer();
        foreach ($this->allowedBoxes as $key => $box) {
            $box = (object)$box;
            $this->packer->addBox(new WPRuby_Hermes_Box($key, $box->outer_width, $box->outer_length, $box->outer_height,
                $box->empty_weight, $box->inner_width, $box->inner_length, $box->inner_height, $box->max_weight));
        }
    }

    /**
     * @param $products
     * @param array $defaults
     * @return $this
     */
	 
	 public function add_items($products, $defaults = array())
    {
	    $default_length = (isset($defaults['length'])) ? $defaults['length'] : 1;
	    $default_width  = (isset($defaults['width']))  ? $defaults['width']  : 1;
	    $default_height = (isset($defaults['height'])) ? $defaults['height'] : 1;
	    $default_weight = (isset($defaults['height'])) ? $defaults['height'] : 1;

        foreach ($products as $key => $product) {
            $_product = $product['data'];

	        $length = wc_get_dimension(($_product->get_length() <= 0) ? $default_length : $_product->get_length(), 'mm');
	        $height = wc_get_dimension(($_product->get_height() <= 0) ? $default_height : $_product->get_height(), 'mm');
	        $width =  wc_get_dimension(($_product->get_width() <= 0) ? $default_width : $_product->get_width(), 'mm');
            $weight = wc_get_weight(($_product->get_weight() <= 0) ? $default_weight : $_product->get_weight(), 'g');

            $price = $_product->get_price() ? $_product->get_price() : 0;
            $price = $price * $product['quantity'];
            $this->orderAmount = +$price;
            $this->packer->addItem(new WPRuby_Hermes_Item($key, $width, $length, $height, $weight, true), $product['quantity']); // item, quantity
        }

        return $this;

    }

    /**
     * @return $this
     */
    public function pack_items()
    {

        $this->packedBoxes = $this->packer->pack();
        return $this;

    }

    /**
     * @return $this
     */
    public function calculate_total_shipping_amount()
    {
	    $this->shippingAmount = 0;
        foreach ($this->packedBoxes as $packedBox) {
            $boxType = $packedBox->getBox();
            $this->shippingAmount += $this->allowedBoxes[$boxType->getReference()]['price'];
            $this->maximumLiabilityAmountAllowed = +$this->allowedBoxes[$boxType->getReference()]['maximum_liability'];
        }

        return $this;

    }

    /**
     * @return int
     */
    public function get_shipping_amount()
    {
        return $this->shippingAmount;
    }

    /**
     * @return int
     */
    public function get_order_amount()
    {
        return $this->orderAmount;
    }

    /**
     * @return int
     */
    public function maximum_liability_allowed()
    {
        return $this->maximumLiabilityAmountAllowed;
    }

}