<?php
class Token_Ad_Admin {

    private $plugin_name;

    public $token;

    public $message_error = '';

    public $widgets;

    private $option_name = 'Token_Ad';

    private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->token = false;
		$this->json = '';
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/token-ad-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/token-ad-admin.js', array( 'jquery' ), $this->version, false );
	}

	public function add_options_page() {
		$this->plugin_screen_hook_suffix = add_menu_page(
			__( 'Edit place', 'token-ad' ),
			__( 'TokenAd', 'token-ad' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_options_page'),
            'dashicons-welcome-widgets-menus'
		);
	}

	public function make_menu() {
	   add_submenu_page($this->plugin_name, 'Edit place', 'Edit place', 'manage_options', 'edit_place', array( $this, 'setting_tokenad' ));
	}

	public function setting_tokenad() {
	    include_once 'partials/token-ad-setting-display.php';
	}

	public function display_options_page() {
		$this->get_json();
		include_once 'partials/token-ad-admin-display.php';
	}

	public function register_setting() {
		add_settings_section(
			$this->option_name . '_general',
			'',
			array( $this, $this->option_name . '_general_cb' ),
			$this->plugin_name
		);
		
		add_settings_section(
			$this->option_name . '_head_account_key',
			'',
			array(),
			$this->plugin_name
		);	

		add_settings_section(
			$this->option_name . '_impressions',
			'',
			array( $this, $this->option_name . '_impressions_cb' ),
			$this->plugin_name
		);

		add_settings_section(
			$this->option_name . '_turn',
			'',
			array( $this, $this->option_name . '_turn_cb' ),
			$this->plugin_name
		);

		register_setting( $this->plugin_name, $this->option_name . '_general');
		register_setting( $this->plugin_name, $this->option_name . '_key');
		register_setting( $this->plugin_name, $this->option_name . '_turn');
	}

	public function get_json() {
		$options_token = get_option( $this->option_name . '_key' );
		if(!empty($options_token)){

			$request = wp_remote_get('http://wp_plug.adnow.com/wp_aadb.php?token='.$options_token);
			if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ){
				$this->json = wp_remote_retrieve_body( $request );
			} else{
				set_error_handler(array($this, "warning_handler"), E_WARNING);
				$this->json = file_get_contents('http://wp_plug.adnow.com/wp_aadb.php?token='.$options_token);
				restore_error_handler();
			}

			$widgets_val = json_decode($this->json, true);
			if(!empty($widgets_val["msg"])){
				$this->message_error = $widgets_val["msg"];
			}
			$this->token = $options_token;
		}
	}

	public function Token_Ad_general_cb() {
		if($this->token !== false){
			$this->widgets = json_decode($this->json, true);
			$account_id = !empty($this->widgets['account']['id']) ? $this->widgets['account']['id'] : '';
			$account_email = !empty($this->widgets['account']['email']) ? $this->widgets['account']['email'] : '';
		} ?>

        <div class="card">
                <h2>Account</h2>
	            <div class="text">
			 		<p><b>Token: </b><input autocomplete="off" type="text" name="<?php echo esc_html($this->option_name)?>_key" id="<?php echo esc_html($this->option_name) ?>_key" value="<?php echo esc_html($this->token) ?>"><span class="message_error"><?php echo $this->message_error?></span></p>
				<?php if($this->token !== false and $this->message_error == '') : ?>
		            <p><b>ID: </b> <span><?php echo esc_html($account_id) ?></span></p>
			 		<p><b>E-mail: </b> <span><?php echo esc_html($account_email) ?></span></p>
                <?php else: ?>
					<input class="checkbox" autocomplete="off" type="hidden" name="<?php echo esc_html($this->option_name) . '_turn' ?>" id="<?php echo esc_html($this->option_name) . '_turn' ?>" value="before"><br>
					
				<?php endif; ?>
				</div>
			</div>
			<?php
	}


	public function Token_Ad_turn_cb(){
	 	$turn = get_option( $this->option_name . '_turn' ); ?>
	 	<?php if($this->token !== false and $this->message_error == '') : ?>
	 	<div class="card adblock">
            <h2>Antiadblock</h2>
            <div class="text">
                <div class="checkbox_cover <?php echo !empty($turn) ? 'success' : ''?>">
                	<label>
                        <input class="checkbox" type="checkbox" name="<?php echo esc_html($this->option_name) . '_turn' ?>" id="<?php echo esc_html($this->option_name) . '_turn' ?>" value="before" <?php checked( $turn, 'before' ); ?>>
                        <span class="check"><i></i></span>
                        <span class="name">Activate Adblock</span>
                    </label>
                </div>
            </div>
        </div>
		<?php
		endif;
	 }

	public function Token_Ad_impressions_cb(){
	 	if($this->token !== false and $this->message_error == ''){
		 	$impressions = !empty($this->widgets['impressions']) ? $this->widgets['impressions'] : 0;
		 	$impressions = number_format($impressions, 0, '', ' '); 
	 	} ?>
	 	<?php if($this->token !== false  and $this->message_error == '') : ?>
	 	<div class="card stats">
            <h2>Antiadblock stats for today</h2>
            <div class="text">
                <p>Impressions: <?php echo esc_html($impressions) ?></p>
            </div>
        </div>
		<?php
		endif;
	}

	public function warning_handler($errno, $errstr) { 
		$this->message_error = 'Problem retrieving data from the server!';
	}
}