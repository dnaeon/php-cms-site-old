<?php
   /* Filename       :  404error.php
    * Description    :  handles the occurence of a 404 error
    * Version        :  1.0
    * Last modified  :  2008/11/04 19:31:34
    */   
   
   $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
   require ("$DOCUMENT_ROOT/../site/begin_user_session.php");
   require ("$DOCUMENT_ROOT/../site/pageclass.php");
   require ("$DOCUMENT_ROOT/../site/display_functions.php");
   
   $pagecontent = '
      <h2>Грешка 404: Липсващ файл/директория</h2>
      <p class="error">Документът, който се опитвате да 
         отворите не съществува.</p>
      <p>За да намерите търсения от Вас документ можете да използвате
         <a href="/search.php">търсачката</a>.</p>
   ';
   
   $error404 = new PageClass ();
   $error404->SetTitle("unix-heaven.org - Грешка 404!");
   $error404->SetContent ($pagecontent);
   $error404->Display ();
?>
