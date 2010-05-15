<?php
   /* Filename       :  mod_functions.php
    * Description    :  defines functions for moderating articles
    * Version        :  1.2
    * Last modified  :  2008/11/14 17:15:34
    * 
    * Functions defined in the file
    * -----------------------------
    * ApproveMarkedArticles ()
    * DeleteMarkedArticles ()
    * ShowArticles ()
    * -----------------------------
    */

   /* deletes marked articles */
   function DeleteMarkedArticles ()
   {
      /* user is global */
      global $user;
      
      if (!defined('__www_db_config_php__')) {
         $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
         require ("$DOCUMENT_ROOT/../site/www_db_config.php");
      }
   
      /* init the database */
      Init_www_db ($articles_db);
      
      /* find the article IDs to delete */
      $find_ids_query = "SELECT `article_id` 
                         FROM `articles`"
                      .  (!isAdministrator($user) 
                      ?  "WHERE `approved` = 'N'"
                      :  "")
                      .  "ORDER BY `created` ASC";
                         
      $find_ids_result = mysql_query ($find_ids_query, $articles_db);
      
      /* delete the marked articles */
      while ($row = mysql_fetch_array($find_ids_result)) {
         if (isset($_POST["id{$row['article_id']}"]) && 
            !strcmp($_POST["id{$row['article_id']}"], "id{$row['article_id']}")) {
               mysql_query ("DELETE FROM `articles`, `keywords`
                             USING `articles` INNER JOIN `keywords`
                             WHERE articles.article_id = {$row['article_id']}
                             AND keywords.article_id = {$row['article_id']}", $articles_db);
         }
      }
      
      mysql_free_result ($find_ids_result);
      mysql_close ($articles_db);
   }
   
   /* approves marked articles */
   function ApproveMarkedArticles ($userinfo)
   {
      if (!defined('__www_db_config_php__')) {
         $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
         require ("$DOCUMENT_ROOT/../site/www_db_config.php");
      }
   
      /* init the databases */
      Init_www_db ($articles_db);
	  Init_phpbb3_db ($phpbb3_db);
      
      /* find the article IDs to approve */
      $find_ids_query = "SELECT `article_id`, `topic_id`
                         FROM `articles`
                         WHERE `approved` = 'N'
                         ORDER BY `created` ASC";
                         
      $find_ids_result = mysql_query ($find_ids_query, $articles_db);
      
      $published = time ();
      
      /* approve the marked articles */
      while ($row = mysql_fetch_array($find_ids_result)) {
         if (isset($_POST["id{$row['article_id']}"]) && 
            !strcmp($_POST["id{$row['article_id']}"], "id{$row['article_id']}")) {
               
			   $article_approve_query = "UPDATE `articles`
										 SET `approved` = 'Y',
										 `published` = {$published},
										 `approved_by` = {$userinfo->data['user_id']}
										 WHERE article_id = {$row['article_id']}";
			   
			   $topic_approve_query = "UPDATE `phpbb_topics`
									   SET `topic_approved` = 1
									   WHERE `topic_id` = {$row['topic_id']}";
						   
			   mysql_query ($article_approve_query, $articles_db);
			   mysql_query ($topic_approve_query, $phpbb3_db);
         }
      }
      
      mysql_free_result ($find_ids_result);
      mysql_close ($articles_db);
	  mysql_close ($phpbb3_db);
   }
   
   /* displays new or all articles */
   function ShowArticles (&$pagecontent, $showall = FALSE)
   {
      $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
      
      if (!defined('__www_db_config_php__'))
         require ("$DOCUMENT_ROOT/../site/www_db_config.php");
      
      if (!defined('__submit_functions_php__'))
         require ("$DOCUMENT_ROOT/../site/submit_functions.php");
   
      /* init the databases */
      Init_www_db ($articles_db);
      
      /* max lenght of username and article title to show */
      define ('MOD_TITLE_MAX_LEN', 15);
      define ('MOD_USERNAME_MAX_LEN', 10);
      
      /* find the total number of articles */
      $articles_per_page = 15;
      $total_query = "SELECT COUNT(article_id)
                      AS `total_results`
                      FROM `articles`"
                   .  ($showall ? '' : "WHERE `approved` = 'N'");
      
      $pagelinks = ShowResultByPages ($articles_db, $total_query, $offset, $articles_per_page, $showall, $total);
      
      /* list the articles */
      $articles_query = "SELECT `article_id`
                         FROM `articles`" . ($showall ? '' : "WHERE `approved` = 'N'") .
                        "ORDER BY `created` ASC
                         LIMIT {$offset}, {$articles_per_page}";
      
      $articles_result = mysql_query ($articles_query, $articles_db);
      
      /* number of articles */
      $num_articles = mysql_num_rows ($articles_result);
      
      $pagecontent = ($showall ? '<h2>Преглед на всички статии</h2>
                                  <p>Общ брой статии: ' . $total['total_results'] . '</p>'
                               : '<h2>Статии чакащи одобрение</h2>
                                  <p>Нови статии: ' . $total['total_results'] . '</p>');
      
      $pagecontent .= '<div class="item">'
                    . (isset($_GET['show']) && !strcmp($_GET['show'], 'all') 
                    ? '<a href="/mod.php" title="Виж новите статии"><img src="/images/arrow.gif" border="0" alt="" />Виж новите статии</a><br />'
                    : '<a href="/mod.php?show=all" title="Виж всички статии"><img src="/images/arrow.gif" border="0" alt="" />Виж всички статии</a><br />')
                    . '</div>';
      
      /* if there are no articles */
      if (!$num_articles)
         return;
         
      $pagecontent .= '<p>Резултати <b>' . ($offset + 1) .'</b> - <b>' . ($offset + $num_articles) . '</b> от общо <b>' . $total['total_results'] . '</b></p>';
      
      $pagecontent .= '<form action="/mod.php" method="post">
                          <table class="news" border="1" cellspacing="2" cellpadding="2">
                          <tr>
                             <th bgcolor=#B7B30F>ID#</th>
                             <th bgcolor=#B7B30F>Име</th>
                             <th bgcolor=#B7B30F>Автор</th>
                             <th bgcolor=#B7B30F>Категория</th>
                             <th bgcolor=#B7B30F>Дата</th>
                             <th bgcolor=#B7B30F>Одобрена</th>
                             <th bgcolor=#B7B30F>Избери</th>
                          </tr>';
                          
      /* display the articles */
      $count = 0;
      while ($row = mysql_fetch_array ($articles_result)) 
         $articles_array[$count++] = $row['article_id'];
         
      mysql_free_result ($articles_result);
      mysql_close ($articles_db);
      
      $i = 0;
      while ($articles_array[$i]) {
         GetArticleDataById ($article, $userinfo, $articles_array[$i]);
         
         $color = ($i % 2) ? '#B5EF81' : '#F4B7AE';
         $i++;

         $article['title'] = htmlspecialchars($article['title']);
         
         $created = date ("d-m-Y@H:i", $article['created']);
         $title = (utf8_strlen($article['title']) > MOD_TITLE_MAX_LEN ? truncate_string ($article['title'], MOD_TITLE_MAX_LEN) . '...' : $article['title']);
         $username = (utf8_strlen($userinfo['username']) > MOD_USERNAME_MAX_LEN ? truncate_string ($userinfo['username'], MOD_USERNAME_MAX_LEN) . '...' : $userinfo['username']);
                  
         $pagecontent .= "<tr>
                          <td bgcolor=\"$color\" align=\"left\"><a href=\"/submit.php?mode=preview&amp;id={$article['article_id']}\" title=\"Преглед/Редакция\">{$article['article_id']}</a></td>
                          <td bgcolor=\"$color\" align=\"left\"><a href=\"/submit.php?mode=preview&amp;id={$article['article_id']}\" title=\"{$article['title']}\">$title</a></td>
                          <td bgcolor=\"$color\" align=\"left\"><a href=\"mailto:{$userinfo['user_email']}\" title=\"{$userinfo['username']}\">$username</a></td>
                          <td bgcolor=\"$color\" align=\"left\">{$article['category']}</td>
                          <td bgcolor=\"$color\" align=\"left\">{$created}</td>
                          <td bgcolor=\"$color\" align=\"left\">" . (($article['approved'] == 'Y') ? 'Да' : 'Не') . "</td>
                          <td bgcolor=\"$color\" align=\"right\"><input type=\"checkbox\" name=\"id{$article['article_id']}\" value=\"id{$article['article_id']}\" /></td></tr>";
      }
      
      $pagecontent .= '</table>
                       <br />
                       <div align="right">
                       <input type="submit" name="delete" value="Изтрий избраните" />&nbsp;&nbsp;
                       <input type="submit" name="approve" value="Одобри избраните" />'
                   .   ($showall ? '<input type="hidden" name="show" value="all" />' : '')
                   .   '</div>
                       </form>';
      
      $pagecontent .= $pagelinks;
   }
?>
