<?php
class Token_Ad_Add_Area {

	public $page_plugin;

	public $widgets;

	public $aabd;

	public $select_widgets;

	public $page_type_all;

	public $iconPageType;

	public $page_area_all;

	public $request_uri;

	public $operation_all;

	private $option_name = 'Token_Ad';

	private $nameAd = 'tokenAd_';

	public function __construct()
    {
		global $wpdb;

		$this->page_plugin = $this->getPluginStatus();

		$this->widgets = array();

    	$this->select_widgets = array();

    	$this->operation_all = array(
    	    'remove',
            'close',
            'preview',
            'save',
        );

        $this->page_type_all = array(
            'post',
            'page',
            'main',
            'category',
            'archive',
            'search',
        );

        $this->iconPageType = array(
            'post' => 'dashicons-admin-post' ,
            'page' => 'dashicons-admin-page',
            'main' => 'dashicons-admin-home',
            'category' => 'dashicons-tag',
            'archive' => 'dashicons-media-archive',
            'search' => 'dashicons-search',
        );

    	$this->page_area_all = array(
    	    $this->nameAd . 'wp_head',
            $this->nameAd . 'wp_footer',
            $this->nameAd . 'get_footer',
            $this->nameAd . 'loop_start',
            $this->nameAd . 'loop_end',
            $this->nameAd . 'comment_form_before',
            $this->nameAd . 'comment_form_after',
            $this->nameAd . 'dynamic_sidebar_before',
            $this->nameAd . 'dynamic_sidebar_after',
            $this->nameAd . 'content_after',
            $this->nameAd . 'content_before',
            $this->nameAd . 'the_excerpt',
            $this->nameAd . 'widget_text_content',
            $this->nameAd . 'widget_custom_html_content',
        );
    	
		$request = $_SERVER["REQUEST_URI"];

    	if (! empty($request)) {
	    	$this->request_uri = esc_url($request);
    	} else {
			$this->request_uri = '/';
    	}

    	$post_operation = !empty($_POST['operation']) ? sanitize_text_field($_POST['operation']) : false;

    	if (! empty($post_operation) && in_array($post_operation, $this->operation_all)) {

    		if (! empty($_POST['widget_id']) && ! empty($_POST['action_area']) && ! empty($_POST['type_post'])) {
    			$id_area_post = true;
    			$post_widget_id = intval($_POST['widget_id']);

    			if (! $post_widget_id) {
    				$id_area_post = false;
    			}

				$post_action_area = sanitize_text_field($_POST['action_area']);

				if (! in_array($post_action_area, $this->page_area_all)) {
    				$id_area_post = false;
				}

				$post_type_post = sanitize_text_field($_POST['type_post']);

				if (! in_array($post_type_post, $this->page_type_all)) {
    				$id_area_post = false;
				}

    		} else {
    			$id_area_post = false;
    		}
    	} else {
    		$post_operation = false;
    	}

		switch ($post_operation) {
			case 'remove':
	    		if (! empty($id_area_post)) {
    				$this->remove_ad($post_action_area, $post_type_post, $post_widget_id);
	    		}
			break;

			case 'close':
	    		if (! empty($id_area_post)) {
    				$this->remove_ad($post_action_area, $post_type_post, $post_widget_id, '-preview-tokenAd');
	    		}
			break;

			case 'preview':
    			if (! empty($id_area_post)) {
		    		$this->add_widget_array($post_widget_id, $post_action_area, $post_type_post);
		    	}
			break;

			case 'save':
				$previews = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s", '%-preview-tokenAd%'));

				foreach ($previews as $key => $double) {
					$wpdb->query( $wpdb->prepare("UPDATE $wpdb->options SET option_name = REPLACE (option_name, '-preview-tokenAd', '') WHERE option_name = %s", sanitize_text_field($double)));
				}

    			$plugin_status = $this->getPluginStatus();

    			if (! empty($plugin_status)) {
		            $wpdb->delete($wpdb->options, array( 'option_name' => 'edit_area'));
		        }
			break;
		}
	}

