<?php
   $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
   require ("$DOCUMENT_ROOT/../site/begin_user_session.php");
   require ("$DOCUMENT_ROOT/../site/pageclass.php");
   require ("$DOCUMENT_ROOT/../site/display_functions.php");
   require ("$DOCUMENT_ROOT/../site/www_db_config.php");
   require ("$DOCUMENT_ROOT/../site/submit_functions.php");
   
   $processsearch = new PageClass ();
   $processsearch->SetTitle ('unix-heaven.org - Резултати от търсенето');
   $pagecontent = '<h2>Резултати от търсенето</h2>';
   
   $keywords = $_POST['keywords'];
   $category = $_POST['category'];
   
   /* validate the data */
   if (!isset($_POST['keywords'], $_POST['category']) || 
                     empty($keywords) || empty($category)) {
      
      $pagecontent .= '<p class="error">Не сте попълнили всички данни</p>
                      <p>Моля върнете се на предишната страница, за да попълните
                      липсващите данни</p>';
                      
      $processsearch->SetContent ($pagecontent);
      $processsearch->Display ();
      exit (1);
   }
   
   $keywords = addslashes ($keywords);
   $category = addslashes ($category);
            
   /* init the database */   
   Init_www_db ($articles_db);   
   
   $search_query = "SELECT keywords.article_id
                    FROM `keywords`, `articles`
                    WHERE keywords.keywords LIKE '%{$keywords}%'
                    AND `approved` = 'Y'
                    AND keywords.article_id = articles.article_id"
                 .  (($category != 'all')
                 ?  " AND `category` = '{$category}'"
                 :  '');
                 
   $search_result = mysql_query ($search_query, $articles_db);

   $pagecontent .= '<p>Търсенето върна ' . mysql_num_rows($search_result) . ' резултат(и)</p>';

   $count = 0;
   while ($row = mysql_fetch_array ($search_result))
      $search_array[$count++] = $row['article_id'];
   
   mysql_free_result ($search_result);
   mysql_close ($articles_db);
   
   $i = 0;
   while ($search_array[$i++]) {
      GetArticleDataById ($article, $userinfo, $search_array[$i - 1], (($category == 'all' ? FALSE : $category)), TRUE);
      $pagecontent .= 
         "<div class=\"item\">
         <a href=\"/docs.php?category={$article['category']}&id={$article['article_id']}\">
         <img src=\"/images/arrow.gif\" border=\"0\" />&nbsp;"
         . htmlspecialchars($article['title']) . "</a>
         от <a href=\"mailto:{$userinfo['user_email']}\">{$userinfo['username']}</a>\n</div>\n";
   }
   
   $processsearch->SetContent ($pagecontent);
   $processsearch->Display ();
?>
