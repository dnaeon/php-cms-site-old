<?php
   /* Filename       :  www_db_config.php
    * Description    :  settings for the databases used by the site
    * Version        :  1.2
    * Last modified  :  2008/11/10 14:11:32
    * 
    * Functions defined in the file
    * -----------------------------
    * Init_www_db ()
    * Init_phpbb3_db ()
    * -----------------------------
    */
   
   if (!defined('__www_db_config_php__'))
      define ('__www_db_config_php__', TRUE);
      
   /* inits the `www_db` database */
   function Init_www_db (&$articles_db)
   {
      $www_db_host = 'localhost';
      $www_db_port = '3306';
      $www_db_name = 'www_db';
      $www_db_user = 'www_user';
      $www_db_pass = 'password';
        
      /* connect to the `articles` database */
      $articles_db = mysql_connect ("$www_db_host:$www_db_port", $www_db_user, $www_db_pass);
      $articles_db_selected = mysql_select_db ($www_db_name, $articles_db);
      if (!$articles_db || !$articles_db_selected) { 
         echo 'Не мога да се свържа с базата данни. Моля опитайте отново по-късно.';
         exit (1);
      }
        
      mysql_query ('SET NAMES UTF8', $articles_db);
   }
   
   /* inits the `phpbb3_db` database */
   function Init_phpbb3_db (&$phpbb3_db)
   {
      $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
      require ("$DOCUMENT_ROOT/forums/config.php");
      
      /* connect to the `phpbb3_db` database */
      $phpbb3_db = mysql_connect ("$dbhost:$dbport", $dbuser, $dbpasswd);
      $phpbb3_db_selected = mysql_select_db ($dbname, $phpbb3_db);
      if (!$phpbb3_db || !$phpbb3_db_selected) {
         echo 'Не мога да се свържа с базата данни. Моля опитайте отново по-късно.';
         exit (1);
      }
      
      mysql_query ('SET NAMES UTF8', $phpbb3_db);
   }
?>
