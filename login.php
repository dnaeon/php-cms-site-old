<?php
   /* Filename       :  login.php
    * Description    :  self-explaining...
    * Version        :  1.0
    * Last modified  :  2008/11/04 19:24:45
    */   
   
   $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
   require ("$DOCUMENT_ROOT/../site/begin_user_session.php");
   require ("$DOCUMENT_ROOT/../site/pageclass.php");
   require ("$DOCUMENT_ROOT/../site/display_functions.php");
   
   $pagecontent = '
   <h2>Потребителски Вход</h2>
   <p>За да влезете в профила си, моля въведете Вашите данни в следната форма.</p>
   
   <form action="' . append_sid("/forums/ucp.php", 'mode=login', true, $user->session_id) . '" method="post">
      <table class="left_aligned_nav" cellspacing="0" cellpadding="4">
      <tr>
         <td>
            Потребител
         </td>
         <td align="right">
            <input type="text" name="username" size="20" maxlength="20" />
         </td>
      </tr>
      <tr>
         <td>
            Парола
         </td>
         <td align="right">
            <input type="password" name="password" size="20" maxlength="20" />
         </td>
      </tr>
      <tr>
         <td>
            Скрий ме
         </td>
         <td align="right">
            <input type="checkbox" class="radio" name="viewonline" />
         </td>
      </tr>
      <tr><td>
      <br />
      <input type="hidden" name="mode" value="login" />
      <input type="hidden" name="autologin" value="1" />
      <input type="submit" value="Вход" name="login" />
      <input type="hidden" name="redirect" value="/index.php" />
      </td></tr>
      </table>
      <br />
      
      </form>';
     
   $login = new PageClass ();
   $login->SetTitle ("unix-heaven.org - Потребителски вход");
   $login->SetContent ($pagecontent);
   $login->Display ();
?>
   
