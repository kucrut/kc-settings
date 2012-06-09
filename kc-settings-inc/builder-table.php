<?php

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
class kcSettings_builder_table extends WP_List_Table {
	function get_columns() {
		$columns = array(
			'id'    => __('ID', 'kc-settings'),
			'type'  => __('Type'),
			'tools' => __('Tools')
		);

		return $columns;
	}


	function prepare_items() {
		$columns = $this->get_columns();
		$this->_column_headers = array( $columns, array(), array() );
		$this->items = $this->_args['kcsb']['settings'];
  }


  function column_id( $item ) {
		$url = "options-general.php?page=kcsb&amp;id={$item['id']}&amp;action=";
		$actions = array(
			'edit'   => "<a href='".admin_url( "{$url}edit" )."'>".__('Edit')."</a>",
			'delete' => "<a href='".wp_nonce_url( admin_url("{$url}delete"), "__kcsb__{$item['id']}" )."' title='".__('Delete this setting', 'kc-settings')."'>".__('Delete')."</a>"
		);

		if ( $item['type'] === 'plugin' ) {
			$values = kc_get_option( $item['prefix'] );
			if ( false !== $values ) {
				if ( !empty($values) )
					$actions['empty'] = "<span class='trash'><a href='".wp_nonce_url( admin_url("{$url}empty"), "__kcsb__{$item['id']}" )."' title='".__('Reset options', 'kc-settings')."'>".__('Empty', 'kc-settings')."</a></span>";

				$actions['purge'] = "<span class='trash'><a href='".wp_nonce_url( admin_url("{$url}purge"), "__kcsb__{$item['id']}" )."' title='".__('Remove all sections and fields', 'kc-settings')."'>".__('Purge', 'kc-settings')."</a></span>";
			}
		}

		return sprintf('%1$s %2$s', $item['id'], $this->row_actions($actions) );
  }


  function column_tools( $item ) {
		$out  = "<div class='kcsb-tools'>\n";
		$out .= "<div class='hide-if-no-js'>\n";
		$out .= "<a class='clone-open' href='#'>".__('Clone', 'kc-settings')."</a>\n";
		$out .= "<div class='kcsb-clone hide-if-js'>\n";
		$out .= "<input class='widefat kcsb-slug kcsb-ids clone-id' data-ids='settings' />\n";
		$out .= "<a class='clone-do' title='".__('Clone', 'kc-settings')."' href='".wp_nonce_url( admin_url("options-general.php?page=kcsb&amp;id={$item['id']}&amp;action=clone"), '__kcsb__{$sID}' )."'><span>".__('Clone', 'kc-settings')."</span></a>\n";
		$out .= "<a class='close' title='".__('Cancel')." ?>' href='#'><span>".__('Cancel')."</span></a><br />\n";
		$out .= "<em class='description'>".__("Don't forget to change the setting properties after cloning!", 'kc-settings')."</em>\n";
		$out .= "</div>\n";
		$out .= "</div>\n";
		$out .= "<p class='hide-if-js'><em>".__('Please enable javascript to use the tool.', 'kc-settings')."</em></p>\n";
		$out .= "</div>\n";

		return $out;
  }


  function column_type( $item ) {
		return $this->_args['kcsb']['options']['type'][$item['type']]['label'];
	}


  function column_default( $item, $column_name ) {
		return $item[$column_name];
  }


	function no_items() {
		_e('No setting found.', 'kc-settings');
	}
}

?>
