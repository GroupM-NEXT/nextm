<?php
$output = $el_class = $bg_image = $bg_color = $bg_image_repeat = $font_color = $padding = $margin_bottom = $css = $css_class = $full_width = $el_id = $parallax_image = $parallax = '';
$bg_styles = $inline_styles = $wrapper_class = $section_wrapper_style = $bg_layer_style = ''; // theme specific

$unique_id = uniqid();

extract(shortcode_atts(array(
    'el_class'        => '',
    'el_id'           => '',
    'bg_image'        => '',
    'bg_color'        => '',
    'bg_image_repeat' => '',
    'font_color'      => '',
    'padding'         => '',
    'margin_bottom'   => '',
    'css' => '',
    /* theme custom */
    'bg_maps'         => '',
    'bg_maps_height'  => '',
    'bg_maps_zoom'    => '',
    'bg_maps_scroll'  => '',
    'bg_maps_scroll'  => '',
    'bg_maps_scroll'  => '',
    'bg_maps_infobox' => '',
    'bg_maps_infobox_content' => '',
    'bg_parallax'     => '',
    'inertia'         => '0.2',
    // 'parallax'        => false,
    // 'parallax_image'  => false,
    'full_width'      => false,
), $atts));
$parallax_image_id = '';
$parallax_image_src = '';

wp_enqueue_style( 'js_composer_front' );
wp_enqueue_script( 'wpb_composer_front_js' );
wp_enqueue_style('js_composer_custom_css');

$el_class = $this->getExtraClass($el_class);

if (function_exists('get_row_css_class') && function_exists('vc_shortcode_custom_css_class')) {
	$css_class =  apply_filters(VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'wpb_row '.get_row_css_class().$el_class.vc_shortcode_custom_css_class($css, ' '), $this->settings['base']);
}

$style = $this->buildStyle('', '', '', $font_color, $padding, $margin_bottom);

$css_class .= ( $full_width == 'stretch_row_content_no_spaces' )? ' vc_row-no-padding' : '';
$css_class .= ( ! empty( $parallax ) )? ' vc_general vc_parallax vc_parallax-' . $parallax : '';
$css_class .= ( ! empty( $parallax ) && strpos( $parallax, 'fade' ) )? ' js-vc_parallax-o-fade' : '';
$css_class .= ( ! empty( $parallax ) && strpos( $parallax, 'fixed' ) )? ' js-vc_parallax-o-fixed' : '';

$css_class .= ( ! empty( $full_width ) )? '" data-vc-full-width="true" data-vc-full-width-init="false" ' : '';
$css_class .= ( $full_width == 'stretch_row_content' || $full_width == 'stretch_row_content_no_spaces' )? ' data-vc-stretch-content="true"' : '';

// parallax bg values
$bgSpeed = 1.5;
if ( $parallax ) {
    wp_enqueue_script( 'vc_jquery_skrollr_js' );

    $css_class .= '" data-vc-parallax="' . $bgSpeed . '" ';
}
if ( strpos( $parallax, 'fade' ) ) {
    $css_class .= ' data-vc-parallax-o-fade="on" ';
}
if ( $parallax_image ) {
    $parallax_image_id = preg_replace( '/[^\d]/', '', $parallax_image );
    $parallax_image_src = wp_get_attachment_image_src( $parallax_image_id, 'full' );
    if ( ! empty( $parallax_image_src[0] ) ) {
        $parallax_image_src = $parallax_image_src[0];
    }
    $css_class .= ' data-vc-parallax-image="' . $parallax_image_src . '';
}

