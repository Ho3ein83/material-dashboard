<?php

$tooltip_mode = amd_get_site_option( "dashboard_tooltip", "dashboard_default" );
$isLocal = amd_is_localhost();
$isLoggedIn = is_user_logged_in();
$userObject = null;

if( $isLoggedIn ){
    $user = amd_get_current_user();
	if( $user ){
		$userObject = array(
			"ID" => $user->ID,
			"email" => $user->email,
			"username" => $user->username,
			"first_name" => $user->firstname,
			"last_name" => $user->lastname,
			"fullname" => $user->fullname,
			"phone" => $user->phone,
			"avatar" => $user->profile,
		);
	}
}

?>function send_ajax(data, doOnSuccess, doOnError, xhrF = null) {
    if(xhrF === null)
        xhrF = () => new window.XMLHttpRequest();
    var data_set = {
        xhr: xhrF,
        url: '<?php echo get_site_url() . "/wp-admin/admin-ajax.php"; ?>',
        type: 'POST',
        dataType: 'JSON',
        data: data,
        success: doOnSuccess,
        error: doOnError
    }
    return $.ajax(data_set);
}

function send_ajax_opt(options) {
    var xhrF = () => new window.XMLHttpRequest();
    return $.ajax(Object.assign({
        xhr: xhrF,
        url: '<?php echo get_site_url() . "/wp-admin/admin-ajax.php"; ?>',
        type: 'POST',
        data: {},
        success: () => null,
        error: () => null
    }, options));
}

<?php
$strings = apply_filters( "amd_get_front_strings", [] );
?>
var _hb_strings = {
	<?php foreach( $strings as $id => $string ): ?>
    '<?php echo esc_js( $id ); ?>': `<?php echo esc_html( $string ); ?>`,
	<?php endforeach; ?>
}

<?php

global /** @var AMDIcon $amdIcon */
$amdIcon;

$amdIcon->initIconPack();

$pack_id = $amdIcon->getCurrentIconPack();

$pack = $amdIcon->getIconPack( $pack_id );

?>
var _hb_icons = {
	<?php
    if( !empty( $pack ) ){
        foreach( $pack["icons"] as $icon_id => $icon ):
    ?>
    '<?php echo esc_js( $icon_id ); ?>': `<?php echo $amdIcon->getIcon( $pack_id, $icon_id ); ?>`,
	<?php endforeach; } ?>
}

function _t(id) {
    var suffix = "";
    if(id.endsWith("_td")) {
        suffix = "...";
        id = id.replaceAll("_td", "");
    }
    else if(id.endsWith("_?")) {
        suffix = _hb_strings["_question_mark_"];
        id = id.replaceAll("_?", "");
    }
    if(typeof _hb_strings[id] === "undefined") return "";
    return _hb_strings[id] + suffix;
}

function _n(id, number, format=false) {
    var _format = x => x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return _t(number > 1 ? `plural:${id}` : `single:${id}`).replace("%s", format ? _format(number) : number);
}

function _i(id) {
    return typeof _hb_icons[id] === "undefined" ? "" : _hb_icons[id];
}

var amd_conf = {
    ajax: {
        public: "public_amd_ajax_handler",
        private: "amd_ajax_handler",
        dashboard: "amd_dash_ajax_handler",
        api: "_api_handler"
    },
    api_url: `<?php echo amd_get_api_url(); ?>`,
    login_url: `<?php echo amd_get_login_page(); ?>`,
    dashboard_url: `<?php echo amd_get_dashboard_page(); ?>`,
    title_separator: `<?php echo apply_filters( "amd_title_separator", "»" ); ?>`,
    tooltip_mode: `<?php echo sanitize_text_field( $tooltip_mode ); ?>`,
    forms: {
        special_keys: ["Control", "Shift", "Backspace", "CapsLock", "NumLock", "Tab", "Meta", "Escape", "Home", "End", "PageUp", "PageDown", "ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight"],
        getAllowedKeys: pattern => {
            if(pattern.regex("^%.*%$")) {
                switch(pattern.trimChar("%").toLowerCase()) {
                    case "email":
                        return ".*";
                    case "phone":
                        return "[0-9]";
                }
            }
            return pattern;
        },
        isSpecialKey: key => {
            return amd_conf.forms.special_keys.includes(key);
        }
    },
    locale: `<?php echo get_locale(); ?>`,
    isLocal: <?php echo $isLocal ? "true" : "false"; ?>,
    isLoggedIn: <?php echo $isLoggedIn ? "true" : "false"; ?>,
    getUser: () => {
        return Object.assign({}, <?php echo json_encode( $userObject ); ?>);
    }
}
/* -- KEEP THE BLANK LINE BELOW -- */