	private function getPluginStatus()
    {
		global $wpdb;

		$edit_area = $wpdb->get_row($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s", 'edit_area'));

		return (! empty($edit_area)) ? true : false;
	}

	private function addHeadPanel()
    {
		global $wpdb;

		$type_post = $this->getTypePage();
		$nal_ad = $wpdb->get_row($wpdb->prepare("SELECT count(*) as cnt FROM $wpdb->options WHERE option_value LIKE %s AND option_name = %s", '%' . $type_post . '-%', 'obhod_tokenAd'));

		if ($this->getPluginStatus() === false && $nal_ad->cnt == 0) {
			$this->widgets = array();
		} else {
			$token = get_option( $this->option_name . '_key' );
			$request = wp_remote_get('http://wp_plug.adnow.com/wp_aadb.php?token=' . $token);

			if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
				$json = wp_remote_retrieve_body( $request );
			} else {
				set_error_handler( array($this, "warning_handler"), E_WARNING );

				$json = file_get_contents('http://wp_plug.adnow.com/wp_aadb.php?token=' . $token);

				restore_error_handler();
			}

			if (! empty($json)) {
				$widgets = json_decode($json, true);

				if (! empty($widgets['widget'])) {
			        $this->widgets = $widgets['widget'];
			        $this->aabd = $widgets['aadb'];

			        if (is_array($this->widgets)) {

			        	foreach ($this->widgets as $key => $value) {
			        		$this->select_widgets[$key] = $value['title'];
			        	}
			        }
				} else {
					$this->widgets = array();
				}
			}
		}
		
		$headpanel = '';

		if ($this->is_user_role('administrator') && $this->getPluginStatus() === true) {

            $headpanel .= '
                <div_token_ad class="b-feedback-sidebar">
                    <form id="form_save" method="post" action="'.$this->request_uri.'">';

            foreach ($this->page_type_all as $type) {
                $headpanel .= '
                    <div_token_ad class="b-feedback-sidebar--item">
                        <span_token_ad class="dashicons ' . $this->iconPageType[$type] . '"></span_token_ad>
                        <span_token_ad class="b-feedback-sidebar--text">' . $this->get_home_page($type) . '</span_token_ad>
                    </div_token_ad>';
            }

            $headpanel .= '
                <input name="operation" type="hidden" value="save">
                <div_token_ad class="b-send-site" onclick="document.getElementById(\'form_save\').submit()" id="all_save">
                    <span_token_ad class="b-send-site--icon b-icon---sendSite"></span_token_ad>
                    <span_token_ad class="b-send-site--text">Save & Exit</span_token_ad>
                </div_token_ad>';
            $headpanel .= '</form>';
            $headpanel .= '</div_token_ad>';

            $activeAreas = $this->getActiveAreas();

            $headpanel .= '
                <div_token_ad class="b-scroll-sidebar-left b-scroll-sidebar---show">
                    <div_token_ad class="b-scroll-sidebar--inside">';
                        foreach ($activeAreas as $areaName => $idAds) {
                            $headpanel .= '
                                <div_token_ad class="b-scroll-sidebar--item">
                                    <a href="#' . $areaName . '" class="activeAreas">' . $this->select_widgets[$idAds] . '</a>
                                </div_token_ad>';
                        }

            $headpanel .= '</div_token_ad>
                </div_token_ad>';
        }

		return $headpanel;
	}

