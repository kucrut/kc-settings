<?php

class kcForm {

  public static function field( $args = array() ) {
    $defaults = array(
      'type'    => 'text',
      'attr'    => '',
      'current' => '',
      'none'    => '&mdash;&nbsp;'.__('Select', 'kc-settings').'&nbsp;&mdash;',
      'echo'    => false
    );
    $args = wp_parse_args( $args, $defaults );

    ( in_array($args['type'], array('', 'text', 'date')) ) ?
      $type = 'input' :
      $type = $args['type'];

    if ( !method_exists(__CLASS__, $type) )
      return false;

    if ( in_array($type, array('select', 'radio', 'checkbox'))
          && (!isset($args['options']) || !is_array($args['options'])) )
      return false;

    return call_user_func( array(__CLASS__, $type), $args );
  }


  public static function input( $args ) {
    $output  = "<input type='{$args['type']}'";
    $output .= self::_build_attr( $args['attr'] );
    $output .= "value='{$args['current']}' ";
    $output .= " />";

    return $output;
  }


  public static function textarea( $args ) {
    $output  = "<textarea";
    $output .= self::_build_attr( $args['attr'] );
    $output .= ">";
    $output .= esc_textarea( $args['current'] );
    $output .= "</textarea>";

    return $output;
  }


  public static function select( $args ) {
    $options = array_merge( array(array('value' => '0', 'label' => $args['none'])), $args['options'] );
    if ( !is_array($args['current']) )
      $args['current'] = array($args['current']);

    $output  = "<select";
    $output .= self::_build_attr( $args['attr'] );
    $output .= ">\n";
    foreach ( $options as $o ) {
      $output .= "\t<option value='".esc_attr($o['value'])."'";
      if ( in_array($o['value'], $args['current']) )
        $output .= " selected='true'\n";
      $output .= ">{$o['label']}</option>\n";
    }
    $output .= "</select>";

    return $output;
  }


  private static function _build_attr( $attr ) {
    if ( !is_array($attr) || empty($attr) )
      return;

    foreach ( array('type', 'value', 'checked', 'selected') as $x )
      unset( $attr[$x] );

    $output = '';
    foreach ( $attr as $k => $v )
      $output .= " {$k}='".esc_attr($v)."'";

    return $output;
  }

}


?>
