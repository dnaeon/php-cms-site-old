<?php
   /* Filename       :  submit_article.php
    * Description    :  interface for submiting articles
    * Version        :  1.3
    * Last modified  :  2008/11/13 17:12:02
    */     

   $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
   require ("$DOCUMENT_ROOT/../site/begin_user_session.php");
   require ("$DOCUMENT_ROOT/../site/pageclass.php");
   require ("$DOCUMENT_ROOT/../site/display_functions.php");
   require ("$DOCUMENT_ROOT/../site/submit_functions.php");
   
   $submit = new PageClass ();
   $submit->SetTitle ('unix-heaven.org - Добавяне на нова статия');
   
   /* check if the user has logged-in */
   if (isAnonymous($user)) {
      $pagecontent = '<h2>Достъп отказан</h2>
                     <p class="error">Не сте влезли в системата. 
                     Моля влезте първо във Вашия профил.</p>';
      $submit->SetContent($pagecontent);
      $submit->Display ();
      exit (1);
   }
   
   /* get the data sent by the form */
   GetFormData ($article);
   
   /* a moderator or administrator selected to preview an unapproved article */
   if (isset($_GET['mode']) && isset($_GET['id']) 
         && !strcmp($_GET['mode'], 'preview') && ereg("^[0-9]+$", $_GET['id'])) {
      
         /* check user permissions */
         if (!isModerator($user) && !isAdministrator($user)) {
            $pagecontent = '<h2>Достъп отказан</h2>
                            <p class="error">Вие нямате право на достъп до тази страница</p>';
            
            $submit->SetTitle ('unix-heaven.org - Достъп отказан');
            $submit->SetContent ($pagecontent);
            $submit->Display ();
            exit (1);
         }
         
         $submit->SetTitle ('unix-heaven.org - Преглед/Редакция на статия');
         PreviewEditArticle ($pagecontent, $_GET['id']);
         $submit->SetContent ($pagecontent);
         $submit->Display ();
         exit (0);
   }
   
   /* edit the article */
   if (isset($_POST['edit_mode'])) {
      /* get article data */
      $submit->SetTitle ('unix-heaven.org - Редакция на статия');
      GetArticleDataById ($article, $userinfo, $_POST['edit_id']);
   }
         
   /* check if the user selected to preview the article */
   if (isset($_POST['preview'])) {
      $submit->SetTitle ('unix-heaven.org - Преглед на статия');
      PreviewArticle ($pagecontent, $user);      
   }
   
   /* check if the user has selected to submit the article */
   if (isset($_POST['add'])) {
      $submit->SetTitle ('unix-heaven.org - Изпращане на статия');
      SubmitArticle ($pagecontent, $user);

      $submit->SetContent ($pagecontent);
      $submit->Display ();
      exit (0);
   }              
   
   /* create the article form */
   CreateArticleForm ($pagecontent, $article);
      
   $submit->SetContent ($pagecontent);
   $submit->Display ();
?>
