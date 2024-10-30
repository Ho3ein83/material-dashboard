<?php

/** @var AMDDashboard $amdDashboard */
$amdDashboard = null;

class AMDDashboard{

	/**
	 * Dashboard home page cards
	 * @var array
	 * @since 1.0.0
	 */
	protected $cards;

	/**
	 * Menu items, navbar items, quick options, etc.
	 * @var array
	 * @since 1.0.0
	 */
	protected $menu;

	/**
	 * Lazy loading pages
	 * @var array
	 * @since 1.0.0
	 */
	protected $pages;

	/**
	 * Dashboard core
	 */
	public function __construct(){

		$this->cards = [];
		$this->menu = [];
		$this->pages = [];

		# Initialize core
		self::init();

	}

	/**
	 * Initialize core
	 * @return void
	 * @since 1.0.0
	 */
	public function init(){

		# Initialize hooks
		self::initHooks();

	}

	/**
	 * Initialize hooks
	 * @return void
	 * @since 1.0.0
	 */
	public function initHooks(){

		# Initialize dashboard
		add_action( "amd_dashboard_init", [ $this, "initDashboard" ], 1 );

		# Register lazy-loading page
		add_action( "amd_register_lazy_page", function( $id, $data ){

			global /** @var AMDDashboard $amdDashboard */
			$amdDashboard;

			$amdDashboard->registerPage( $id, $data );

		}, 10, 2 );

		# Register lazy-loading page (simple method)
		add_action( "amd_register_lazy_page_simple", function( $id, $title, $path, $icon ){

			global /** @var AMDDashboard $amdDashboard */
			$amdDashboard;

			$amdDashboard->registerPageSimple( $id, $title, $path, $icon );

		}, 10, 4 );

        # Register validation page
        $this->registerPageSimple( "__account_validation", __( "Account validation", "material-dashboard" ), AMD_DASHBOARD . "/validation.php", "security_warning" );

        # Disallow messages for premium notifications in special pages
        add_filter( "adp_disallowed_pages_for_displaying_messages", function( $black_list ){
            $black_list[] = "__account_validation";
            return $black_list;
        } );

	}

	/**
	 * Initialize dashboard when client visits dashboard and login page
	 * @return void
	 * @since 1.0.0
	 */
	public function initDashboard(){



	}

	/**
	 * Ajax handler
	 *
	 * @param array $r
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function ajax( $r ){

		require_once( __DIR__ . '/ajax.php' );

		if( function_exists( 'amd_hbdash_ajax_handler' ) )
			amd_hbdash_ajax_handler( $r );

	}

	/**
	 * Register
	 *
	 * @param array $data
	 * Card data
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function registerCard( $data ){

		foreach( $data as $id => $card )
			$this->cards[$id] = $card;

	}

	/**
	 * Get registered cards
	 * @return array
	 * @since 1.0.0
	 */
	public function getCards( $filter = null, $sort = false ){
		if( empty( $filter ) )
			return $this->cards;

		$cards = [];
		if( is_string( $filter ) ){
			$filter = function( $id, $data ) use ( $filter ){
				if( !empty( $data["type"] ) AND in_array( $data["type"], explode( ",", $filter ) ) )
					return true;

				return false;
			};
		}
		else if( empty( $filter ) ){
			$filter = "__return_true";
		}
		if( !is_callable( $filter ) )
			return $cards;
		foreach( $this->cards as $id => $card ){
			$v = call_user_func( $filter, $id, $card );
			if( $v )
				$cards[$id] = $card;
		}

		if( $sort ){
			usort( $cards, function( $a, $b ){
				return ( $a['priority'] ?? 10 ) - ( $b['priority'] ?? 10 );
			} );
		}

		return $cards;

	}