// Background CSS - Parse custom styles
preg_match_all('/[*]*background[-]*[image|color|repeat|position|size]*:[^;]*;/i', $css, $background_css);
$bg_styles = (isset($background_css) && !empty($background_css)) ? str_replace("!important", "", implode('',$background_css[0])) : '';
// Margin CSS - Parse custom styles
preg_match_all('/[*]*margin[-]*[top|right|bottom|left]*:[^;]*;/i', $css, $margin_css);
$inline_styles .= (isset($margin_css) && !empty($margin_css)) ? str_replace("!important", "", implode('',$margin_css[0])) : '';
// Padding CSS - Parse custom styles
preg_match_all('/[*]*padding[-]*[top|right|bottom|left]*:[^;]*;/i', $css, $padding_css);
$inline_styles .= (isset($padding_css) && !empty($padding_css)) ? str_replace("!important", "", implode('',$padding_css[0])) : '';
// Border CSS - Parse custom styles
preg_match_all('/[*]*border[-]*[top|right|bottom|left]*:[^;]*;/i', $css, $border_css);
$inline_styles .= (isset($border_css) && !empty($border_css)) ? str_replace("!important", "", implode('',$border_css[0])) : '';

// Update default VC styles variable
if (!empty($bg_styles) || !empty($inline_styles)) {
	// Prepare the style variable	
	$style = (isset($style) && !empty($style)) ? rtrim($style, '"').' ' : 'style="'; 
	// Remove bg image styles from VC row
	if (!empty($bg_styles))	{
		$style .= 'background: none !important; background-image: none !important; background-color: inherit !important;';
	}
	// Add other inline styles
	$style .= $inline_styles .'"';
}

if (strpos($content, 'vc_progress_bar') !== false) {
	$content = str_replace('bgcolor="wpb_button"', '', $content);
}

// Setup theme specific containers and classes
$wrapper_class = 'vc_section_wrapper';

// Parallax
if ($bg_parallax) {
	$wrapper_class .= ' parallax-section';
}

// Background images
if ($bg_color || strpos($bg_styles,'background-color:') !== false) {
	$wrapper_class  .= ' has_bg_color';
    //$section_wrapper_style .= ' style="position: relative;"';
    // backwards compatibility
	if ($bg_color) { 
		$bg_layer_style .= 'background-color:'. $bg_color .';'; 
	}
}
if ($bg_image || strpos($bg_styles,'background-image:') !== false || strpos($bg_styles,'background:') !== false) {
	$wrapper_class  .= ' has_bg_img';
	// backwards compatibility
	if ($bg_image) { 
		$media = wp_get_attachment_image_src($bg_image, 'full');
		if ($bg_image_repeat == 'cover') {
			$wrapper_class  .= ' cover_all';		
		} else if ($bg_image_repeat == 'no-repeat') {
			$bg_layer_style .= 'background-repeat:no-repeat;';
		} else if ($bg_image_repeat == '') {
			$bg_layer_style .= 'background-repeat:repeat;';
		}
		$bg_layer_style .= 'background-image: url('.$media[0].');';
	}

}
if($bg_maps)
{
    $wrapper_class .= ' wpb_map-section-full';
    
    // height
    $height = !$bg_maps_height ? 200 : str_replace("px", '', $bg_maps_height);
    $section_wrapper_style = ' style="height: '.$height.'px"';
    
}

// Start the output
$output .= '<section class="'.$wrapper_class.'"'.$section_wrapper_style.'>';
if ($bg_layer_style || $bg_styles) {
	$bg_styles = $bg_styles . $bg_layer_style;
	$output .= '<div class="bg-layer" style="'. $bg_styles .'" data-inertia="'. $inertia .'"></div>';
}

