<?php
   /* Filename       :  contacts.php
    * Description    :  a contacts form for user feedback
    * Version        :  1.0
    * Last modified  :  2008/11/04 19:17:18
    */

   $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
   require ("$DOCUMENT_ROOT/../site/begin_user_session.php");
   require ("$DOCUMENT_ROOT/../site/pageclass.php");
   require ("$DOCUMENT_ROOT/../site/display_functions.php");
   
   $pagecontent = '
   <h2>Контакти</h2>
   <p>За да се свържете с нас можете да ни пишете на следните e-mail адреси
   или като използвате формата по-долу за да изпратите Вашето съобщение.</p>
   <p>Адреси за контакт:</p>
   <a href="mailto:daemon@unix-heaven.org">daemon AT unix-heaven DOT org</a><br />
   <a href="mailto:dnaeon@gmail.com">dnaeon AT gmail DOT com</a>
   <hr />
   <h2>Форма за контакти</h2><br />
   
   <form action="/processmessage.php" method="post">
   <div align="center">
   <table class="nav">
   <tr>
   <td>
      <div class="section">Вашето име</div>
      <input type="text" name="name" size="40" maxlength="40" /><br /><br />
      
      <div class="section">Вашият e-mail адрес</div>
      <input type="text" name="email" size="40" maxlength="40" /><br /><br />
   
      <div class="section">Вашето съобщение</div>
      <textarea rows="10" cols="40" name="message"></textarea><br /><br />
   </td>
   </tr>
   <tr>
   <td align="right">
      <input type="reset" name="reset" value="Изчисти" />&nbsp;&nbsp;&nbsp;&nbsp;
      <input type="submit" name="submit" value="Изпрати" />
   </td>
   </tr>
   </table>
   </div>
   </form>
   ';
   
   $contacts = new PageClass ();
   $contacts->SetTitle ("unix-heaven.org - Контакти");
   $contacts->SetContent ($pagecontent);
   $contacts->Display ();
?>
