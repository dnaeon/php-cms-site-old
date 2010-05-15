<?php
   /* Filename       :  processmessage.php
    * Description    :  processes the user feedback
    * Version        :  1.0
    * Last modified  :  2008/11/04 19:22:24
    */

   $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
   require ("$DOCUMENT_ROOT/../site/begin_user_session.php");
   require ("$DOCUMENT_ROOT/../site/pageclass.php");
   require ("$DOCUMENT_ROOT/../site/display_functions.php");
   
   /* craete a class instance */
   $processmessage = new PageClass ();
   $processmessage->SetTitle ('unix-heaven.org - Изпращане на съобщение');
   $content = '<h2>Състояние на изпратеното съобщение</h2>';
   
   $name = $_POST['name'];
   $email = $_POST['email'];
   $message = $_POST['message'];
   
   $toaddress = 'daemon@unix-heaven.org, dnaeon@gmail.com';
   $fromaddress = 'From: webserver@unix-heaven.org';
   
   $subject = 'Message from the website - unix-heaven.org';
   $mailcontent = 'Име: ' . $name . "\n" .
                  'E-mail: '. $email . "\n" .
                  "Съдържание на съобщението: \n" . $message . "\n";
              
   /* validate the data */    
   if (!isset($name, $email, $message) || 
      empty($name) || empty($email) || empty($message)) {
         $content .= '<p class="error">Грешка: Не сте попълнили правилно
                      формата за изпращане.</p>
                      <p>Моля върнете се на предишната страница, 
                      за да попълните липсващите данни.</p>';
         $processmessage->SetContent ($content);
         $processmessage->Display ();
         exit (1);
   }
   
   /* check for a valid e-mail address */
   define ("MAILFORMAT", '^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$');
   if (!eregi(MAILFORMAT, $email)) {
      $content .= '<p class="error">Грешка: Не сте въвели валиден e-mail адрес.</p>
                   <p>Моля върнете се на предишната страница,
                   за да попълните липсващите данни.</p>';
      $processmessage->SetContent ($content);
      $processmessage->Display ();
      exit (1);
   }
   
   /* send the message */
   if (($result = @mail ($toaddress, $subject, $mailcontent, $fromaddress)))
      $content .= '<p>Вашето съобщение е изпратено успешно!</p>
                   <p>Благодарим Ви!</p>';
   else
      $content .= '<p>В момента не е възможно Вашето съобщение да бъде 
                   изпратено. Опитайте отново по-късно.</p>
                   <p>Моля да ни извините за причиненото неудобство.</p>';
   
   $processmessage->SetContent ($content);
   $processmessage->Display ();
?>
      
   
   
