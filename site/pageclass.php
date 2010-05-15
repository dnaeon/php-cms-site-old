<?php
   /* Filename       :  pageclass.php
    * Description    :  define a class used by every page of the site
    * Version        :  1.1
    * Last modified  :  2008/11/10 14:04:07
    * 
    * Classes defined in the file
    * ---------------------------------
    * PageClass ()
    * ---------------------------------
    * 
    * Class methods defined in the file
    * ---------------------------------
    * SetContent ()
    * SetTitle ()
    * SetMeta ()
    * Display ()
    * DisplayTitle ()
    * DisplayMeta ()
    * DisplayStyles ()
    * DisplayHeader ()
    * DisplayMenu ()
    * IsUrlCurrentPage ()
    * DisplayButtons ()
    * DisplayContent ()
    * DisplayBody ()
    * LeftNavigation ()
    * RightNavigation ()
    * ---------------------------------
    */
   
   if (!defined('__pageclass_php__'))
      define ('__pageclass_php__', TRUE);
   
   /* define the class */
   class PageClass {
      
      /* page content */
      protected $content;
      
      /* a default title for each page */
      protected $title = 'unix-heaven.org - Spread the Knowledge!';
      
      /* -- meta tags -- */
      protected $charset = 'http-equiv="Content-Type" content="text/html; charset=utf-8"';
      
      protected $keywords = array ('unix', 'unix-heaven', 'bulgaria',
                                   'bulgarian', 'help', 'programming',
                                   'doc', 'documentation', 'bsd', 'FreeBSD',
                                   'OpenBSD', 'NetBSD', 'Solaris', 'Linux',
                                   'QNX', 'forums');
                                   
      protected $description = 'unix-heaven.org - Spread the Knowledge!';
      protected $author = 'Marin Atanasov - daemon@unix-heaven.org - http://www.unix-heaven.org/';
      /* -- end of meta tags -- */
      
      protected $license = '
         <a rel="license" href="http://creativecommons.org/licenses/by-sa/2.5/bg/">
         <img alt="Creative Commons License" style="border-width:0" 
         src="http://i.creativecommons.org/l/by-sa/2.5/bg/80x15.png" /></a>';
            
      /* top navigation menu buttons */
      protected $buttons = array ('Начало'   => '/index.php',
                                  'Новини'   => '/news.php',
                                  'Статии'   => '/docs.php',
                                  'Форуми'   => '/forums/',
                                  'Търсене'  => '/search.php',
                                  'Контакти' => '/contacts.php');
      
      /* --- PUBLIC CLASS METHODS --- */
      
      /* sets the content of the page */
      public function SetContent ($newcontent) 
      {
         $this->content = $newcontent;
      }
      
      /* sets the page title */
      public function SetTitle ($newtitle)
      {
         $this->title = $newtitle;
      }
      
      /* sets the meta tags */
      public function SetMeta ($newkeywords, $newdescription, $newauthor)
      {
         $this->keywords = $newkeywords;
         $this->description = $newdescription;
         $this->author = $newauthor;
      }
      
      /* displays the page */
      public function Display ()
      {
         echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\n";
         echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="bg" lang="bg">' . "\n";
       
         echo "<head>\n";
         $this->DisplayTitle ();
         $this->DisplayMeta ();
         $this->DisplayStyles ();
         echo "</head>\n<body>\n";
         $this->DisplayHeader ();
         $this->DisplayMenu ($this->buttons);
         $this->DisplayContent ($this->content);
         echo "</body>\n</html>";
      }
      
      /* --- END OF PUBLIC CLASS METHODS --- */
      
      /* --- PROTECTED CLASS METHODS --- */
      
      /* displays the page title */
      protected function DisplayTitle ()
      {
         echo "<title>$this->title</title>\n";
      }
               
      /* sets the meta tags */
      protected function DisplayMeta ()
      {
         echo '<script src="js_quicktags.js" type="text/javascript"></script>';
         echo "<meta $this->charset />\n";
         echo '<meta name="keywords" content="' . implode (", ", $this->keywords) . "\" />\n";
         echo "<meta name=\"description\" content=\"$this->description\" />\n";
         echo "<meta name=\"author\" content=\"$this->author\" />\n";
      }
      
      /* include CSS styles and favicon */
      protected function DisplayStyles ()
      {
         echo '<link rel="shortcut icon" href="/images/favicon.ico" />' . "\n";
         echo '<link rel="stylesheet" type="text/css" href="/styles.css" />' . "\n";
         
         /* needed for IE only */
?>
         <!--[IF IE]>
         <style type=\"text/css\">
         .footer { width: 540px; }
         </style>
         <![ENDIF]-->
<?php
      }
      
      /* display the header of the page */
      protected function DisplayHeader ()
      {
         echo '<div class="pageheader" align="center">' . "\n";
         echo '<a href="/index.php"><img src="/images/header.jpg"'
              . ' border="0" alt="' . $this->description 
              . '" title="' . $this->description . "\" /></a>\n</div>\n";
      }
      
      /* displays the top menu */
      protected function DisplayMenu ()
      {
         echo "<div class=\"listmenu\">\n<ul>\n";
         
         while (list($name, $url) = each ($this->buttons))
            $this->DisplayButtons ($name, $url, $this->IsURLCurrentPage($url));

         echo "</ul>\n</div>\n";
      }
      
      /* checks what is the current page */
      protected function IsURLCurrentPage ($url) 
      {
         return ((strstr($_SERVER['SCRIPT_NAME'], $url) == FALSE) ? FALSE : TRUE);
      }
   
      /* displays the top navigation menu buttons */
      protected function DisplayButtons ($name, $url, $active)
      {                  
         if (!$active)
            echo "\t<li><a href=\"$url\">$name</a></li>\n";
         else
            echo "\t<li><span>$name</span></li>\n";
      }
      
      /* displays the page content - right, left navigation menu and body */
      protected function DisplayContent ($content)
      {
?>
         <!-- page content starts here -->
         <div align="center">
         <table class="content" border="0" cellpadding="0" cellspacing="2">
         <tr>
            <!-- left navigation menu -->
            <?php $this->LeftNavigation (); ?>
            <!-- end of left navigation menu -->

            <!-- page body starts here -->
            <?php $this->DisplayBody (); ?>
            <!-- page body ends here -->
           
            <!-- right navigation menu -->
            <?php $this->RightNavigation (); ?>
            <!-- end of right navigation menu --> 

         </tr>
         </table> 
         </div> 
         <!-- page content ends here -->
<?php
      }
      
      /* displays the left navigation menu - current time, login menu, search form and links */
      protected function LeftNavigation () 
      {
?>
         <td valign="top">
            <!-- display the current time -->
            <?php DisplayCurrentTime (); ?>
            <!-- end of display current time -->
            
            <!-- check for login -->
            <?php DisplayLoginMenu (); ?>
            <!-- end of check for login -->
            
            <!-- display the search form -->
            <?php DisplaySearchForm (); ?>
            <!-- end of display search form -->
            
            <!-- display navigation links -->
            <?php DisplayLinks (); ?>
            <!-- end of display navigation links -->
         </td>
<?php
      }
      
      /* displays the body of the page */
      protected function DisplayBody () 
      {
?>
         <td valign="top">

            <!-- outer table content -->
            <table class="contentitem" border="0" cellpadding="0" cellspacing="0">
            <tr>
            <td>
      
               <!-- inner table content -->
               <table>
               <tr>
               <td align="left">
                  <div>
                  
                  <!-- page text goes here -->
                  <?php echo $this->content; ?>                        
                  <!-- page text ends here -->
                  
                  <!-- page footer goes here -->
                  <?php 
                     echo '<div class="footer">
                           <div align="center">
                           <table>
                           <tr>
                              <td>' . $this->license . '</td>
                              <td align="left">&nbsp;Marin Atanasov</td>
                           </tr>
                           <tr>
                              <td>' . $this->license . '</td>
                              <td align="left">&nbsp;unix-heaven.org</td>
                           </tr>
                           </table>
                           </div>
                           </div>' . "\n"; 
                  ?>
                  <!-- page footer ends here -->
                  </div>
               </td>
               </tr>
               </table>
               <!-- end of inner table content -->
               
            </td>
            </tr>
            </table> 
            <!-- end of outer table content -->
         </td>
<?php 
      }
      
      /* displays the right navigation menu - latest news and forum topics */
      protected function RightNavigation () 
      {
?>
         <td valign="top">
         
            <!-- display latest news -->
            <?php DisplayLatestNews (); ?>
            <!-- end of display latest news -->
            
            <!-- display latest forum topics -->
            <?php DisplayLatestTopics (); ?>
            <!-- end of display latest forum topics -->
         </td>
<?php
      }
      
      /* --- END OF PROTECTED CLASS METHODS --- */
   }
?>