	/**
	 * Add menu item
	 *
	 * @param string $id
	 * Item ID
	 * @param array $data
	 * Item data
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function addMenuItem( $id, $data ){

		foreach( $data as $item_id => $item_value )
			$this->menu[$id][$item_id] = $item_value;

	}

	public function editMenuItem( $id, $data ){

		if( empty( $this->menu["sidebar_menu"][$id] ) )
			return;

		if( !empty( $this->menu["sidebar_menu"] ) ){
			foreach( $data as $item_id => $item_value )
				$this->menu["sidebar_menu"][$id][$item_id] = $item_value;
		}

	}

	/**
	 * Set navbar item
	 *
	 * @param string $side
	 * Item position: "left" | "right"
	 * @param array $data
	 * Item data
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function addNavItem( $side, $data ){

		foreach( $data as $id => $item ){
			$this->menu["navbar"][$side][$id] = $item;
		}

	}

	/**
	 * Get navbar item
	 *
	 * @param null|string|callable $filter
	 * Navbar filter. Pass null to get all items, pass string to get only the item with that ID or pass a callable to custom filtering.
	 * <br>Custom filtering callable has two callable, e.g:
	 * <code>$filters = function( $side, $data ){ ... }</code>
	 * `$side` is navbar item position (left or right) and `$data` is navbar data array
	 *
	 * @return array|mixed
	 * @since 1.0.0
	 */
	public function getNav( $filter = null ){
		$_m = $this->menu["navbar"] ?? [];
		if( empty( $filter ) OR empty( $_m ) )
			return $_m;

		$items = [];
		if( is_string( $filter ) ){
			$filter = function( $side ) use ( $filter ){
				return $side == $filter;
			};
		}
		if( !is_callable( $filter ) )
			return $items;
		foreach( $_m as $side => $item ){
			foreach( $item as $id => $data ){
				$v = call_user_func( $filter, $side, $data );
				$icon = $data["icon"] ?? "";
				if( $v ){
					$items[$id] = $data;
					if( !empty( $icon ) )
						$items[$id]["icon"] = amd_icon( $icon );
				}
			}
		}

		return $items;

	}

	/**
	 * Get menu items
	 *
	 * @param string $id
	 * Menu ID
	 *
	 * @return array
	 * Menu items array if ID exists, otherwise returns empty array
	 * @since 1.0.0
	 */
	public function getMenu( $id, $void = null, $sort = true ){

		$menu = $this->menu[$id] ?? [];

		foreach( $menu as $_id => $data ){
			$_void = $data["void"] ?? null;
			$icon = $data["icon"] ?? "";
			$menu[$_id]["id"] = $_id;
			if( !empty( $icon ) )
				$menu[$_id]["icon"] = amd_icon( $icon );

			if( $id !== "quick_options" ){
				if( ( !empty( $void ) AND !empty( $_void ) AND $_void == $void ) OR ( empty( $void ) AND $_void == "home" ) )
					$menu[$_id]["active"] = true;
			}
		}

		if( $sort ){
			usort( $menu, function( $a, $b ){
				return ( $a['priority'] ?? 10 ) - ( $b['priority'] ?? 10 );
			} );
		}

		return $menu;

	}

	/**
	 * Register new page
	 *
	 * @param string $page_id
	 * Page ID
	 * @param array $data
	 * Page data
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function registerPage( $page_id, $data ){

		$this->pages[$page_id] = $data;

	}

	/**
	 * Register new page
	 *
	 * @param string $page_id
	 * Page ID
	 * @param string $title
	 * Page title
	 * @param string $path
	 * Page PHP file path
	 * @param string $icon
	 * Page icon
	 *
	 * @return void
	 * @see AMDDashboard::registerPage()
	 * @since 1.0.0
	 */
	public function registerPageSimple( $page_id, $title, $path, $icon ){

		self::registerPage( $page_id, array(
			"title" => $title,
			"path" => $path,
			"icon" => $icon
		) );

	}

	/**
	 * Get registered page data
	 *
	 * @param string $void
	 * Page ID
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function lazyPage( $void ){

		/**
		 * Initialize getting page content
		 * @since 1.0.1
		 */
		do_action( "amd_init_dashboard_page" );

		do_action( "amd_dashboard_init" );

		$title = esc_html__( "Dashboard", "material-dashboard" );
		$path = AMD_DASHBOARD . "/pages/page-$void.php";
		$callable = null;

		if( !empty( $this->pages[$void] ) ){
			$title = $this->pages[$void]["title"] ?? $title;
			$path = $this->pages[$void]["path"] ?? "";
			$callable = $this->pages[$void]["callable"] ?? null;
		}

		if( !file_exists( $path ) AND !is_callable( $callable ) ){
			$title = esc_html__( "Page not found", "material-dashboard" );
			$path = amd_get_default( "dashboard_404" );
		}

		$redirect = apply_filters( "amd_page_redirect_$void", null );
		$redirect = apply_filters( "amd_page_redirect", $redirect, $void );