// Maps output
if ($bg_maps) {
    // zoom
    $zoom = !$bg_maps_zoom ? 17 : $bg_maps_zoom;
    
    // scrolling
    
    $scroll = !$bg_maps_scroll ? "false" : $bg_maps_scroll;
    
    
    $infobox = !$bg_maps_infobox ? "false" : $bg_maps_infobox;
    $infobox_address = '';
    if(strlen($bg_maps) < 30) {                                                                         // if short URL
        $response = wp_remote_get("http://api.longurl.org/v2/expand?url=$bg_maps&format=json");         // get long URL
        if(isset($response['body'])) {
            $address = json_decode($response['body'], true);
            $bg_maps = esc_url($address['long-url']);
        }
    }
    $lat_pos = strpos($bg_maps, '@') + 1;
    $lng_pos = strpos($bg_maps, ',', $lat_pos+1) + 1;
    $next_pos = strpos($bg_maps, ',', $lng_pos+1) + 1;
    $lat = substr($bg_maps, $lat_pos, $lng_pos - $lat_pos - 1);
    $lng = substr($bg_maps, $lng_pos, $next_pos - $lng_pos - 1);

    if($infobox)
    {
        $infobox_address = !$bg_maps_infobox_content ? $bg_maps : $bg_maps_infobox_content;
        $infobox_address = nl2br($infobox_address);

        $output .= "<div class='infobox-wrapper'><div id='infobox'>" . $infobox_address . "</div></div>";
    }
   
    $output .= '<div class="bg-layer cover_all" id="gmap_'.$unique_id.'" style="height: '.$height.'px;"></div>';
    //$output .= '<script>jQuery(document).ready(function() {getMap("'.$bg_maps.'", "'.$unique_id.'", '.$zoom.', '.$scroll.', '.$infobox.', '.json_encode($infobox_address).');});</script>';
    $output .= "<script>jQuery(document).ready(function() {
                            var loc, map, marker;
                            loc = new google.maps.LatLng(".$lat.",".$lng.");
                            function initialize() {
                                var mapOptions = {
                                    zoom: ".$zoom.",
                                    scrollwheel : ".$scroll.",
                                    center: loc
                                };
                                map = new google.maps.Map(document.getElementById('gmap_".$unique_id."'),
                                    mapOptions);

                                marker = new google.maps.Marker({
                                    map: map,
                                    position: loc,
                                    visible: true
                                });                            

                                infobox = new InfoBox({
                                    content: document.getElementById('infobox'),
                                    disableAutoPan: false,
                                    maxWidth: 150,
                                    pixelOffset: new google.maps.Size(-140, 0),
                                    zIndex: null,
                                    boxStyle: {
                                        background: 'url(\'http://google-maps-utility-library-v3.googlecode.com/svn/trunk/infobox/examples/tipbox.gif\') no-repeat',
                                        opacity: 0.75,
                                        width: '280px'
                                    },
                                    closeBoxMargin: '12px 4px 2px 2px',
                                    closeBoxURL: 'http://www.google.com/intl/en_us/mapfiles/close.gif',
                                    infoBoxClearance: new google.maps.Size(1, 1)
                                });

                                google.maps.event.addListener(marker, 'click', function() {
                                    infobox.open(map, this);
                                });
                            }

                            google.maps.event.addDomListener(window, 'load', initialize);

                        });
                </script>";    

    //wp_enqueue_script("google-maps", "http://maps.google.com/maps/api/js?sensor=false", array('jquery'));
    wp_enqueue_script("google-maps", "https://maps.googleapis.com/maps/api/js?v=3", array('jquery'));
    //wp_enqueue_script("google-maps", "https://maps.googleapis.com/maps/api/js?v=3.exp", array('jquery'));
    wp_enqueue_script("info-box", 'http://google-maps-utility-library-v3.googlecode.com/svn/trunk/infobox/src/infobox.js', array('jquery'));
    wp_localize_script('google-maps', 'vellum', array('theme_directory' => get_stylesheet_directory_uri()));
}

// VC default output
$element_id = empty($el_id)? '' : 'id="'.$el_id.'" ';
$output .= '<div '.$element_id.'class="'.$css_class.'"'.$style.'>';
$output .= wpb_js_remove_wpautop($content);
$output .= '</div>'.$this->endBlockComment('row');

if ( ! empty( $full_width ) ) {
   $output .= '<div class="vc_row-full-width"></div>';
}

// Finish output
$output .= '</section>';

// Print
echo $output;