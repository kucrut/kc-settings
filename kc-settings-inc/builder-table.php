<?php

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

/**
 * Settings list table
 */
class kcSettings_builder_table extends WP_List_Table {
	function get_columns() {
		$columns = array(
			'cb'    => '<input type="checkbox" />',
			'id'    => __('ID', 'kc-settings'),
			'type'  => __('Type'),
			'name'  => __('Name'),
			'tools' => __('Tools'),
		);

		return $columns;
	}


	function get_bulk_actions() {
		$actions = array(
			'activate'   => __('Activate'),
			'deactivate' => __('Deactivate'),
			'delete'     => __('Delete'),
			'empty'      => __('Cleanup values', 'kc-settings')
		);
		return $actions;
	}


	function prepare_items() {
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			array(
				'id'   => array( 'id', true ),
				'type' => array( 'type', false ),
			),
		);

		usort( $this->_args['kcsb']['settings'], array($this, 'usort_reorder') );
		$this->items = $this->_args['kcsb']['settings'];
	}


	function column_id( $item ) {
		$url     = 'options-general.php?page=kcsb&amp;id='. $item['id'] .'&amp;_wpnonce='. wp_create_nonce( "__kcsb__{$item['id']}" ) .'&amp;action=';
		$actions = array('edit' => "<a href='".admin_url( "{$url}edit" )."'>".__('Edit')."</a>");

		if ( isset($item['status']) && !$item['status'] )
			$actions['activate'] = "<a href='".admin_url( "{$url}activate" )."'>".__('Activate')."</a>";
		else
			$actions['deactivate'] = "<a href='".admin_url( "{$url}deactivate" )."'>".__('Deactivate')."</a>";

		$actions['delete'] = "<a href='".admin_url( "{$url}delete")."'>".__('Delete')."</a>";

		if ( $item['type'] === 'plugin' ) {
			$values = kc_get_option( $item['prefix'] );
			if ( false !== $values && !empty($values) )
				$actions['empty'] = "<span class='trash'><a href='".wp_nonce_url( admin_url("{$url}empty"), "__kcsb__{$item['id']}" )."' title='".__('Delete setting value(s) from DB', 'kc-settings')."'>".__('Cleanup values', 'kc-settings')."</a></span>";
		}

		if ( !isset($item['status']) || $item['status'] )
			$actions['export'] = "<a href='".admin_url( "{$url}export&amp;type={$item['type']}")."'>".__('Export', 'kc-settings')."</a>";

		return sprintf('%1$s %2$s', $item['id'], $this->row_actions($actions) );
	}


	function column_name( $item ) {
		return $item['type'] === 'plugin' ? $item['page_title'] : '&mdash;&nbsp;N/A&nbsp;&mdash;';
	}


	function column_type( $item ) {
		return $this->_args['kcsb']['options']['type'][$item['type']];
	}


	function column_cb( $item ) {
		return '<input type="checkbox" name="ids[]" value="'.$item['id'].'"/>';
	}


	function column_tools( $item ) {
		$out  = "<div class='kcsb-tools'>\n";
		$out .= "<div class='hide-if-no-js'>\n";
		$out .= "<a class='clone-open' href='#'>".__('Clone', 'kc-settings')."</a>\n";
		$out .= "<div class='kcsb-clone hide-if-js'>\n";
		$out .= "<input class='widefat kcsb-slug kcsb-ids clone-id' data-ids='settings' />\n";
		$out .= "<a class='clone-do' title='".__('Clone', 'kc-settings')."' href='".wp_nonce_url( admin_url("options-general.php?page=kcsb&amp;id={$item['id']}&amp;action=clone"), "__kcsb__{$item['id']}" )."'><span>".__('Clone', 'kc-settings')."</span></a>\n";
		$out .= "<a class='close' title='".__('Cancel')."' href='#'><span>".__('Cancel')."</span></a><br />\n";
		$out .= "<em class='description'>".__("Don't forget to change the setting properties after cloning!", 'kc-settings')."</em>\n";
		$out .= "</div>\n";
		$out .= "</div>\n";
		$out .= "<p class='hide-if-js'><em>".__('Please enable javascript to use the tool.', 'kc-settings')."</em></p>\n";
		$out .= "</div>\n";

		return $out;
	}


	function column_default( $item, $column_name ) {
		return $item[$column_name];
	}


	function no_items() {
		_e('No setting found.', 'kc-settings');
	}


	function usort_reorder( $a, $b ) {
		// If no sort, default to title
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'id';
		// If no order, default to asc
		$order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
		// Determine sort order
		$result = strcmp( $a[$orderby], $b[$orderby] );
		// Send final sort direction to usort
		return ( $order === 'asc' ) ? $result : -$result;
	}
}
