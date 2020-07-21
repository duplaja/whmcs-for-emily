<?php
/*
Plugin Name: WHMCS Price For Emily
Plugin URI: https://codeable.io/developers/dan-dulaney/
Description: Customized WHMCS Price Plugin
Version: 1.1
Author: Dan Dulaney
Author URI: https://codeable.io/developers/dan-dulaney/
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2020 by Dan Dulaney <dan.dulaney07@gmail.com>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if (! function_exists('get_whmcs_price')) {

    /**
     *  Gets price given URL, and saves to transient
     * 
     * @param $slug, $url
     * @return $price
     **/


    function get_whmcs_price($slug,$url) {

	$slug = $slug.'-2';
	    
        $price = get_transient( $slug );
  
        // Yep!  Just return it and we're done.
        if( ! empty( $price ) ) {
    
            // The function will return here every time after the first time it is run, until the transient expires.
            return $price;
    
        // Nope!  We gotta make a call.
        } else {

            $price_page = wp_remote_get($url);

            if(wp_remote_retrieve_response_code($price_page) != 200) {
                return 'N/A';
            }

            $price_raw = wp_remote_retrieve_body($price_page);

            $price = str_replace("document.write('",'',$price_raw);
            $price = str_replace("');",'',$price);
            $price = str_replace("GBP",'',$price);
            $price = str_replace("USD",'',$price);

            set_transient( "$slug", "$price", DAY_IN_SECONDS );

            return $price;
        }
    }
}
	
if ( ! function_exists( 'wchms_func' ) ) {
	/**
	 * Shortcode Function to call WCHMS Price
	 *
	 * @param
	 * @return
	 */

    add_shortcode('whmcs', 'whmcs_func');
    
    function whmcs_func($atts) {
    
        $whmcs_url = "https://my.vimly.uk";

        if (isset($atts['pid']) && isset($atts['bc'])) {
            $pid = $atts['pid'];
            $bc = $atts['bc'];
        
            switch($bc){
                case "1m" :
                $bc_r = "monthly";
                break;
                case "3m" :
                $bc_r = "quarterly";
                break;
                case "6m" :
                $bc_r = "semiannually";
                break;
                case "1y" :
                $bc_r = "annually";
                break;
                case "2y" :
                $bc_r = "biennially";
                break;
                case "3y" :
                $bc_r = "triennially";
                break;
            }

            $full_url = "$whmcs_url/feeds/productsinfo.php?pid=$pid&get=price&billingcycle=$bc_r";

            $slug="$pid"."$bc_r";

            $output = get_whmcs_price($slug,$full_url);

            return $output;

        } elseif (isset($atts['tld']) && isset($atts['type']) && isset($atts['reg'])) {
            $tld = "." . $atts['tld'];
            $type = $atts['type'];
            $reg = $atts['reg'];
            $reg_r = (string) str_replace("y","",$reg);
            
            $full_url = "$whmcs_url/feeds/domainprice.php?tld=$tld&type=$type&regperiod=$reg_r&format=1";

            $slug = $tld.$type.$reg_r;

            $output = get_whmcs_price($slug,$full_url);

        
            return $output;
        } else {
            $output = "NA";
            return $output;
        }
    }
        	
}

