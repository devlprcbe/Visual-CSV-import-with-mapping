<?php
error_reporting(E_WARNING ^ E_DEPRECATED);/*
 Quick_CSV_import class provides interface to a quick CSV file to MySQL database import. Much quicker (10-100 times) that line by line SQL INSERTs.
 version: 1.5
 author: Skakunov Alexander <i1t2b3@gmail.com>
 date: 23.08.2006
 description:
   1. Before importing, you MUST: 
     - establish connection with MySQL database and select database;
     - define CSV filename to import from.
   2. You CAN define several additional import attributes:
     - use CSV header or not: if yes, first line of the file will be recognized as CSV header, and all database columns will be called so, and this header line won't be imported in table content. If not, the table columns will be calles as "column1", "column2", etc
     - separate char: character to separate fields, comma [,] is default
     - enclose char: character to enclose those values that contain separate char in text, quote ["] is default
     - escape char: character to escape special symbols like enclose char, back slash [\] is default
     - encoding: string value which represents MySQL encoding table to parse files with. It's strongly recomended to use known values, not manual typing! Use get_encodings() method (or "SHOW CHARACTER SET" query) to ask the server for the encoding tables list
     - arr_csv_columns is an assoc. array, 'name' goes for field name and 'type' for its SQL type like 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT'
     - additional_create contains any SQL to add in the end of the CREATE TABLE statement, like 'PRIMARY KEY(`id`)'
   3. You can read "error" property to get the text of the error after import. If import has been finished successfully, this property is empty.
*/


class Quick_CSV_import
{
  var $table_name; //where to import to
  var $file_name;  //where to import from
  var $use_csv_header; //use first line of file OR generated columns names
  var $line_separate_char; //character(s) to separate lines (usually \n ) 
  var $field_separate_char; //character to separate fields
  var $field_enclose_char; //character to enclose fields, which contain separator char into content
  var $field_escape_char;  //char to escape special symbols
  var $error; //error message
  var $arr_csv_columns; //array of columns
  var $arr_csv_columns_to_load; //array of columns
  var $table_exists; //flag: does table for import exist
  var $make_temporary; //flag: does table for import exist
  var $additional_create;
  var $rows_count; //how many rows has been imported
  var $encoding; //encoding table, used to parse the incoming file. Added in 1.5 version
  var $fDB; //database object ref
  
  function Quick_CSV_import($fDB, $file_name="")
  {
    if(!is_object($fDB) || empty($fDB->link))
    {
      throw new Exception('Wrong database object');
    }
    $this->fDB = $fDB;
    $this->file_name = $file_name;
    $this->arr_csv_columns = array();
    $this->use_csv_header = true;
    $this->line_separate_char = '\n';
    $this->field_separate_char = ",";
    $this->field_enclose_char  = "\"";
    $this->field_escape_char   = "\\";
    $this->table_exists = false;
  }
  
  function import()
  {
    if( empty($this->table_name) )
      $this->table_name = "temp_".date("d_m_Y_H_i_s");
    
    if( !$this->table_exists )
    {
      $this->create_import_table();
    }
    
    if( !in_array( $this->line_separate_char, array('\n', '\r', '\r\n' ) ) )
    {
        $this->line_separate_char = '\n';
    }
    
    if(empty($this->arr_csv_columns_to_load))
      $this->arr_csv_columns_to_load = $this->arr_csv_columns;
      
    $fields = array();
    foreach($this->arr_csv_columns_to_load as $column)
    {
      $field = '@dummy';
      if( is_array($column) )
      {
        $field = '`'.$column['name'].'`';
      }
      elseif( '' != trim($column) )
      {
        $field = '`'.$column.'`';
      }
      $fields[] = $field;
    }
    
    /* change start. Added in 1.5 version */
    if("" != $this->encoding && "default" != $this->encoding)
      $this->set_encoding();
    /* change end */
    
    if($this->table_exists)
    {
      $sql = "LOAD DATA LOCAL INFILE '".@mysql_escape_string($this->file_name).
             "' IGNORE INTO TABLE `".$this->table_name.
             "` FIELDS TERMINATED BY '".@mysql_escape_string($this->field_separate_char).
             "' OPTIONALLY ENCLOSED BY '".@mysql_escape_string($this->field_enclose_char).
             "' ESCAPED BY '".@mysql_escape_string($this->field_escape_char).
             "' LINES TERMINATED BY '". $this->line_separate_char .
             "' ".
             ($this->use_csv_header ? " IGNORE 1 LINES " : "")
             ."(".implode(",", $fields).")";
      $res = $this->fDB->query($sql);
      // error_log($sql);
      $this->error = $this->fDB->fError;
      if(empty($this->error)) //OK!
      {
        $sql = "SELECT COUNT(*) AS cnt
                 FROM `".$this->table_name."`";
        $res = $this->fDB->query($sql);
        $this->error = $this->fDB->fError;
        if(empty($this->error)) //OK!
        {
          $this->rows_count = $this->fDB->getField();
        }
      }
    }
  }
  
