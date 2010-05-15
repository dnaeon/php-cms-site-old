<?php
   /* Filename       :  thankyou.php
    * Description    :  thanks the user for the submit
    * Version        :  1.0
    * Last modified  :  2008/11/12 15:02:18
    */
   
   $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
   require ("$DOCUMENT_ROOT/../site/begin_user_session.php");
   require ("$DOCUMENT_ROOT/../site/pageclass.php");
   require ("$DOCUMENT_ROOT/../site/display_functions.php");
   
   $thankyou = new PageClass ();
   $thankyou->SetTitle ('unix-heaven.org - Изпращане на статия');
      
   $pagecontent = '<h2>Състояние на изпратената статия</h2>';
   
   if (isRegistered($user)) 
      $pagecontent .= '<p class="notice">Изпратената статия ще бъде прегледана 
                      от Администратора или Модераторите на сайта. Имайте на
                      предвид, че Администратора и Модераторите на сайта си
                      запазват правото да изтрият, редактират или дори да не
                      одобрят Вашата статия в случай, на некоректно съдържание
                      или нарушение на Правилата на Форумът или сайта.</p>';
   $pagecontent .= '<p>Статия е изпратена успешно! Благодарим Ви!</p>';
   
   $thankyou->SetContent ($pagecontent);
   $thankyou->Display ();
?>
