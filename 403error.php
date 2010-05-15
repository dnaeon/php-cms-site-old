<?php
   /* Filename       :  403error.php
    * Description    :  handles the occurence of a 403 error
    * Version        :  1.0
    * Last modified  :  2008/11/04 19:32:08
    */   
 
   $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
   require ("$DOCUMENT_ROOT/../site/begin_user_session.php");
   require ("$DOCUMENT_ROOT/../site/pageclass.php");
   require ("$DOCUMENT_ROOT/../site/display_functions.php");
   
   $pagecontent = '
   <h2>Грешка 403: Достъп отказан</h2>
   <p class="error">Вие нямате право на достъп до следния 
      файл/директория: </p><i>' . $_SERVER['REQUEST_URI'] . '</i>';
   
   $error403 = new PageClass ();
   $error403->SetTitle ("unix-heaven.org - Грешка 403!");
   $error403->SetContent ($pagecontent);
   $error403->Display ();
?>