  //returns array of CSV file columns
  function get_csv_header_fields()
  {
    $this->arr_csv_columns = array();
    $fpointer = fopen($this->file_name, "r");
    if ($fpointer)
    {
      $arr = fgetcsv($fpointer, 10*1024, $this->field_separate_char);
      if(is_array($arr) && !empty($arr))
      {
        if($this->use_csv_header)
        {
          foreach($arr as $val)
            //if(''!=trim($val))
              $this->arr_csv_columns[] = array('name'=>$val, 'type'=>'TEXT');
        }
        else
        {
          $i = 1;
          foreach($arr as $val)
          {
            //if(''!=trim($val))
            $this->arr_csv_columns[] = array('name'=>'column'.$i, 'type'=>'TEXT');
            $i++;
          }
        }
      }
      unset($arr);
      fclose($fpointer);
    }
    else
      $this->error = "file cannot be opened: ".(""==$this->file_name ? "[empty]" : @mysql_escape_string($this->file_name));
    return $this->arr_csv_columns;
  }
  
  function create_import_table()
  {
    $sql = "CREATE ".($this->make_temporary ? 'TEMPORARY':'')." TABLE IF NOT EXISTS ".$this->table_name." (";
    
    if(empty($this->arr_csv_columns))
      $this->get_csv_header_fields();
    
    if(!empty($this->arr_csv_columns))
    {
      $arr = array();
      foreach($this->arr_csv_columns as $i=>$column)
        $arr[] = "`".$column['name']."` ".$column['type'];
      if( !empty($this->additional_create) )
        $arr[] = $this->additional_create;
      $sql .= implode(",", $arr);
      $sql .= ")";
      //new dBug($sql);
      $res = $this->fDB->query($sql);
      $this->error = $this->fDB->fError;
      $this->table_exists = empty($this->error);
    }
  }
  
  /* change start. Added in 1.5 version */
  //returns recordset with all encoding tables names, supported by your database
  function get_encodings()
  {
    $rez = array();
    $sql = "SHOW CHARACTER SET";
    $res = $this->fDB->query($sql);
    if($this->fDB->fRowsNumber > 0 && empty($this->fDB->fError))
    {
      while ($row = $this->fDB->getRow())
      {
        $rez[$row["Charset"]] = ("" != $row["Description"] ? $row["Description"] : $row["Charset"]); //some MySQL databases return empty Description field
      }
    }
    return $rez;
  }
  
  /* change start. Added in 1.5 version */
  //returns recordset with all encoding tables names, supported by your database
  function get_column($column_name, &$whole_count, $page=1, $limit=0)
  {
    $arrColumns = array();
    $sql = sprintf(
           "SELECT SQL_CALC_FOUND_ROWS DISTINCT `%1\$s`
            FROM `%2\$s`
            WHERE `%1\$s` <> ''
            ORDER BY `%1\$s`
           %3\$s", 
           $column_name,
           $this->table_name,
           ($limit > 0 ? 'LIMIT '.($page-1)*$limit.', '.$limit : '')
           );
    $res = $this->fDB->query($sql);
    //new dBug($sql);
    if( !empty($this->fDB->fError) )
    {
      $this->error = $this->fDB->fError;
      return $arrColumns;
    }

    $arrColumns = $this->fDB->getColumn($column_name);
    //new dBug($arrColumns);
    $sql = "SELECT FOUND_ROWS()";
    $res = $this->fDB->query($sql);
    $whole_count = $this->fDB->getField();
    return $arrColumns;
  }
  
  //defines the encoding of the server to parse to file
  function set_encoding($encoding="")
  {
    if("" == $encoding)
      $encoding = $this->encoding;
    $sql = "SET SESSION character_set_database = " . $encoding; //'character_set_database' MySQL server variable is [also] to parse file with rigth encoding
    $res = @mysql_query($sql);
    return mysql_error();
  }
  /* change end */

}

?>