	private function getRecheck($action_area)
    {
		global $wpdb;

		$type_post = $this->getTypePage();

		$count_add_page = $wpdb->get_row($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s", $action_area . '_add_' . $type_post));

		if (! isset($count_add_page)) {
			$wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->options ( option_name, option_value, autoload ) VALUES ( %s, %s, %s )", $action_area . '_add_' . $type_post, 'yes', 'no' ) );
		}

		return $count_add_page;
	}

	private function getActiveAreas()
    {
        global $wpdb;

        $type_post = $this->getTypePage();

        $visionArea = array();

        foreach ($this->page_area_all as $areas) {
            $getVisionArea = $wpdb->get_row($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s", $areas . '-' . $type_post));

            if (! empty($getVisionArea)) {
                $visionArea[$areas] = $getVisionArea->option_value;
            }
        }

        return $visionArea;
    }

    private function getCode($action_area, $size='big')
    {
        global $wpdb;

        $adnblock = '';

        $type_post = $this->getTypePage();

        $vision = $wpdb->get_row($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s", $action_area . '-' . $type_post));

        $vision_preview = $wpdb->get_row($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s", $action_area . '-' . $type_post . '-preview-tokenAd'));

        if (! empty($vision)) {
            $vision_arr = esc_html($vision->option_value);

            if (! empty($this->widgets[$vision_arr])) {

                $adnblock = '
				<token_ad class="top_index_block_adnow" id="' . $action_area . '">
					<form id="form_' . $action_area . '" method="post" action="' . $this->request_uri . '#' . $action_area . '">';

                if ($this->is_user_role('administrator') && $this->getPluginStatus() === true) {
                    $adnblock .= '<input name="widget_id" type="hidden" value="' . $vision_arr . '">
						<input name="action_area" type="hidden" value="' . $action_area . '">
						<input name="type_post" type="hidden" value="' . $type_post . '">
						<input name="operation" type="hidden" value="remove">
							<button_token_ad onclick="document.getElementById(\'form_' . $action_area . '\').submit()" class="add_widget_plus_content">
								<span_token_ad class="remove_widget">
								    <span class="dashicons dashicons-trash"></span>
								    Remove widgets
								</span_token_ad>
							</button_token_ad>';
                }

                $adnblock .= '
						<div class="prev" data-widget="' . $vision_arr . '">' . base64_decode($this->widgets[$vision_arr]['code']) . '</div>
					</form>
				</token_ad>';
            }
        } elseif (! empty($vision_preview)) {
            $vision_arr = esc_html($vision_preview->option_value);

            if (! empty($this->widgets[$vision_arr])) {

                if ($this->is_user_role('administrator') && $this->getPluginStatus() === true) {
                    $adnblock = '
				<token_ad class="top_index_block_adnow" id="' . $action_area . '">
					<form id="form_' . $action_area . '" method="post" action="' . $this->request_uri . '#' . $action_area . '">';
                    $adnblock .= '<input name="widget_id" type="hidden" value="' . $vision_arr . '">
						<input name="action_area" type="hidden" value="' . $action_area . '">
						<input name="type_post" type="hidden" value="' . $type_post . '">
						<input name="operation" type="hidden" value="close">';
                    $adnblock .= '
                    <div_token_ad class="b-scroll-sidebar-prew b-scroll-sidebar---show">
                        <div_token_ad class="b-scroll-sidebar--inside">';

                    $adnblock .= '
                    <div_token_ad class="b-scroll-sidebar--item">
                        <button_token_ad onclick="document.getElementById(\'form_' . $action_area . '\').submit()" class="add_widget_plus_content">
                            <span_token_ad class="remove_widget close_prev">Close view widget</span_token_ad>
                        </button_token_ad>
                    </div_token_ad>';

                    $adnblock .= '
                        </div_token_ad>
                    </div_token_ad>';


                    $adnblock .= '
						<div_token_ad class="prev view_prev" data-widget="' . $vision_arr . '">' . base64_decode($this->widgets[$vision_arr]['code']) . '</div_token_ad>
					</form>
				</token_ad>';
                }
            }
        } else {
            if ($this->is_user_role('administrator')  and $this->getPluginStatus() === true) {
                $select_in = $wpdb->get_row($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s", 'obhod_tokenAd'));

                $ids = ! empty($select_in->option_value) ? explode(",", $select_in->option_value) : array();
                $ids = array_diff($ids, array(''));

                $adnblock = '
				<token_ad class="top_index_block_adnow"  id="' . $action_area . '">
					<form id="form_' . $action_area . '"  method="post" action="' . $this->request_uri . '#' . $action_area . '">
						<div_token_ad class="adnow_widget_block adn_' . $size . '">
							<div_token_ad class="adn_form">';

                $adnblock .= '
                    <div_token_ad class="b-scroll-sidebar b-scroll-sidebar---show">
                        <div_token_ad class="b-scroll-sidebar--inside">';

                        foreach ($this->select_widgets as $key => $value) {

                            if (! in_array($type_post . '-' . $key, $ids)) {
                                $adnblock .= '<div_token_ad class="b-scroll-sidebar--item"><a class="footer__dropdown-item i-color-black i-link-no-deco" onclick = "document.getElementById(\'widget_id_' . $action_area . '\').value=\'' . $key . '\'; document.getElementById(\'form_' . $action_area . '\').submit()">' . $value . '</a></div_token_ad>';
                            }
                        }

                $adnblock .= '
                        </div_token_ad>
                    </div_token_ad>';

                $adnblock .= ' 
								<input name="action_area" type="hidden" value="' . $action_area . '">
								<input name="widget_id" id="widget_id_' . $action_area . '" data-id="' . $action_area . '" type="hidden" value="">
								<input name="type_post" type="hidden" value="' . $type_post . '">
								<input name="operation" type="hidden" value="preview">
							</div_token_ad>
						</div_token_ad>
					</form>
				</token_ad>';
            }
        }

        return $adnblock;
    }

	private function getTypePage()
    {
		if (is_front_page()) {
			$type_post = 'main';
		} elseif (is_search()) {
			$type_post = 'search';
		} elseif (is_page()) {
			$type_post = 'page';
		} elseif (is_single()) {
			$type_post = 'post';
		} elseif (is_category()) {
			$type_post = 'category';
		} elseif (is_archive()) {
			$type_post = 'archive';
		} else {
			$type_post = 'other';
		}

        return $type_post;
	}	

    private function get_home_page($param)
    {
        global $wpdb;

        global $cache_page_secret;

        $type_post_active = $this->getTypePage();
        $type_page = $param;
        $adv_active = $param == $type_post_active ? 'adn_active' : '';

        $home_page = home_url();

        if (! empty($param)) {

            switch ($param) {
                case 'page':
                    $post_guid = $wpdb->get_col($wpdb->prepare("SELECT id FROM $wpdb->posts WHERE post_status = %s AND post_type = %s ORDER BY id DESC LIMIT 1", 'publish', 'page')); 
                    $home_page = !empty($post_guid[0]) ? get_site_url() . '/?p=' . $post_guid[0] : get_site_url() . '/';
                break;

                case 'post':
                    $post_guid = $wpdb->get_col($wpdb->prepare("SELECT id FROM $wpdb->posts WHERE post_status = %s AND post_type = %s ORDER BY id DESC  LIMIT 1", 'publish', 'post')); 
                    $home_page = !empty($post_guid[0]) ? get_site_url() . '/?p=' . $post_guid[0] : get_site_url() . '/';
                break;

                case 'attachment':
                    $post_guid = $wpdb->get_col($wpdb->prepare("SELECT id FROM $wpdb->posts WHERE post_status = %s AND post_type = %s ORDER BY id DESC  LIMIT 1", 'publish', 'attachment')); 
                    $home_page = !empty($post_guid[0]) ? get_site_url() . '/?p=' . $post_guid[0] : get_site_url() . '/';
                break;

                case 'category':
	                $categories = get_the_category();
					if ( ! empty( $categories ) ) {
						$home_page = esc_url(get_category_link($categories[0]->term_id));
					}
                break;

                case 'archive':
					$string = wp_get_archives('type=monthly&limit=1&echo=0&format=html');
					$regexp = "<a\s[^>]*(?:href=[\'\"])(\"??)([^\"\' >]*?)\\1[^>]*>(.*)<\/a>";
					if(preg_match_all("/$regexp/siU", $string, $matches, PREG_SET_ORDER)) {
					    $home_page =  $matches[0][2];
					}
                break;

                case 'search':
					$home_page = home_url() . '/?s=search';
                break;
            }
        }

		if (! empty($cache_page_secret)) {
		    $home_page = add_query_arg( 'donotcachepage', $cache_page_secret,  $home_page );
		}

        return '<a class="adn_button ' . $adv_active . '" href="' . esc_url($home_page) . '">' . ucfirst($type_page) . '</a>';
    }

    private function add_widget_array($id_widget, $action_area, $type_post)
    {
        global $wpdb;

        $backup = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_name = %s", $action_area . '-' . $type_post . '-preview-tokenAd'));

        if (count($backup) == 0) {
            $inc = $wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->options ( option_name, option_value, autoload ) VALUES ( %s, %s, %s )", $action_area . '-' . $type_post . '-preview-tokenAd', $id_widget, 'no' ) );
            
            $this->obhod($type_post . '-' . $id_widget, 'add');
        }

        return $inc;
    }

    private function obhod($id_widget, $action)
    {
        global $wpdb;

        $obhod = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_name = %s", 'obhod_tokenAd'));

        if (count($obhod) == 0) {
            $wpdb->query($wpdb->prepare("INSERT INTO $wpdb->options ( option_name, option_value, autoload ) VALUES ( %s, %s, %s )", 'obhod_tokenAd', '', 'no'));
        }

        switch ($action) {
            case 'add':
            $wpdb->query($wpdb->prepare("UPDATE $wpdb->options SET option_value = CONCAT(option_value, %s) WHERE option_name='obhod_tokenAd'", $id_widget . ','));
            break;

            case 'remove':
            $wpdb->query($wpdb->prepare("UPDATE $wpdb->options SET option_value = REPLACE(option_value, %s, '')  WHERE option_name='obhod_tokenAd'", $id_widget . ','));
            break;
        }
    }

    private function remove_ad($action_area, $type_post, $id_widget, $preview = '')
    {
        global $wpdb;

        $nal = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_name = %s", $action_area . '-' . $type_post . $preview));

        if (! empty($nal)) {
            $wpdb->delete($wpdb->options, array( 'option_name' => $action_area . '-' . $type_post . $preview));
            $this->obhod($type_post . '-' . $id_widget, 'remove');
        }
    }

	public function wp_head_area()
    {
		echo $this->addHeadPanel();
	}

	public function wp_footer_area()
    {
		echo $this->getCode($this->nameAd . 'wp_footer');
	}

	public function get_footer_area()
    {
		echo $this->getCode($this->nameAd . 'get_footer');
	}

	public function comment_form_before_area()
    {
		echo $this->getCode($this->nameAd . 'comment_form_before');
	}
	
	public function comment_form_after_area()
    {
		echo $this->getCode($this->nameAd . 'comment_form_after');
	}
		
	public function dynamic_sidebar_before_area()
    {
		$recheck = $this->getRecheck($this->nameAd . 'dynamic_sidebar_before');

		if (! isset($recheck)) {
			echo $this->getCode($this->nameAd . 'dynamic_sidebar_before', 'small');
		}
	}	

	public function dynamic_sidebar_after_area()
    {
		$recheck = $this->getRecheck($this->nameAd . 'dynamic_sidebar_after');

		if (! isset($recheck)) {
			echo $this->getCode($this->nameAd . 'dynamic_sidebar_after', 'small');
		}
	}
	
	public function content_after_area($content)
    {
		$recheck = $this->getRecheck($this->nameAd . 'content_after');

		if (! isset($recheck)) {
			$adnblock = $this->getCode($this->nameAd . 'content_after');
			$content = $content . $adnblock;
		}

		return $content;
	}

	public function content_before_area($content)
    {
		$recheck = $this->getRecheck($this->nameAd . 'content_before');

		if (! isset($recheck)) {
			$adnblock = $this->getCode($this->nameAd . 'content_before');
			$content = $adnblock . $content;
		}

		return $content;
	}

	/*
	 * Content of the displayed post excerpt (for the first post)
	 */
	public function excerpt_after_area($content)
    {
		$recheck = $this->getRecheck($this->nameAd . 'the_excerpt');

		if (! isset($recheck)) {
			$adnblock = $this->getCode($this->nameAd . 'the_excerpt');
			$content = $content . $adnblock;
		}

		return $content;
	}

    /*
     * Content of the Custom HTML widget
     */
	public function widget_custom_html_content_area($content)
    {
		$recheck = $this->getRecheck($this->nameAd . 'widget_custom_html_content');

		if (! isset($recheck)) {
			$adnblock = $this->getCode($this->nameAd . 'widget_custom_html_content');
			$content = $content . $adnblock;
		}

		return $content;
	}

	/*
	 * Content of the Text widget
	 */
	public function widget_text_content_area($content)
    {
		$recheck = $this->getRecheck($this->nameAd . 'widget_text_content');

		if (! isset($recheck)) {
			$adnblock = $this->getCode($this->nameAd . 'widget_text_content');
			$content = $content . $adnblock;
		}

		return $content;
	}



	public function is_user_role($role, $user_id = null)
    {
		$user = is_numeric($user_id) ? get_userdata($user_id) : wp_get_current_user();

		if (! $user) {
            return false;
        }

		return in_array($role, (array) $user->roles);
	}

	public function empty_povt() {

		global $wpdb;

		foreach ($this->page_area_all as $key => $row) {

			foreach ($this->page_type_all as $add) {
				$wpdb->delete($wpdb->options, array( 'option_name' => $row . '_add_' . $add));
			}
		}
	}

	public function add_obhod()
    {
        $options_turn = get_option( $this->option_name . '_turn' );

        if (! empty($options_turn)) {

        	if (! empty($this->aabd)) {
				echo base64_decode($this->aabd);
        	}
        }
	}


    public function modify_admin_bar( $wp_admin_bar )
    {
        $args = array(
            'id'    => 'edit_place',
            'title' => 'Show areas for advertising',
            'href'  =>  stristr($this->request_uri, '/wp-admin') ? admin_url() . 'admin.php?page=edit_place' : admin_url() . 'admin.php?page=edit_place&url=' . $this->request_uri,
            'meta'  => array( 'class' => 'my-toolbar-page' )
        );

        $wp_admin_bar->add_node( $args );
    }

	public function warning_handler($errno, $errstr)
    {
		return false;
	}
}
