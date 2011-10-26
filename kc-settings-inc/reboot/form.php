<?php

class kcForm {

  public static function field( $args = array() ) {
    $defaults = array(
      'type'    => 'text',
      'attr'    => '',
      'current' => ''
    );
    $args = wp_parse_args( $args, $defaults );

    if ( in_array($args['type'], array('', 'text', 'date')) ) {
      $type = 'input';
    }
    elseif ( in_array($args['type'], array('checkbox', 'radio')) ) {
      $type = 'checkbox_radio';
    }
    else {
      $type = $args['type'];
    }


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


  public static function checkbox_radio( $args ) {
    if ( !is_array($args['current']) )
      $args['current'] = array($args['current']);
    if ( !isset($args['check_sep']) || !is_array($args['check_sep']) || count($args['check_sep']) < 2 )
      $args['check_sep'] = array('', '<br />');
    $attr = self::_build_attr( $args['attr'] );

    $output  = '';
    foreach ( $args['options'] as $o ) {
      $output .= "{$args['check_sep'][0]}<label><input type='{$args['type']}' value='{$o['value']}'{$attr}";
      if ( in_array($o['value'], $args['current']) || ( isset($args['current'][$o['value']]) && $args['current'][$o['value']]) )
        $output .= " checked='true'";
      $output .= " /> {$o['label']}</label>{$args['check_sep'][1]}\n";
    }

    return $output;
  }


  public static function select( $args ) {
    if ( !isset($args['none']) || !is_array($args['none']) || empty($args['none']) ) {
      $args['none'] = array(
        'value'   => '-1',
        'label'   => '&mdash;&nbsp;'.__('Select', 'kc-settings').'&nbsp;&mdash;'
      );
    }
    $options = array_merge( array($args['none']), $args['options'] );

    if ( !is_array($args['current']) )
      $args['current'] = array($args['current']);

    $output  = "<select";
    $output .= self::_build_attr( $args['attr'] );
    $output .= ">\n";
    foreach ( $options as $o ) {
      $output .= "\t<option value='".esc_attr($o['value'])."'";
      if ( in_array($o['value'], $args['current']) && ($o['value'] != $args['none']['value']) )
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
