<?php

$csv = new Quick_CSV_import($db);

$arr_encodings = $csv->get_encodings(); //take possible encodings list
$arr_encodings["utf8"] = "utf8"; //set a default (when the default database encoding should be used)

$db->query( 'SHOW TABLES' );
$arr_tables = $db->getRecordSet();

$attributes["encoding"] = coalesce( $attributes["encoding"], "utf8" ); //set default charset as default option in the list

if( !empty($_POST["Go"]) ) //form was submitted
{
 
  $errorCode = $_FILES['file_source']['error'];
  if( 0 == $_FILES['file_source']['size'] )
  {
    $errorCode = -1; //empty file
  }
  
  if( is_uploaded_file($_FILES['file_source']['tmp_name']) && UPLOAD_ERR_OK == $errorCode )  //file was uploaded successfully
  {
    $temp_file = $_FILES['file_source']['tmp_name'];
    $our_file  = TEMP_DIR . basename($temp_file);
    if ( !move_uploaded_file( $temp_file, $our_file ) ) //copy to our folder
    {
      $error = 'Could not copy [' . $temp_file .'] to [' . $our_file . ']';
    }
    else
    {
      $_SESSION['data']['file_name'] = $our_file;
      $_SESSION['data']['use_csv_header'] = isset($attributes['use_csv_header']);
      $_SESSION['data']['field_separate_char'] = $attributes['field_separate_char'];
      $_SESSION['data']['field_enclose_char'] = $attributes['field_enclose_char'];
      $_SESSION['data']['field_escape_char'] = $attributes['field_escape_char'];
      $_SESSION['data']['encoding'] = $attributes['encoding'];
      $_SESSION['data']['table'] = $attributes['table'];
    
      Location('index.php?step=2');
    }
  }
  else //no, file was not uploaded, so let's rise an error
  {
    $error = coalesce( $uploadErrors[$errorCode], 'General upload error. Check <a href="http://php.net/manual/en/features.file-upload.php">file uploads settings</a> of your php.ini' );
    $_SESSION['data'] = array(); //erase previosly saved options
  }
  
}
else //form wasn't submited, it's a first request
{
  $_POST["use_csv_header"] = 1;
  $_SESSION['data'] = array(); //erase previosly saved options
}