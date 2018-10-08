<?php

class Token_Ad_Area {
    public function __construct(){
        global $wpdb;
        $edit_area = $wpdb->get_row($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s", 'edit_area'));
        if(!isset($edit_area)){
            $inc = $wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->options ( option_name, option_value, autoload ) VALUES ( %s, %s, %s )", 'edit_area', 'yes', 'no' ) );
        }
    }
}

$token = get_option( $this->option_name . '_key' );
if(!empty($token)){
    $json = '';
    $request = wp_remote_get('http://wp_plug.adnow.com/wp_aadb.php?token='.$token);
    if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ){
        $json = wp_remote_retrieve_body( $request );
    } else{
        set_error_handler(array($this, "warning_handler"), E_WARNING);
        $json = file_get_contents('http://wp_plug.adnow.com/wp_aadb.php?token='.$token);
        restore_error_handler();
    }

	$widgets = json_decode($json, true);
} else{
	$widgets["success"] = false;
}

function warning_handler($errno, $errstr) { 
    return false;
}

if($widgets["success"] !== false){
	$edit_area = new Token_Ad_Area;
	$url = !empty($_GET['url']) ? sanitize_text_field($_GET['url']) : home_url();
}else{
	$url = admin_url()."admin.php?page=token-ad";
}

global $cache_page_secret;
if(!empty($cache_page_secret)){
    $url = add_query_arg( 'donotcachepage', $cache_page_secret,  $url );
}

$src = '<scr'; $src .= 'ipt>document.location.href="'.esc_html($url).'"</'; $src .= 'scr'; $src .= 'ipt>';
echo $src;
exit;