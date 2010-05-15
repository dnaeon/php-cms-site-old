<?php
   /* Filename       :  docs.php
    * Description    :  displays the latest articles
    * Version        :  1.1
    * Last modified  :  2008/11/04 19:29:58
    */   

   $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
   require ("$DOCUMENT_ROOT/../site/begin_user_session.php");
   require ("$DOCUMENT_ROOT/../site/pageclass.php");
   require ("$DOCUMENT_ROOT/../site/display_functions.php");
   require ("$DOCUMENT_ROOT/../site/www_db_config.php");
   require ("$DOCUMENT_ROOT/../site/submit_functions.php");
   
   $docs = new PageClass ();
   $docs->SetTitle ("unix-heaven.org - Статии");
   
   /* display article by id */
   if (isset($_GET['category']) && isset($_GET['id']) && ereg("^[0-9]+$", $_GET['id'])) {
      
      /* check for valid id */
      if (!($result = GetArticleDataById ($article, $userinfo, $_GET['id'], $_GET['category'], TRUE))) {
        $pagecontent .= '<h2>Статии</h2><p class="error">Статията, която търсите не може
                           да бъде открита</p>
                           <p>За да намерите търсения от Вас документ можете да използвате
                           <a href="/search.php">търсачката</a>.</p>';
         $docs->SetContent ($pagecontent);
         $docs->Display ();
         exit (1);
      }
      
      ArrangeArticleContent ($article, $userinfo, $pagecontent);
      $docs->SetTitle ("unix-heaven.org - Статии - " . htmlspecialchars($article['title']));
      $docs->SetContent ($pagecontent);
      $docs->Display ();
      exit (0);
   }
   
   /* display all articles by category */
   if (isset($_GET['category']) && isset($_GET['show']) && !strcmp($_GET['show'], 'all')) {
      $pagecontent = '<h2>Статии</h2>';
      GetArticlesByCategory ($pagecontent, $_GET['category'], 'Статии от категорията', FALSE);
      
      $docs->SetContent ($pagecontent);
      $docs->Display ();
      exit (0);
   }
         
   
   $pagecontent = 
      '<h2>Статии</h2>
      <p>Ако Вие имате написана статия/ръководство/превод и желаете да я споделите
      с други потребители - пишете ни - ние ще се погрижим Вашия принос да бъде
      на страниците на Проекта!</p>
      <p>Тук ще намерите разнообразна документация по следните (и други за напред) 
      категории:</p>
      <ul class="contentlist">
         <li><a href="/docs.php?category=howtos&amp;show=all">Ръководства</a></li>
         <li><a href="/docs.php?category=programming&amp;show=all">Програмиране</a></li>
         <li><a href="/docs.php?category=programs&amp;show=all">Програми</a></li>
         <li><a href="/docs.php?category=translations&amp;show=all">Превод на програми и документация</a></li>
      </ul>';
      
   GetArticlesByCategory ($pagecontent, 'howtos', 'Ръководства', 5);
   $pagecontent .= 
      '<p>Ръководства, които следват да бъдат добавени скоро към категорията:</p>
      <ul class="contentlist">
         <li>Инсталиране и конфифуриране на Apache + PHP + MySQL</li>
         <li>Инсталация, конфигуриране и експлоатация на Qmail</li>
      </ul>';
   
   GetArticlesByCategory ($pagecontent, 'programming', 'Програмиране', 5);
   GetArticlesByCategory ($pagecontent, 'programs', 'Програми', 5);
   GetArticlesByCategory ($pagecontent, 'translations', 'Превод на програми и документация', 5);
   $pagecontent .= 
      '<p>Преводи, които следват да бъдат добавени скоро към категорията:</p>
      <ul class="contentlist">
         <li>Различни глави (а защо не и всички :)) от FreeBSD Handbook</li>
         <li>OpenBSD PF User\'s Guide</li>
      </ul>';
   
   
   $docs->SetContent ($pagecontent);
   $docs->Display ();
   
   /* gets information about articles by category */
   function GetArticlesByCategory (&$pagecontent, $cat, $cat_name, $limit = FALSE)
   {
      /* $limit == FALSE when showing articles by category
       * $limit == TRUE when showing articles in the main docs.php page */
      
       /* init the database */
      Init_www_db ($articles_db);
      
      /* number of articles to show */
      $articles_per_page = 20;
      
      /* find the total number of articles in the category */
      $total_query = "SELECT COUNT(article_id)
                      AS `total_results`
                      FROM `articles`
                      WHERE `category` = '{$cat}'
                      AND `approved` = 'Y'
                      ORDER BY `created` DESC";
      $pagelinks = ShowResultByPages ($articles_db, $total_query, $offset, $articles_per_page, FALSE, $total);
      
      $articles_query = "SELECT `title`, `article_id`, `category`
                         FROM `articles`
                         WHERE `category` = '{$cat}'
                         AND `approved` = 'Y'
                         ORDER BY `created` DESC"
                      .  (($limit == TRUE)
                      ?  " LIMIT 0, {$limit}"
                      :  " LIMIT {$offset}, {$articles_per_page}");
                         
      $articles_result = mysql_query ($articles_query, $articles_db);
      $num_articles = mysql_num_rows ($articles_result);
      
      $pagecontent .= "<hr /><h2>$cat_name</h2>\n";
      
      $pagecontent .= (!mysql_num_rows($articles_result)
                    ? '<p>В тази категория все още не са добавени материали.</p>'
                    : '');      
           
      $pagecontent .= '<ul class="contentlist">';
      while ($row = mysql_fetch_array($articles_result)) 
         $pagecontent .= "<li><a href=\"/docs.php?category={$cat}&amp;id={$row['article_id']}\">
                              {$row['title']}</a></li>\n";
                              
      $pagecontent .= '</ul>';
      $pagecontent .= ($limit == FALSE && $total['total_results'] && ($offset < $total['total_results'])
                   ?  '<hr /><p>Статии <b>' . ($offset + 1) . '</b> - <b>' .
                      ($offset + $num_articles) . '</b> от общо <b>' .
                      $total['total_results'] . '</b></p>' . $pagelinks 
                   :  '');
      
      mysql_free_result ($articles_result);
      mysql_close ($articles_db);
   }
?>
