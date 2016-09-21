<?php

$options =& $_SESSION['data'];

if( !empty($attributes['submit']) ) //second step was OK 
{
  $_SESSION['data']['mapping'] = $attributes['mapping'];
  Location('index.php?step=3');
}
elseif( !empty($options['file_name']) ) //first step was OK 
{
  if( empty($options['field_separate_char']) ) //separator autodetect (comma as default)
  {
    $separator = CSV::try_separators( CSV::get_line( $options['file_name'] ) );
    if( empty( $separator ) ) {
        exit( 'Cannot autodetect the separator' );
    }
    $options['field_separate_char'] = $separator;
  }
  
    $arr_fields = get_table_fields($db, $options['table'] );
    if( empty( $arr_fields ) ) {
        exit( 'Cannot retrieve fields of the selected table' );
    }
  
    $arr_headers = CSV::get_header_fields( $db, $options['file_name'], $options['encoding'], $options['field_separate_char'], $options['field_enclose_char'], $options['field_escape_char'] );
    if( empty( $arr_headers ) ) {
        exit( 'Cannot retrieve headers columns of the CSV file' );
    }

    $arr_examples = CSV::get_examples( $db, $options['file_name'], $options['encoding'], $options['field_separate_char'], $options['field_enclose_char'], $options['field_escape_char'] );
    if( empty( $arr_examples ) ) {
        exit( 'Cannot retrieve example data of the CSV file (first data line)' );
    }
  //save for 3rd step to avoid rereads
  $_SESSION['data']['table_columns'] = $arr_fields;
  $_SESSION['data']['csv_headers'] = $arr_headers;

}
else //1st step was not OK
{
  Location('index.php?step=1');
}

$fields_select = '<select name="mapping[%s]" id="select_%d"><option value="">- select -</option>';
foreach( $arr_fields as $field)
{
  $fields_select .= '<option value="'.htmlspecialchars($field).'">'.htmlspecialchars($field).'</option>';
}
$fields_select .= '</select>';