		return array(
			"title" => $title,
			"path" => $path,
			"callable" => $callable,
			"redirect" => $redirect
		);

	}

	/**
	 * Use `require_once` function without allowing it to change previous declared variables
	 *
	 * @param string $path
	 * PHP file path
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function requireOnce( $path ){

		require_once( $path );

	}

	/**
	 * Get page data for lazy-load
	 *
	 * @param string $void
	 * Page ID
	 * @param array $extra
	 * Extra data for hooks
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getLazyPage( $void, $extra = [] ){

		$__resp__ = $this->lazyPage( $void );
		$__title__ = $__resp__["title"];
		$__page__ = $__resp__["path"];
		$__callable__ = $__resp__["callable"];
		$__redirect__ = $__resp__["redirect"] ?? null;

		if( $__page__ AND file_exists( $__page__ ) ){
			ob_start();

			# Content actions
			do_action( "amd_before_pages", $void );
			do_action( "amd_before_page_$void" );
			do_action( "amd_before_lazy_page_$void" );
			do_action( "amd_before_lazy_load", $extra );

			$display = apply_filters( "amd_display_lazy_page", true );
			if( $display )
				self::requireOnce( $__page__ );

			# Content actions
			do_action( "amd_after_pages", $void );
			do_action( "amd_after_page_$void" );
			do_action( "amd_after_lazy_page_$void" );
			do_action( "amd_after_lazy_load", $extra );

			$__html__ = apply_filters( "amd_override_page_{$void}_content", ob_get_clean() );
		}
		else if( is_callable( $__callable__ ) ){

			ob_start();

			# Content actions
			do_action( "amd_before_pages", $void );
			do_action( "amd_before_pages_callback" );
			do_action( "amd_before_page_$void" );
			do_action( "amd_before_page_callback_$void" );
			do_action( "amd_before_lazy_page_$void" );
			do_action( "amd_before_lazy_load", $extra );

			call_user_func( $__callable__, $void, $__resp__ );

			# Content actions
			do_action( "amd_after_pages", $void );
			do_action( "amd_after_pages_callback" );
			do_action( "amd_after_page_$void" );
			do_action( "amd_after_page_callback_$void" );
			do_action( "amd_after_lazy_page_$void" );
			do_action( "amd_after_lazy_load", $extra );

			$__html__ = apply_filters( "amd_override_page_{$void}_content", ob_get_clean() );

		}
		else{
			$__title__ = esc_html__( "Unavailable", "material-dashboard" );
			$__html__ = amd_hbdash_ajax_fail_message();
		}

		if( $__redirect__ ){
			$title = apply_filters( "amd_redirected_page_title", esc_html__( "Dashboard", "material-dashboard" ) );
			$html = "";
		}

		return array(
			"title" => $__title__,
			"content" => $__html__,
			"redirect" => $__redirect__
		);

	}

	/**
	 * Get page data
	 *
	 * @param string $void
	 * Page ID
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getDashboardPage( $void ){

		$resp = $this->lazyPage( $void );
		$title = $resp["title"];
		$page = $resp["path"];
		$callable = $resp["callable"];
		$redirect = $resp["redirect"] ?? null;
		$turtle = $resp["turtle"] ?? null;

		$html = "";
		if( file_exists( $page ) ){
			ob_start();

			# Content actions
			do_action( "amd_before_pages" );
			do_action( "amd_before_pages_content" );
			do_action( "amd_before_page_$void" );
			do_action( "amd_before_page_content_$void" );

			require_once( $page );

			# Content actions
			do_action( "amd_after_pages" );
			do_action( "amd_after_pages_content" );
			do_action( "amd_after_page_$void" );
			do_action( "amd_after_page_content_$void" );

			$html = apply_filters( "amd_override_page_{$void}_content", ob_get_clean() );
		}
		else if( is_callable( $callable ) ){

			ob_start();

			# Content actions
			do_action( "amd_before_pages" );
			do_action( "amd_before_pages_callback" );
			do_action( "amd_before_page_$void" );
			do_action( "amd_before_page_callback_$void" );

			$result = call_user_func( $callable, $void, $resp );
			if( is_array( $result ) ){
				if( isset( $result["turtle"] ) )
					$turtle = $result["turtle"];
			}

			# Content actions
			do_action( "amd_after_pages" );
			do_action( "amd_after_pages_callback" );
			do_action( "amd_after_page_$void" );
			do_action( "amd_after_page_callback_$void" );

			$html = apply_filters( "amd_override_page_{$void}_content", ob_get_clean() );

		}
		else{
			$title = esc_html__( "Unavailable", "material-dashboard" );
			$html = amd_hbdash_ajax_fail_message();
		}

		if( $redirect ){
			$title = apply_filters( "amd_redirected_page_title", esc_html__( "Dashboard", "material-dashboard" ) );
			$html = "";
		}

		return array(
			"title" => $title,
			"content" => $html,
			"turtle" => $turtle,
			"redirect" => $redirect
		);

	}

	public function getDashboardPageObject( $void ){

		return $this->pages[$void] ?? null;

	}

}