<?php
   /* Filename       :  news.php
    * Description    :  displays the latest news
    * Version        :  1.0
    * Last modified  :  2008/11/04 19:30:35
    * 
    * Functions defined in the file
    * -----------------------------
    * DisplayNewsById ()
    * DisplayNewsInformation ()
    * DisplayRecentNews ()
    * DisplayAllNews ()
    * -----------------------------
    */   

   $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
   require ("$DOCUMENT_ROOT/../site/begin_user_session.php");
   require ("$DOCUMENT_ROOT/../site/pageclass.php");
   require ("$DOCUMENT_ROOT/../site/display_functions.php");
   require ("$DOCUMENT_ROOT/../site/submit_functions.php");
   
   $news = new PageClass ();
   $news->SetTitle ("unix-heaven.org - Новини");
   
   $pagecontent  = '<h2>Новини</h2>' 
                 . (isset($_GET['show']) && !strcmp($_GET['show'], 'all')
                 ? '<div class="item"><a href="/news.php"><img src="/images/arrow.gif" border="0" alt="" />&nbsp;Виж последните новини</a></div>'
                 : '<div class="item"><a href="/news.php?show=all"><img src="/images/arrow.gif" border="0" alt="" />&nbsp;Виж всички новини</a></div>');
   
   /* view all news */
   if (isset($_GET['show']) && !strcmp($_GET['show'], 'all')) {
      DisplayAllNews ($pagecontent);
      
      $news->SetContent ($pagecontent);
      $news->Display ();
      exit (0);
   }
   
   /* view news by ID */
   if (isset($_GET['id']) && ereg("^[0-9]+$", $_GET['id'])) {
      if (!($result = GetArticleDataById ($article, $userinfo, $_GET['id'], 'news', TRUE))) {
         $pagecontent = '<h2>Новини</h2><p class="error">Новината, която търсете не може да бъде открита</p>
                         <p>За да намерите търсения от Вас документ можете да използвате
                         <a href="/search.php">търсачката</a>.</p>';
         $news->SetContent ($pagecontent);
         $news->Display ();
         exit (1);
      }
      
      ArrangeNewsContent ($article, $userinfo, $pagecontent);
      $news->SetTitle ("unix-heaven.org - Новини - " . htmlspecialchars($article['title']));
      $news->SetContent ($pagecontent);
      $news->Display ();
      exit (0);
   }
   
   /* display most recent news */
   DisplayRecentNews ($pagecontent);
   
   $news->SetContent ($pagecontent);
   $news->Display ();
   
   /* displays all news */
   function DisplayAllNews (&$pagecontent)
   {     
      if (!defined('__www_db_config__')) {
         $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
         require ("$DOCUMENT_ROOT/../site/www_db_config.php");
      }
      
      /* init the databases */
      Init_www_db ($articles_db);
      
      $results_per_page = 20;
      $total_query = "SELECT COUNT(article_id)
                      AS `total_results`
                      FROM `articles`
                      WHERE `category` = 'news'
                      AND `approved` = 'Y'
                      ORDER BY `created` ASC";
                      
      $pagelinks = ShowResultByPages ($articles_db, $total_query, $offset, $results_per_page, FALSE, $total); 
      
      $news_query = "SELECT `article_id`
                     FROM `articles`
                     WHERE `category` = 'news'
                     AND `approved` = 'Y'
                     ORDER BY `created` ASC
                     LIMIT {$offset}, {$results_per_page}";
                     
      $news_result = mysql_query ($news_query, $articles_db);
      $num_news = mysql_num_rows ($news_result);
      
      if (!mysql_num_rows($news_result)) {
         mysql_free_result ($news_result);
         mysql_close ($articles_db);
         return;
      }
      
      $count = 0;
      while ($row = mysql_fetch_array($news_result))
         $news_array[$count++] = $row['article_id'];
         
      mysql_free_result ($news_result);
      mysql_close ($articles_db);
      
      $pagecontent .= '<hr />';
      $i = 0;
      while ($news_array[$i++]) {
         GetArticleDataById ($article, $userinfo, $news_array[$i - 1], 'news', TRUE);
         $pagecontent .= "<div class=\"item\">
                          <a href=\"/news.php?id={$article['article_id']}\">
                          <img src=\"/images/arrow.gif\" border=\"0\" alt=\"\" />&nbsp;"
                          . stripslashes(htmlspecialchars($article['title'])) . "</a>
                          от <a href=\"mailto:{$userinfo['user_email']}\">{$userinfo['username']}</a>
                          на " . date("d-m-Y@H:i",$article['created']) . "\n</div>\n";
      }
      
      $pagecontent .= ($total['total_results'] && $offset < $total['total_results']
                   ?  '<hr /><p>Новини <b>' . ($offset + 1) . '</b> - <b>' .
                      ($offset + $num_news) . '</b> от общо <b>' .
                      $total['total_results'] . '</b></p>' . $pagelinks 
                   :  '<p class="error">Грешка: Страницата, която се опитвате да отворите не съществува.</p>');
   }
   
   /* displays the most recent news */
   function DisplayRecentNews (&$pagecontent)
   {
      /* number of news to show */
      $num_news = 10;
      
      if (!defined('__www_db_config__')) {
         $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
         require ("$DOCUMENT_ROOT/../site/www_db_config.php");
      }
      
      /* init the databases */
      Init_www_db ($articles_db);
      
      $news_query = "SELECT `article_id`
                     FROM `articles`
                     WHERE `category` = 'news'
                     AND `approved` = 'Y'
                     ORDER BY `created` DESC
                     LIMIT 0, {$num_news}";
      
      $news_result = mysql_query ($news_query, $articles_db);
      
      /* if there are no news to show */
      if (!mysql_num_rows($news_result)) {
         mysql_free_result ($news_result);
         mysql_close ($articles_db);
         return;
      }
         
      $count = 0;
      while ($row = mysql_fetch_array($news_result))
         $news_array[$count++] = $row['article_id'];
         
      mysql_free_result ($news_result);
      mysql_close ($articles_db);
      
      $i = 0;
      while ($news_array[$i++]) {
         GetArticleDataById($article, $userinfo, $news_array[$i - 1], 'news', TRUE);
         ArrangeNewsContent ($article, $userinfo, $pagecontent); 
      }
   }
   
   /* arranges the article content for preview as a news */
   function ArrangeNewsContent ($article, $userinfo, &$pagecontent)
   {
      $topic_url = 'http://www.unix-heaven.org/forums/viewtopic.php?f=38&t=' . $article['topic_id'];
      
      $pagecontent .= 
         "<br />
         <table class=\"news\">
         <tr>
           <td>
           <div class=\"section\"> " . stripslashes(htmlspecialchars($article['title'])) . "</div>
           <p class=\"author\">
           Автор: <a href=\"mailto:{$userinfo['user_email']}\">{$userinfo['username']}</a><br />
           Създадена на: " . date("d-m-Y@H:i",$article['created']) . "<br />
           Публикувана на: " . date("d-m-Y@H:i", $article['published']) . "<br />
           Последно редактиранa на: " . date("d-m-Y@H:i", $article['modified']) . "<br /></p>
         <br /><a class='commentslink' target='_blank' href='{$topic_url}'>Коментари и Дискусия</a><br />
           {$article['content']}
           </td>
         </tr>
         </table>";
   }  
?>
