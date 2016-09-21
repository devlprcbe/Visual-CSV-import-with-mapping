<?php
error_reporting(E_WARNING ^ E_DEPRECATED);//Returns the first non-empty value in the list, or an empty line if there are no non-empty values.
function coalesce()
{ 
  for($i=0; $i < func_num_args(); $i++)
  {
    $arg = func_get_arg($i);
    if(!empty($arg))
      return $arg;
  }
  return "";
}

//go to new location (got from Fusebox4 source)
function Location($URL, $addToken = 1)
{
  $questionORamp = (strstr($URL, "?"))?"&":"?";
  $location = ( $addToken && substr($URL, 0, 7) != "http://" && defined('SID') ) ? $URL.$questionORamp.SID : $URL; //append the sessionID ($SID) by default
  //ob_end_clean(); //clear buffer, end collection of content
  if(headers_sent()) {
    print('<script type="text/javascript" type="text/javascript">( document.location.replace ) ? document.location.replace("'.$location.'") : document.location.href = "'.$location.'";</script>'."\n".'<noscript><meta http-equiv="Refresh" content="0;URL='.$location.'" /></noscript>');
  } else {
    header('Location: '.$location); //forward to another page
    exit; //end the PHP processing
  }
}

//checks that we have all modules we need or exit() will be called
function check_necessary_functions()
{ 
  for($i=0; $i < func_num_args(); $i++)
  {
    $func_name = func_get_arg($i);
    if( !function_exists($func_name) )
    {
      exit ( "Function [" . $func_name . "] is not accessable. Please check that correspondent PHP module is installed at your web-server." );
    }
  }
  return true;
}

//writes data in a file
function write_file($filename, $data)
{
  $fp = fopen($filename, 'w');
  if($fp)
  {
    fwrite($fp, $data);
    fclose($fp);
    return true;
  }
  return false;
}

//writes data in the end of a file
function append_file($filename, $data)
{
  $fp = fopen($filename, 'a');
  if($fp)
  {
    fwrite($fp, $data);
    fclose($fp);
    return true;
  }
  return false;
}

//OS independent deletion of a file
function delete_file($filename)
{
  if(file_exists($filename))
  {
    $os = php_uname();
    if(stristr($os, "indows")!==false)
      return exec("del ".$filename);
    else
      return unlink($filename);
  }
  return true;
}


//returns all fields of [tableName]
function get_table_fields($db, $tableName )
{
  $arrFields = array();
  if( empty($tableName) )
  {
    return false;
  }
  
  $db->query("SHOW TABLES LIKE '".$tableName."'");
  
  if( 0 == $db->getRowsCount())
  {
    return false;
  }
  
  $db->query("SHOW COLUMNS FROM ".$tableName);
  
  
  while( $row = mysql_fetch_array($db->fResult) )
  {
    $arrFields[] = trim( $row[0] );
  }
  
  return $arrFields;
}

function detect_line_ending($file)
{
    $s = file_get_contents($file);
    if( empty($s) ) return null;
    
    if( substr_count( $s,  "\r\n" ) ) return '\r\n'; //Win
    if( substr_count( $s,  "\r" ) )   return '\r';   //Mac
    return '\n'; //Unix
}

function startsWith( $str, $token ) {
    $_token = trim( $token );
    $_str = trim( $str );
    if( empty( $_token ) || empty( $str ) ) return false;
    
    $tokenLen = strlen( $_token );
    // $tokenFromStr = substr( $_str, 0, $tokenLen );
    // return strtolower( $_token ) == strtolower( $tokenFromStr );
    
    return !strncasecmp($_str, $token, $tokenLen );
}