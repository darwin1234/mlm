<?php

    class GHLConnectPro_Updater {
        
        
		public $cache_key;
		public $cache_duration;
        private $_version;
        private $_slug;
        private $_path;
		
		public function __construct() {
		    
			$this->cache_key = 'ghlconnectpro_updater';
			$this->cache_duration = 86400; // Cache for 24 hours
            $this->_version = GHLCONNECTPRO_VERSION;
            $this->_slug = GHLCONNECTPRO_PLUGIN_BASENAME;
            $this->_path = GHLCONNECTPRO_PATH;
        
		}
        
        // Plugin update
        public function ghlconnectpro_request() {
            
			$remote = get_transient( $this->cache_key );
            
			if( false === $remote ) {
			    
			    $license_key =get_option('ghl_connect_pro_license');
			    $license_key = empty($license_key) ? '' : urlencode($license_key);
    
            $remote_response = wp_remote_get("https://server.ibsofts.com/ghlconnectpro/ghlconnectpro-info.php?license_key=$license_key", array(
                'timeout' => 10,
                'headers' => array('Accept' => 'application/json'),
            ));
    
            if (!is_wp_error($remote_response) && 200 === wp_remote_retrieve_response_code($remote_response)) {
                $remote_body = wp_remote_retrieve_body($remote_response);
                $remote = json_decode($remote_body);
    
                if ($remote !== null) {
                    set_transient($this->cache_key, $remote, $this->cache_duration);
                } else {
                    // Handle invalid JSON response
                    $remote = false;
                }
            } else {
                // Handle WP_Error or non-200 response
                $remote = false;
            }    
		}
		 return $remote;
		}

		
		public function ghlconnectpro_update( $transient ) {

			if ( empty($transient->checked ) ) {
				return $transient;
			}

			$remote = $this->ghlconnectpro_request();
            
			// Check if $remote is an array and convert it to an object if necessary
			if (is_array($remote)) {
				$remote = (object) $remote;
			}
		
			if (
				$remote &&
				isset($remote->version) &&
				version_compare($this->_version, $remote->version, '<') &&
				isset($remote->requires) &&
				version_compare($remote->requires, get_bloginfo('version'), '<=') &&
				isset($remote->requires_php) &&
				version_compare($remote->requires_php, PHP_VERSION, '<')
			) {
				$res = new stdClass();
				$res->slug = $this->_slug;
				$res->plugin = $this->_path;
				$res->new_version = $remote->version;
				$res->tested = $remote->tested;
				$res->package = $remote->download_url;
				$transient->response[$res->plugin] = $res;
		
				// Cache update information for 24 hours
				set_transient($this->cache_key, $remote, $this->cache_duration);
			}
		
			return $transient;

		}

		
		
        public function ghlconnectpro_update_message( $plugin_info_array, $plugin_info_object ) {
            
        	if( empty( $plugin_info_array[ 'package' ] ) ) {
        		echo ' Please renew your license to update. You can change your license key in GHL Connect For Woocommerce Pro> License > Enter Your License';
        	}
        	
        }
        
    }