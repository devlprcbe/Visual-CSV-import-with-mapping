<?php

$options =& $_SESSION['data'];

if( !empty($options['file_name']) && !empty($options['mapping']) ) //1st and 2nd step were OK 
{
  $fQuickCSV = new Quick_CSV_import($db);
  
  $lineSeparator = detect_line_ending( $options['file_name'] );
  if( '\n' != $lineSeparator ) {
    $fQuickCSV->line_separate_char = $lineSeparator;
  }
  
  $fQuickCSV->table_name = $options['table'];
  $fQuickCSV->file_name = $options['file_name'];
  $fQuickCSV->use_csv_header = $options['use_csv_header'];
  $fQuickCSV->make_temporary = false;
  $fQuickCSV->table_exists = true;
  $fQuickCSV->field_separate_char = $options['field_separate_char'];
  $fQuickCSV->encoding = $options['encoding'];
  $fQuickCSV->field_enclose_char = $options['field_enclose_char'];
  $fQuickCSV->field_escape_char = $options['field_escape_char'];
  $fQuickCSV->arr_csv_columns = $_SESSION['data']['mapping'];
  
  $fQuickCSV->import();

  if( !empty($fQuickCSV->error) )
  {
    $error = $fQuickCSV->error;
  }
  elseif( 0 == $fQuickCSV->rows_count )
  {
    $error = 'Imported rows count is 0.';
  }

}
else //1st step was not OK
{
  Location('index.php?step=1');
}
