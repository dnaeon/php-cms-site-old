<?php
   /* Filename       :  begin_user_session.php
    * Description    :  handles the user sessions
    * Version        :  1.2
    * Last modified  :  2008/11/10 14:14:01
    * 
    * Functions defined in the file
    * -----------------------------
    * IsAnonymous ()
    * IsRegistered ()
    * IsAdministrator ()
    * IsModerator ()
    */
   
   if (!defined('__begin_user_session_php__'))
      define ('__begin_user_session_php__', TRUE);

   /* begin the user session using phpbb3 */
   define('IN_PHPBB', true);
   $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
   $phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : "$DOCUMENT_ROOT/forums/";
   $phpEx = substr(strrchr(__FILE__, '.'), 1);
   require ("{$phpbb_root_path}common.{$phpEx}");
   require ("{$phpbb_root_path}/includes/functions_display.php");

   /* Start session management */
   $user->session_begin();
   $auth->acl($user->data);
   $user->setup();
   
   /* phpbb3 defines the following groups as valid
    * --------------------------------------------
    * 1  -  GUESTS
    * 2  -  REGISTERED
    * 3  -  REGISTERED_COPPA
    * 4  -  GLOBAL_MODERATORS
    * 5  -  ADMINISTRATORS
    * 6  -  BOTS
    * --------------------------------------------
    */
   
   /* checks if the user is anonymous */
   function isAnonymous ($user)
   {
      return (($user->data['group_id'] == 1) || ($user->data['group_id'] == 3)
               || ($user->data['group_id'] == 6) ? TRUE : FALSE);
   }
   
   /* checks if the user is registered */
   function isRegistered ($user)
   {
      return (($user->data['group_id'] == 2) ? TRUE : FALSE);
   }
   
   /* checks if the user is Administrator */
   function isAdministrator ($user)
   {
      return (($user->data['group_id'] == 5) ? TRUE : FALSE);
   }
   
   /* checks if the user is Moderator */
   function isModerator ($user)
   {
      return (($user->data['group_id'] == 4) ? TRUE : FALSE);
   }
?>
