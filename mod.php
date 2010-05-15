<?php
   /* Filename       :  mod.php
    * Description    :  interface for Administrators and Moderators
    * Version        :  1.0
    * Last modified  :  2008/11/11 10:21:44
    */

   $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
   require ("$DOCUMENT_ROOT/../site/begin_user_session.php");
   require ("$DOCUMENT_ROOT/../site/pageclass.php");
   require ("$DOCUMENT_ROOT/../site/display_functions.php");
   require ("$DOCUMENT_ROOT/../site/mod_functions.php");
   
   $mod = new PageClass ();
   
   /* check if the user is Moderator or Administrator */
   if (!isModerator($user) && !isAdministrator($user)) {
      $pagecontent = '<h2>Достъп отказан</h2>
                      <p class="error">Вие нямате право на достъп до
                      тази страница.</p>';
      
      $mod->SetTitle ('unix-heaven.org - Достъп отказан');
      $mod->SetContent ($pagecontent);
      $mod->Display ();
      exit (1);
   }
   
   $mod->SetTitle ('unix-heaven.org - Модераторски Панел');
   
   /* the marked articles are selected for deleting */
   if (isset($_POST['delete']))
      DeleteMarkedArticles ();
   
   /* the chosen articles are marked as approved */
   if (isset($_POST['approve']))
      ApproveMarkedArticles ($user);
   
   ShowArticles ($pagecontent, ((isset($_GET['show']) && !strcmp($_GET['show'], 'all')) || (isset($_POST['show']) && !strcmp($_POST['show'], 'all'))));
   $mod->SetContent ($pagecontent);
   $mod->Display ();
?>
