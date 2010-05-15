<?php
   /* Filename       :  logout.php
    * Description    :  kills the users session
    * Version        :  1.0
    * Last modified  :  2008/11/04 19:33:05
    */   

   $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
   require ("$DOCUMENT_ROOT/../site/begin_user_session.php");
   require ("$DOCUMENT_ROOT/../site/pageclass.php");
   require ("$DOCUMENT_ROOT/../site/display_functions.php");
   
   $user->session_kill();
   $user->session_begin();   
   header ("Location: /index.php");
?>
