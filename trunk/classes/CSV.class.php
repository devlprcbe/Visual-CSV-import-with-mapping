<?php

class CSV 
{

  //returns array of CSV file fields names
  public static function get_header_fields($db, $file_name, $encoding='utf8', $separator=',', $enclose_char='"', $escape_char='\\')
  {
    return self::load_line($db, $file_name, 1, $encoding, $separator, $enclose_char, $escape_char);
  }

  public static function get_examples($db, $file_name, $encoding='utf8', $separator=',', $enclose_char='"', $escape_char='\\')
  {
    return self::load_line($db, $file_name, 2, $encoding, $separator, $enclose_char, $escape_char);
  }

  public static function get_line($file_name, $line_num=1)
  {
    $line = '';
    $fpointer = fopen($file_name, "r");
    if ($fpointer)
    {
      for($i=1; $i<=$line_num; $i++)
      {
        $line = fgets($fpointer); //get a line which number is equal to $line_num
      }
    }
    return $line;
  }

  public static function load_line($db, $file_name, $line_num, $encoding='utf8', $separator=',', $enclose_char='"', $escape_char='\\')
  {
    $arrColumns = array();
    $line = self::get_line($file_name, $line_num);
      
    if( !empty($line) )
    {
      $filename = tempnam(TEMP_DIR, 'csv');
      if($filename)
      {
        write_file($filename, $line);
        $arrColumns = self::convert_line($db, $filename, $encoding, $separator, $enclose_char, $escape_char);
        delete_file($filename);
      }
    }
    return $arrColumns;
  }

  public static function convert_line($db, $file_name, $encoding='utf8', $separator=',', $enclose_char='"', $escape_char='\\')
  {
    $rez = array();
    
    $fQuickCSV = new Quick_CSV_import($db);
    
    $fQuickCSV->table_name = sprintf("temp_%s_%d", date("d_m_Y_H_i_s"), rand(1, 100));
    $fQuickCSV->file_name = $file_name;
    $fQuickCSV->use_csv_header = false;
    $fQuickCSV->make_temporary = true;
    $fQuickCSV->field_separate_char = $separator;
    $fQuickCSV->encoding = $encoding;
    $fQuickCSV->field_enclose_char = $enclose_char;
    $fQuickCSV->field_escape_char = $escape_char;
    
    $fQuickCSV->import();

    if( !empty($fQuickCSV->error) ) {
        exit( $fQuickCSV->error );
    }
    
    if( 0 == $fQuickCSV->rows_count )
    {
        exit( 'CSV::convert_line returned 0 rows which is not good' );
    }
    
    $db->query("SELECT * FROM `".$fQuickCSV->table_name."` LIMIT 1");
    return $db->getRow();
  }

  //gets count of each possible CSV separator
  //returns most appropriative separator (by max count in the string)
  public static function try_separators($line, $default=null)
  {
    $csv_separators = array(1=>",", ";", "|", "\\", "/", "#", "!", "*", "-");
    $max_similar = 0;
    $index       = 0;
    foreach($csv_separators as $i=>$sep)
    {
      $count = substr_count($line, $sep);
      if($max_similar<$count)
      {
        $max_similar=$count;
        $index = $i;
      }
    }
    if($index>0)
    {
      return $csv_separators[$index];
    }
    elseif( !empty( $default ) )
    {
      return $default;
    }
    
    return null;
  }

}