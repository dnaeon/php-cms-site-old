<?php
   /* Filename       :  search.php
    * Description    :  search engine for the web site
    * Version        :  1.1
    * Last modified  :  2008/11/14 16:29:54
    * 
    * Functions defined in the file
    * -----------------------------
    * CreateSearchForm ()
    * ProcessSearch ()
    * -----------------------------
    */   

   $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
   require ("$DOCUMENT_ROOT/../site/begin_user_session.php");
   require ("$DOCUMENT_ROOT/../site/pageclass.php");
   require ("$DOCUMENT_ROOT/../site/display_functions.php");
   require ("$DOCUMENT_ROOT/../site/www_db_config.php");
   require ("$DOCUMENT_ROOT/../site/submit_functions.php");
   
   $search = new PageClass ();
   $search->SetTitle ("unix-heaven.org - Търсене");
     
   CreateSearchFrom ($pagecontent);
   ProcessSearch ($pagecontent);

   $search->SetContent ($pagecontent);
   $search->Display ();
   
   /* creates a form for searching into the database */
   function CreateSearchFrom (&$pagecontent)
   {
      $pagecontent .= '
      <h2>Подробно търсене</h2>
      <p>За търсене в сайта чрез <a href="http://www.google.com/">Google</a>
         използвайте полето в лявата навигационна лента.</p>
      <p>За търсене в базата данни на сайта използвайте предоставената по-долу
         форма.</p>
      <form action="search.php" method="get">
      <div align="center">
      <table class="nav">
      <tr>
         <td>
             <div class="section">Категория</div>
             <select name="category">
             <option value="all"' . (isset($_GET['category']) && !strcmp($_GET['category'], 'all') ? ' selected="selected"' : '') . '>Всички</option>
             <option value="news"' . (isset($_GET['category']) && !strcmp($_GET['category'], 'news') ? ' selected="selected"' : '') . '>Новини</option>
             <option value="howtos"' . (isset($_GET['category']) && !strcmp($_GET['category'], 'howtos') ? ' selected="selected"' : '') . '>Ръководства</option>
             <option value="translations"' . (isset($_GET['category']) && !strcmp($_GET['category'], 'translations') ? ' selected="selected"' : '') . '>Преводи</option>
             <option value="programs"' . (isset($_GET['category']) && !strcmp($_GET['category'], 'programs') ? ' selected="selected"' : '') . '>Програми</option>
             <option value="programming"' . (isset($_GET['category']) && !strcmp($_GET['category'], 'programming') ? ' selected="selected"' : '') . '>Програмиране</option>
             </select><br /><br />
             
             <div class="section">Ключови думи</div>
             <input type="text" name="keywords" size="80" maxlength="80" value="' . (isset($_GET['keywords']) ? $_GET['keywords'] : '') . '" /><br /><br />
         </td>
      </tr>
      <tr>
         <td align="right">
            <input type="submit" name="submit" value="Търси!" />
         </td>
      </tr>
      </table>
      </div>
      </form>';
   }
   
   /* processes the search query */
   function ProcessSearch (&$pagecontent)
   {  
      /* check for valid data */
      if (isset($_GET['keywords'], $_GET['category']) &&
          !empty($_GET['keywords']) && !empty($_GET['category'])) {
         $keywords = trim(addslashes($_GET['keywords']));
         $category = trim(addslashes($_GET['category']));
                  
         /* init the database */   
         Init_www_db ($articles_db);   
         
         $results_per_page = 20;
         
         /* find the total results - result search query*/
         $total_query = "SELECT COUNT(keywords.article_id)
                         AS `total_results`
                         FROM `keywords`, `articles`
                         WHERE keywords.keywords LIKE '%{$keywords}%'
                         AND `approved` = 'Y'
                         AND keywords.article_id = articles.article_id"
                      . (($category != 'all')
                      ?  " AND `category` = '{$category}'"
                      :  '');
         
         /* create the links for the different result pages */
         $pagelinks = ShowResultByPages ($articles_db, $total_query, $offset, $results_per_page, FALSE, $total);
         
         /* search for matching patterns */
         $search_query = "SELECT keywords.article_id
                          FROM `keywords`, `articles`
                          WHERE keywords.keywords LIKE '%{$keywords}%'
                          AND `approved` = 'Y'
                          AND keywords.article_id = articles.article_id"
                       .  (($category != 'all')
                       ?  " AND `category` = '{$category}'"
                       :  '')
                       .  " LIMIT {$offset}, {$results_per_page}";
                       
         $search_result = mysql_query ($search_query, $articles_db);
         $num_results = mysql_num_rows ($search_result);

         $pagecontent .= ($offset <= $total['total_results'] ? '<p>Търсенето върна ' . $total['total_results'] . ' резултат(и)</p>' : '<p class="error">Грешка: Страницата, която се опитвате да отворите не съществува.</p>');

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
               <a href=\"/docs.php?category={$article['category']}&amp;id={$article['article_id']}\">
               <img src=\"/images/arrow.gif\" border=\"0\" alt=\"\" />&nbsp;"
               . htmlspecialchars($article['title']) . "</a>
               от <a href=\"mailto:{$userinfo['user_email']}\">{$userinfo['username']}</a>\n</div>\n";
         }
                       
         $pagecontent .= ($total['total_results'] && $offset < $total['total_results']
                       ? '<hr /><p>Резултати <b>' . ($offset+1) . '</b> - <b>' . 
                         ($offset+$num_results) . '</b> от общо <b>' . 
                         $total['total_results'] . '</b></p>' . $pagelinks 
                       : '');
      }
   }
?>
