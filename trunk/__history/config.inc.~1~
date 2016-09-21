<?php
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);

ini_set("session.use_trans_sid", 1);
ini_set("arg_separator.output", "&amp;");
ini_set("display_errors", 1);
ini_set("track_errors", 1);
ini_set("error_log", 'error.log');

//database settings
define("DB_HOST"    , 'localhost');
define("DB_LOGIN"   , 'root');
define("DB_PASSWORD", '1');
define("DB_NAME"    , 'test');

define("CURRENT_DIR"  , getcwd() . DIRECTORY_SEPARATOR );   //stand-alone classes
define("CLASSES_DIR"  , CURRENT_DIR . 'classes' .  DIRECTORY_SEPARATOR);   //stand-alone classes
define("ACTIONS_DIR"  , CURRENT_DIR . 'actions' .  DIRECTORY_SEPARATOR);   //controllers processing sumbitted data and preparing output
define("SESSIONS_DIR",  CURRENT_DIR . 'temp' . DIRECTORY_SEPARATOR . 'sessions' . DIRECTORY_SEPARATOR); //sessions are stored here
define("TEMP_DIR",  CURRENT_DIR . 'temp' . DIRECTORY_SEPARATOR . 'csv' . DIRECTORY_SEPARATOR); //all uploaded files will be copied here so that they won't be deleted between requests

define("SESSION_TTL",  60*60*24*30 ); //30 days
