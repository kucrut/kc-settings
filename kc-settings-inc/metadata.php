<?php

/**
 * KC Meta
 *
 * Merge all the custom fields set by themes/plugins. Also rebuild the array.
 *
 * @param string $meta_type post|term|user Which meta?
 * @return array $nu Our valid post/term/user meta options array
 *
 */

function kc_meta( $meta_type, $others = array() ) {
	$old = apply_filters( "kc_{$meta_type}_settings", $others );
	if ( !is_array($old) || empty($old) )
		return;

	$nu = array();
	foreach ( $old as $group ) {
		if ( !is_array($group) || empty($group) )
			return;

		# Loop through each taxonomy/post type to see if it has sections
		foreach ( $group as $pt_tax => $sections ) {
			# Skip this taxonomy/post type if it has no sections
			if ( !is_array($sections) )
				continue;

			# Loop through each section to see if it has fields
			foreach ( $sections as $section )
				# Skip the section if it doesn't have them
				if ( !isset($section['fields']) || !is_array($section['fields']) || empty($section['fields']) )
					continue 2;

			# Rebuild the array
			if ( isset($nu[$pt_tax]) )
				foreach ( $sections as $sk => $sv )
					$nu[$pt_tax][$sk] = $sv;
			else
				$nu[$pt_tax] = $sections;
		}
	}

	return $nu;
}


/**
 * Update posts & terms metadata
 *
 * @param string $meta_type post|term|user The type of metadata, post, term or user
 * @param string $object_type_name The taxonomy or post type name
 * @param int $object_id The ID of the object (post/term) that we're gonna update
 * @param array $section The meta section array
 * @param array $field The meta field array
 * @param bool $attachment Are we updating attachment metadata?
 */
function kc_update_meta( $meta_type = 'post', $object_type_name, $object_id, $section, $field, $attachment = false ) {
	if ( isset($_POST['action']) && $_POST['action'] == 'inline-save' )
		return;

	# Set the meta key and get the value based on the $meta_type and screen
	switch( $meta_type ) {
		case 'post' :
			$meta_key = "_{$field['id']}";
			$action = 'editpost';
		break;

		case 'term' :
			$meta_key = $field['id'];
			$action = 'editedtag';
		break;

		case 'user' :
			$meta_key = $field['id'];
			$action = 'update';
		break;
	}

	#$db_val = ( isset($_POST['action']) && $_POST['action'] == $action ) ? get_metadata( $meta_type, $object_id, $meta_key, true ) : null;
	$db_val = get_metadata( $meta_type, $object_id, $meta_key, true );

	# Get the new meta value from user
	if ( $attachment ) {
		$nu_val = array_key_exists($field['id'], $_POST['attachments'][$object_id]) ? $_POST['attachments'][$object_id][$field['id']] : '';
	}
	else {
		$nu_val = $_POST["kc-{$meta_type}meta"][$section['id']][$field['id']];
	}

	# default sanitation
	if ( $field['type'] == 'multiinput' ) {
		$nu_val = kc_array_remove_empty( $nu_val );
		$nu_val = kc_array_rebuild_index( $nu_val );
		if ( empty($nu_val) )
			$nu_val = '';
	}
	elseif ( !is_array($nu_val) ) {
		$nu_val = trim( $nu_val );
	}

	$filter_prefix = "kcv_{$meta_type}meta";
	if ( $meta_type != 'user' && $object_type_name != '' )
		$filter_prefix .= "_{$object_type_name}";

	# apply validation/sanitation filters on the new values
	# 	0. Taxonomy / Post type
	$nu_val = apply_filters( "{$filter_prefix}", $nu_val, $section, $field );
	# 	1. Field type
	$nu_val = apply_filters( "{$filter_prefix}_{$field['type']}", $nu_val, $section, $field );
	#		2. Section
	$nu_val = apply_filters( "{$filter_prefix}_{$section['id']}", $nu_val, $section, $field );
	# 	3. Field
	$nu_val = apply_filters( "{$filter_prefix}_{$section['id']}_{$field['id']}", $nu_val, $section, $field );

	# If a new meta value was added and there was no previous value, add it.
	if ( $nu_val && '' == $db_val )
		add_metadata( $meta_type, $object_id, $meta_key, $nu_val, true );

	# If the new meta value does not match the old value, update it.
	elseif ( $nu_val && $nu_val != $db_val )
		update_metadata( $meta_type, $object_id, $meta_key, $nu_val );

	# If there is no new meta value but an old value exists, delete it.
	elseif ( '' == $nu_val && $db_val )
		delete_metadata( $meta_type, $object_id, $meta_key, $nu_val );
}

?>
