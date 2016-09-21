<?php

include "config.inc"; //load database settings, folders paths and such stuff

//load necessary classes
set_include_path( CLASSES_DIR );
require "CSV.class.php";
require "Quick_CSV_import.class.php";
require "DB.class.php";
require "functions.php";
include "dBug.php"; //for dev

check_necessary_functions( 'session_start', 'mysql_connect'); //check that we have all modules we need. Exit() can be called here if we're lacking anything

if( !is_writable( TEMP_DIR ) )
{
  exit ( "Temporary folder must be writable: <code>".TEMP_DIR."</code>" );
}

if( !is_writable( SESSIONS_DIR ) )
{
  exit ( "Sessions folder must be writable: <code>".SESSIONS_DIR."</code>" );
}

if ( -1 == version_compare( PHP_VERSION, '4.1.0' ) ) {
    exit ('Please, you PHP version greater than 4.1.0 - files uploads will not work properly');
}

$attributes = array_merge( $_GET, $_POST ); //useful array from Fusebox world allows avoid thinking about the way a variable has come to us

if ( get_magic_quotes_gpc() ) {
  foreach( $attributes as $key => $value ) {
    $attributes[$key] = stripslashes( $value );
  }
}

//start session and renew it
session_save_path( SESSIONS_DIR );
session_start( 'user');
setcookie( session_name(), session_id(), time()+SESSION_TTL, "/" ); //renew session for the next TTL days

//connect to database
$db = new mysql( DB_HOST, DB_LOGIN, DB_PASSWORD, DB_NAME );
if( !$db->connect() )
{
  exit ( "Cannot connect a database" );
}
register_shutdown_function( 'mysql_close', $db->link );


// now check if current user has right to all operations. 
// You can comment this block on your server
// (actually here must be a check for current database!)
$sql = sprintf( "SHOW GRANTS FOR CURRENT_USER" );

$db->query( $sql );
if( !empty( $db->fError ) ) {
    exit( $db->fError );
}
$result = $db->getRow();
$result = current($result);

if( !startsWith( $result, 'GRANT ALL PRIVILEGES' ) ) {
    $necessaryPermissions = array( 
        'SELECT',
        'CREATE',
        'CREATE TEMPORARY TABLES',
    );
    
    foreach( $necessaryPermissions as $perm ) {
        if( !strpos( $result, $perm . ',' ) && !strpos( $result, $perm . ' ON' ) ) {
            exit( sprintf( 'Database user <code>%s</code> must have <code>%s</code> permission',
               DB_LOGIN, $perm ) );
        }
    }
}





if( !ini_get("file_uploads") ) //check whether administrator must tune PHP
{
  exit ( "PHP directive [file_uploads] must be turned ON" );
}

ini_set('auto_detect_line_endings', 1);

$uploadErrors = array(
    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
    UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.',
    -1 => 'File is empty. Try again.'
);