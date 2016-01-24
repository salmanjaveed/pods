<?php
/**
 * @var $form_field_type string
 * @var $options         array
 * @var $field_type      string
 */
wp_enqueue_script( 'jquery-ui-core' );
wp_enqueue_script( 'jquery-ui-sortable' );

wp_enqueue_script( 'backbone' );
wp_enqueue_script( 'marionette', PODS_URL . 'ui/js/marionette/backbone.marionette.js', array( 'backbone' ), '2.4.4', true );

wp_enqueue_script( 'backbone.babysitter', PODS_URL . 'ui/js/marionette/backbone.babysitter.min.js', array( 'backbone' ), '0.1.10', true );
wp_enqueue_script( 'backbone.wreqr', PODS_URL . 'ui/js/marionette/backbone.wreqr.min.js', array( 'backbone' ), '1.0.2', true );
//wp_enqueue_script( 'backbone.radio', PODS_URL . 'ui/js/marionette/backbone.radio.min.js', array( 'backbone' ), '1.0.2', true );
//wp_enqueue_script( 'marionette.radio.shim', PODS_URL . 'ui/js/marionette/marionette.radio.shim.js', array( 'marionette', 'backbone.radio' ), '1.0.2', true );

wp_enqueue_script( 'pods-ui', PODS_URL . 'ui/js/pods-ui.js', array(
	'backbone.wreqr',
	'marionette'
), PODS_VERSION, true );
wp_enqueue_script( 'ui/js/pods-ui-ready', PODS_URL . 'ui/js/pods-ui-ready.js', array( 'pods-ui' ), PODS_VERSION, true );

wp_enqueue_script( 'file-upload-model', PODS_URL . 'ui/fields/file-upload/models/file-upload-model.js', array( 'pods-ui' ), PODS_VERSION, true );
wp_enqueue_script( 'file-upload-list', PODS_URL . 'ui/fields/file-upload/views/file-upload-list.js', array( 'pods-ui' ), PODS_VERSION, true );
wp_enqueue_script( 'file-upload-form', PODS_URL . 'ui/fields/file-upload/views/file-upload-form.js', array( 'pods-ui' ), PODS_VERSION, true );
wp_enqueue_script( 'file-upload-layout', PODS_URL . 'ui/fields/file-upload/views/file-upload-layout.js', array(
	'file-upload-model',
	'file-upload-form',
	'file-upload-list'
), PODS_VERSION, true );

$file_limit = 1;
if ( 'multi' == pods_v( $form_field_type . '_format_type', $options, 'single' ) ) {
	$file_limit = (int) pods_v( $form_field_type . '_limit', $options, 0 );
}

$button_text = pods_v( $form_field_type . '_add_button', $options, __( 'Add File', 'pods' ) );

if ( empty( $value ) ) {
	$value = array();
} else {
	$value = (array) $value;
}

$attributes = PodsForm::merge_attributes( array(), $name, $form_field_type, $options );
$attributes = array_map( 'esc_attr', $attributes );

$model_data = array();
foreach ( $value as $id ) {
	$attachment = get_post( $id );
	if ( empty( $attachment ) ) {
		continue;
	}

	$thumb = wp_get_attachment_image_src( $id, 'thumbnail', true );
	$title = $attachment->post_title;
	if ( 0 == $title_editable ) {
		$title = basename( $attachment->guid );
	}

	$link = wp_get_attachment_url( $attachment->ID );

	$model_data[] = array(
		'id'   => $id,
		'name' => $title,
		'icon' => $thumb[ 0 ],
		'link' => $link
	);
}

$limit_file_type = pods_var( $form_field_type . '_type', $options, 'images' );

$title_editable = pods_var( $form_field_type . '_edit_title', $options, 0 );
$linked = pods_var( $form_field_type . '_linked', $options, 0 );

if ( 'images' == $limit_file_type ) {
	$limit_types = 'image';
	$limit_extensions = 'jpg,jpeg,png,gif';
}
elseif ( 'video' == $limit_file_type ) {
	$limit_types = 'video';
	$limit_extensions = 'mpg,mov,flv,mp4';
}
elseif ( 'audio' == $limit_file_type ) {
	$limit_types = 'audio';
	$limit_extensions = 'mp3,m4a,wav,wma';
}
elseif ( 'text' == $limit_file_type ) {
	$limit_types = 'text';
	$limit_extensions = 'txt,rtx,csv,tsv';
}
elseif ( 'any' == $limit_file_type ) {
	$limit_types = '';
	$limit_extensions = '*';
}
else {
	$limit_types = $limit_extensions = pods_var( $form_field_type . '_allowed_extensions', $options, '', null, true );
}
$limit_types = trim( str_replace( array( ' ', '.', "\n", "\t", ';' ), array( '', ',', ',', ',' ), $limit_types ), ',' );
$limit_extensions = trim( str_replace( array( ' ', '.', "\n", "\t", ';' ), array( '', ',', ',', ',' ), $limit_extensions ), ',' );
$mime_types = wp_get_mime_types();

if ( !in_array( $limit_file_type, array( 'images', 'video', 'audio', 'text', 'any' ) ) ) {
	$new_limit_types = array();

	$limit_types = explode( ',', $limit_types );

	foreach ( $limit_types as $k => $limit_type ) {
		if ( isset( $mime_types[ $limit_type ] ) ) {
			$mime = explode( '/', $mime_types[ $limit_type ] );
			$mime = $mime[ 0 ];

			if ( !in_array( $mime, $new_limit_types ) )
				$new_limit_types[] = $mime;
		}
		else {
			$found = false;

			foreach ( $mime_types as $type => $mime ) {
				if ( false !== strpos( $type, $limit_type ) ) {
					$mime = explode( '/', $mime );
					$mime = $mime[ 0 ];

					if ( !in_array( $mime, $new_limit_types ) ) {
						$new_limit_types[] = $mime;
					}
					$found = true;
				}
			}

			if ( !$found )
				$new_limit_types[] = $limit_type;
		}
	}

	if ( !empty( $new_limit_types ) )
		$limit_types = implode( ',', $new_limit_types );
}

$options[ 'limit_types' ] = $limit_types;
$options[ 'limit_extensions' ] = $limit_extensions;
$field_meta = array(
	'field_attributes' => array(
		'id'         => $attributes[ 'id' ],
		'class'      => $attributes[ 'class' ],
		'name'       => $attributes[ 'name' ],
		'name_clean' => $attributes[ 'data-name-clean' ]
	),
	'field_options' => $options
);
include_once PODS_DIR . 'ui/fields/file-upload/templates/file-upload-tpl.php';
include_once PODS_DIR . 'ui/fields/file-upload/PodsFieldData.php';

// @todo Need to normalize and finalize.  Is there a potential need for subclasses or does this basically cover it?
$field_data = new PodsUIFieldData( $field_type, array( 'model_data' => $model_data, 'field_meta' => $field_meta ) );

// @todo This is the demarcation point, everything above this exists to achieve the single line below.  Everything
// upstream from here needs clean up, simplification, and refactoring
?>
<div class="pods-ui-field"><?php $field_data->emit_script(); ?></div>