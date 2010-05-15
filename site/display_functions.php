<?php
   /*
    * Filename       :  display_functions.php
    * Description    :  defines various functions for displaying the page
    * Version        :  1.2
    * Last modified  :  2008/11/10 14:45:51
    * Functions defined in the file
    * -----------------------------
    * DisplayLinks ()
    * DisplayCurrentTime ()
    * DisplayLatestNews ()
    * DisplayLatestTopics ()
    * DisplaySearchForm ()
    * DisplayLoginMenu ()
    * -----------------------------
    */
   
   if (!defined('__display_functions_php__')) 
      define ('__display_functions_php__', TRUE);

   /* displays the links at the left navigation menu */
   function DisplayLinks ()
   {
      /* links at the left navigation menu */
      $links = array ('NetBSD.org'     => 'http://www.netbsd.org/',
                      'FreeBSD.org'    => 'http://www.freebsd.org/',
                      'OpenBSD.org'    => 'http://www.openbsd.org/',
                      'DaemonForums'   => 'http://www.daemonforums.org/',
                      'PC-BSD'         => 'http://www.pcbsd.org/',
                      'BSDNews'        => 'http://bsdnews.com/',
                      'Линукс БГ'      => 'http://www.linux-bg.org/',
                      'FreeBSD BG'     => 'http://www.freebsd-bg.org', 
                      'BSDGuides.org'  => 'http://www.bsdguides.org/',
                      'IT Toolbox'     => 'http://it.toolbox.com/',
                      'FreeBSDNews'    => 'http://www.freebsdnews.net/',
                      'FreeBSDRocks'   => 'http://www.freebsdrocks.net/',
                      'FreeBSD Wiki'   => 'http://en.wikipedia.org/wiki/FreeBSD');
?>
      
      <table class="nav">
         <tr>
            <td>
               <div class="minheight">
               <div class="section">Връзки</div>
<?php
                  while (list($name, $url) = each($links))
                     echo "<div class=\"item\"><a href=\"$url\" target=\"_blank\">$name</a></div>\n";
?>
               </div>
            </td>
         </tr>
      </table>
<?php 
   }
 
   /* displays current date and time */
   function DisplayCurrentTime ()
   {
      /* current month in bulgarian */
      $bg_month = array ('January'     => 'Януари',
                         'February'    => 'Февруари',
                         'March'       => 'Март',
                         'April'       => 'Април',
                         'May'         => 'Май',
                         'June'        => 'Юни',
                         'July'        => 'Юли',
                         'August'      => 'Август',
                         'September'   => 'Септември',
                         'October'     => 'Октомври',
                         'November'    => 'Ноември',
                         'December'    => 'Декември');
      
      /* current day in bulgarian */
      $bg_weekday = array ('Sunday'    => 'Нед',
                           'Monday'    => 'Пон',
                           'Tuesday'   => 'Вто',
                           'Wednesday' => 'Сря',
                           'Thursday'  => 'Чет',
                           'Friday'    => 'Пет',
                           'Saturday'  => 'Съб');
?>                       
      <table class="nav">
         <tr>
            <td>
               <div class="section">Текущо време</div>
               <div class="item">
<?php 
                  $curr_date = getdate ();
                  printf ("%02s:%02s, %s %s %s, %s", $curr_date['hours'],
                     $curr_date['minutes'], $bg_weekday[$curr_date['weekday']],
                     $curr_date['mday'], $bg_month[$curr_date['month']],
                     $curr_date['year']);   
?> 
               </div>
            </td>
         </tr>
      </table>
<?php
   }
   
   /* displays the latest news */
   function DisplayLatestNews ()
   {
      $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
      
      if (!defined('__www_db_config_php__'))
         require ("$DOCUMENT_ROOT/../site/www_db_config.php");
      
      if (!defined('__submit_functions_php__'))
         require ("$DOCUMENT_ROOT/../site/submit_functions.php");
      
      /* truncate the news title to a certain length */
      define ('TITLE_MAX_LEN', 20);
      
      /* max number of news to show */
      $num_news = 6;
      
      /* init the databases */
      Init_www_db ($articles_db);
      
      $news_query = "SELECT `article_id`
                     FROM `articles`
                     WHERE `category` = 'news'
                     AND `approved` = 'Y'
                     GROUP BY `created` DESC
                     LIMIT 0, {$num_news}";
      
      $news_result = mysql_query ($news_query, $articles_db);
      
      $count = 0;
      while ($row = mysql_fetch_array($news_result))
         $news_array[$count++] = $row['article_id'];
      
      mysql_free_result ($news_result);
      mysql_close ($articles_db);
      
?>
      <table class="left_aligned_nav">
         <tr>
            <td>
               <div class="minheight">
               <div class="section">Последни Новини</div>
<?php
                  $i = 0;
                  while ($news_array[$i++]) {                    
                     GetArticleDataById ($article, $userinfo, $news_array[$i - 1], 'news', TRUE);

                     $created = date ("d-m-Y@H:i", $article['created']);
                     $news_title = (utf8_strlen($article['title']) > TITLE_MAX_LEN ? truncate_string ($article['title'], TITLE_MAX_LEN) . '...' : $article['title']);
                     
                     echo "<div class=\"item\">
                           <a href=\"/news.php?id={$article['article_id']}\" title=\" " . htmlspecialchars($article['title']) . "\">
                           <img src=\"/images/arrow.gif\" border=\"0\" alt=\"\" />&nbsp;" . htmlspecialchars($news_title) . "</a>
                           от <a href=\"/forums/memberlist.php?mode=viewprofile&amp;u={$article['user_id']}\">{$userinfo['username']}</a>
                           на {$created}</div>\n";
                  }
?>
               </div>
            </td>
         </tr>
      </table>
<?php
   }
   
   /* displays the latest news */
   function DisplayLatestTopics ()
   {
      if (!defined('__www_db_config_php__')) {
         $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
         require ("$DOCUMENT_ROOT/../site/www_db_config.php");
      }
      
      /* truncate the topic title to a certain length */
      define ('TITLE_MAX_LEN', 25);
      
      /* number of posts to show */
      $num_posts = 6;
      
      /* init the database */
      Init_phpbb3_db ($phpbb3_db);
            
      $topics_query = "SELECT * FROM `phpbb_topics`
                       WHERE `topic_approved` = 1
                       AND `forum_id` <> 17
                       ORDER BY `topic_last_post_time` DESC 
                       LIMIT 0, {$num_posts}";
      
      $topics_result = mysql_query ($topics_query, $phpbb3_db);
?>
      <table class="left_aligned_nav">
         <tr>
            <td>
               <div class="minheight">
               <div class="section">Последни теми от Форумите</div>
<?php
               while ($row = mysql_fetch_array ($topics_result)) {
                  /* get the topic date */
                  $date = date ("d-m-Y@H:i", $row['topic_last_post_time']);
                  
                  /* truncate the topic title */
                  $topic_title = (utf8_strlen($row['topic_title']) > TITLE_MAX_LEN ? truncate_string ($row['topic_title'], TITLE_MAX_LEN) . '...' : $row['topic_title']);
                  
                  /* create a link */                  
                  echo "<div class=\"item\"><a href=\"/forums/viewtopic.php?f={$row['forum_id']}&amp;t={$row['topic_id']}\" title=\"{$row['topic_title']}\">
                        <img src=\"/images/arrow.gif\" border=\"0\" alt=\"\" />&nbsp;{$topic_title}</a>, 
                        от <a href=\"/forums/memberlist.php?mode=viewprofile&amp;u={$row['topic_last_poster_id']}\">{$row['topic_last_poster_name']}</a>
                        на $date</div>\n";
               }
               
               /* close the database and free the result */
               mysql_free_result ($topics_result);
               mysql_close ($phpbb3_db);
?>
               </div>
            </td>
         </tr>
      </table>          
<?php
   }
   
   /* web site search form */
   function DisplaySearchForm ()
   {
?>
      <table class="nav">
         <tr>
            <td>
               <form action="http://www.google.com/search" method="get" target="_blank">
               <input type="hidden" name="sitesearch" value="http://www.unix-heaven.org" />
               <div class="section">Търсене</div>
               <div class="item"><input alt="search" type="text" name="as_q" size="20" value="" /></div>
               <div class="item"><input type="submit" value="Търси!" /></div>
               <br />
               <div class="item" align="left"><a href="/search.php">подробно търсене...</a></div>
               </form>
            </td>
         </tr>
      </table>
<?php
   }
         
    /* web site login system */
   function DisplayLoginMenu ()
   {     
      /* $user is global object comming from phpbb3 */
      global $user;
?> 
      <table class="nav">
      <tr>
         <td>
            <div class="section">Профил</div>
<?php

         /* check if the user has logged-in */
         if (isAnonymous($user)) {
?>
            <div class="item"><a href="/login.php">Вход</a></div>
            <div class="item"><a href="/forums/ucp.php?mode=register">
               Регистрация</a></div>
            <div class="item"><a href="/forums/ucp.php?mode=sendpassword">
               Забравена парола?</a></div>
<?php    
         }
         
         /* user has logged-in */
         else {
            echo '<div class="item"><a href="/forums/ucp.php">' 
                 . $user->data['username'] . "</a></div>\n";
            echo '<div class="item"><a href="/forums/ucp.php?i=pm&folder=inbox">
                  Съобщения (' . $user->data['user_unread_privmsg'] . ")</a></div>\n";
            echo '<div class="item"><a href="/submit.php">Добавяне на статия</a></div>';
            
            /* show articles waiting to be approved */
            if (isAdministrator($user) || isModerator($user)) {
               if (!defined('__www_db_config_php__')) {
                  $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
                  require ("$DOCUMENT_ROOT/../site/www_db_config.php");
               }
               
               /* init the database */
               Init_www_db ($articles_db);
               
               $new_articles_query = "SELECT NULL FROM `articles`
                                      WHERE `approved` = 'N'";
               $new_articles_result = mysql_query ($new_articles_query, $articles_db);
                           
               echo '<div class="item"><a href="/mod.php">Нови статии ('
                    . mysql_num_rows ($new_articles_result) . ')</a></div>';
               
               mysql_free_result ($new_articles_result);
               mysql_close ($articles_db);   
            }
            
            /* show admin panel */
            if (isAdministrator($user))
               echo '<div class="item"><a href="http://www.unix-heaven.org/webmail">
                     Пощенска кутия</a></div>
                     <div class="item"><a href="/adm.php">Админ Панел</a></div>';
               
            echo '<div class="item"><a href="/logout.php">Изход</a></div>' . "\n";
         }
?>
         </td>
      </tr>
      </table>
<?php
   }
?>
