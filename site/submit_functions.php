<?php
   /* Filename       :  submit_functions.php
    * Description    :  defines functions for submitting articles
    * Version        :  1.1
    * Last modified  :  2008/11/12 20:17:37
    * 
    * Functions defined in the file
    * -----------------------------
    * ArrangeArticleContent ()
    * GetFormData ()
    * GetArticleDataById ()
    * CreateArticleForm ()
    * PreviewArticle ()
    * PreviewEditArticle ()
    * SubmitArticle ()
    * isValidData ()
    * ShowUsersList ()
    * ShowResultByPages ()
    * -----------------------------
    */
   
   if (!defined('__submit_functions_php__'))
      define ('__submit_functions_php__', TRUE);
   
   /* arranges the content of an article for preview */
   function ArrangeArticleContent ($article, $userinfo, &$pagecontent)
   {
	   $topic_url = 'http://www.unix-heaven.org/forums/viewtopic.php?f=38&t=' . $article['topic_id'];
	   
	   $pagecontent .= "
            <h2>{$article['title']}</h2>
            <p class=\"author\">
            Автор: <a href=\"mailto:{$userinfo['user_email']}\">{$userinfo['username']}</a><br />
            Създадена на: " . date("d-m-Y@H:i", $article['created']) . "<br />
            Публикувана на: " . date("d-m-Y@H:i", $article['published']) . "<br />
            Последно редактирана на: " . date("d-m-Y@H:i", $article['modified']) . "</p>
            <br /><a class='commentslink' target='_blank' href='{$topic_url}'>Коментари и Дискусия</a><br />
			{$article['content']}";
   }
   
   /* retrieves the data sent by the form */
   function GetFormData (&$article)
   {
      $article['user_id'] = trim(stripslashes($_POST['article_author']));
      $article['title'] = trim(stripslashes($_POST['title']));
      $article['category'] = trim(stripslashes($_POST['category']));
      $article['keywords'] = trim(stripslashes($_POST['keywords']));
      $article['content'] = trim(stripslashes($_POST['content']));
      
      $article['keywords'] = mb_ereg_replace ('[^а-яА-Яa-zA-Z0-9\-_\. ]', '', $article['keywords']);
      $article['keywords'] = ereg_replace ('([[:blank:]])+', ' ', $article['keywords']);
      
      return isValidData ($article);
   }
   
   /* retrieves the data about an article from the database */
   function GetArticleDataById (&$article, &$userinfo, $id, $cat = FALSE, $approved = FALSE)
   {
      if (!defined('__www_db_config_php__')) {
         $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
         require ("$DOCUMENT_ROOT/../site/www_db_config.php");
      }
      
      /* init the databases */
      Init_www_db ($articles_db);
      Init_phpbb3_db ($phpbb3_db);
      
      $article_query = "SELECT * FROM `articles`, `keywords`
                        WHERE articles.article_id = {$id}
                        AND keywords.article_id = {$id}"
                     .  ($cat ? " AND `category` = '{$cat}'" : '')
                     .  ($approved ? " AND `approved` = 'Y'" : '');
      
      $article_result = mysql_query ($article_query, $articles_db);
            
      /* if the article does not exists */
      if (!mysql_num_rows($article_result))
         return FALSE;
         
      $article = mysql_fetch_array ($article_result);
      
      $user_query = "SELECT `username`, `user_email`
                     FROM `phpbb_users`
                     WHERE `user_id` = {$article['user_id']}";
      
      $user_result = mysql_query ($user_query, $phpbb3_db);
      
      $userinfo = mysql_fetch_array ($user_result);
      
      /* strip slashes */
      $article['title'] = stripslashes ($article['title']);
      $article['category'] = stripslashes ($article['category']);
      $article['keywords'] = stripslashes ($article['keywords']);
      $article['content'] = stripslashes ($article['content']);
      $article['approved'] = stripslashes ($article['approved']);
      
      /* free the result and close the databases */
      mysql_free_result ($article_result);
      mysql_free_result ($user_result);
      mysql_close ($articles_db);
      mysql_close ($phpbb3_db);
      
      return TRUE;
   }
   
   /* validates the data from the form */
   function isValidData ($article)
   {
      return (!isset($_POST['title'], $_POST['category'], $_POST['keywords'], 
              $_POST['content']) || empty($article['title']) || 
              empty($article['category']) || empty($article['keywords']) ||
              empty($article['content'])) ? FALSE : TRUE;
   }  

   /* previews the article */
   function PreviewArticle (&$pagecontent, $user)
   {
      $pagecontent .= '<h2>Преглед на статия</h2>';
      
      /* get and validate the data */
      if (GetFormData ($article)) {
         
         /* check if the article is being edited */
         if (isset($_POST['edit_id'])) {
            GetArticleDataById ($old_article, $old_userinfo, $_POST['edit_id']);
            $article['created'] = $old_article['created'];
            $article['modified'] = $article['published'] = time ();
            $userinfo = $old_userinfo;
         }
         else {
            $article['created'] = $article['modified'] = $article['published'] = time ();
            $userinfo['username'] = $user->data['username'];
            $userinfo['user_email'] = $user->data['user_email'];
         }
             
         $pagecontent .= '<hr />';
         ArrangeArticleContent ($article, $userinfo, $pagecontent);
      }
      /* if the data is not valid */
      else 
         $pagecontent .= '<p class="notice">Не сте въвели всички полета.
                         Моля въведете необходимата информация първо.</p>';
   }
   
   /* previews/edits articles */
   function PreviewEditArticle (&$pagecontent, $id)
   {
      /* $user is global object */
      global $user;
      
      $pagecontent .= '<h2>Преглед/Редакция на статия</h2>';
      
      /* get the data from the database */
      $result = GetArticleDataByID ($article, $userinfo, $id);
      if (!$result)
         $pagecontent .= '<p class="error">Статията, която търсите не съществува</p>';
      else {
         $pagecontent .=
                        ((($article['approved'] == 'N') || ($article['approved'] == 'Y'
                        && isAdministrator($user)))
                      ? "<form action=\"/submit.php\" method=\"post\">
                         <input type=\"hidden\" name=\"edit_id\" value=\"{$id}\" />
                         <input type=\"hidden\" name=\"edit_mode\" value=\"1\" />
                         <input type=\"submit\" name=\"submit\" value=\"Редактирай\" />
                         </form>\n"
                      : '');
         
         $pagecontent .= '<hr />';                      
         ArrangeArticleContent ($article, $userinfo, $pagecontent);
      }
   }
   
   /* submits the article */
   function SubmitArticle (&$pagecontent, $user)
   {     
      $pagecontent = '<h2>Състояние на изпратената статия</h2>';
      
      /* get and validate the data */
      if (GetFormData ($article)) {
         $article['title'] = addslashes ($article['title']);
         $article['category'] = addslashes ($article['category']);
         $article['keywords'] = addslashes ($article['keywords']);
         $article['content'] = addslashes ($article['content']);
        
         if (!defined('__www_db_config_php__')) {
            $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
            require ("$DOCUMENT_ROOT/../site/www_db_config.php");
         }
            
         /* check user permissions */        
         $approved = ((isAdministrator($user) || isModerator($user)) ? 'Y' : 'N');
         $approved_by = ((isAdministrator($user) || isModerator($user)) ? $user->data['user_id'] : 0);        
         
         /* check if the article was edited, if so - update the record */
         if (isset($_POST['edit_id'])) {
            GetArticleDataById ($old_article, $old_userinfo, $_POST['edit_id']);
            
            /* init the database */
            Init_www_db ($articles_db);
			Init_phpbb3_db ($phpbb3_db);
            
            $modified = $published = time ();
            $author = (isset($_POST['article_author'])) ? $_POST['article_author'] : $old_article['user_id'];
            $update_query = "UPDATE `articles`
                             SET `title` = '{$article['title']}',
                             `category` = '{$article['category']}',
                             `content` = '{$article['content']}',
                             `approved` = '{$approved}',
                             `modified` = {$modified},
                             `published` = {$published},
                             `approved_by` = {$approved_by},
                             `user_id` = {$author}
                             WHERE `article_id` = {$_POST['edit_id']}";
            
            mysql_query ("UPDATE `keywords`
                          SET `keywords` = '{$article['keywords']}'
                          WHERE `article_id` = {$_POST['edit_id']}", $articles_db);
            
			mysql_query ("UPDATE `phpbb_topics` SET
						  `topic_approved` = " . ($approved == 'Y' ? 1 : 0) .
						  " WHERE `topic_id` = {$old_article['topic_id']}", $phpbb3_db);
			
            mysql_query ($update_query, $articles_db);
            mysql_close ($articles_db);
			mysql_close ($phpbb3_db);
         }
         
         /* else - the article has been just created */
         else {
            /* init the database */
            Init_www_db ($articles_db);
            
            $created = $modified = $published = time ();
			
            /* insert the article */
            $article_query = "INSERT INTO `articles` VALUES
                             (NULL, 0, '{$article['title']}', '{$article['category']}',
                             '{$article['content']}', '{$approved}',
                             {$created}, {$modified}, {$published}, 
                             {$user->data['user_id']}, {$approved_by})";
            
            mysql_query ($article_query, $articles_db);
                    
            /* find the currently inserted article_id */
            $id_query = "SELECT `article_id`
                         FROM `articles`
                         WHERE `user_id` = '{$user->data['user_id']}'
                         AND `created` = '{$created}'"; 
            
            $id_result = mysql_query ($id_query, $articles_db);
            $article_id = mysql_fetch_array ($id_result);
            
            $keywords_query = "INSERT INTO `keywords` VALUES
                              ({$article_id['article_id']}, '{$article['keywords']}')";
            
            mysql_query ($keywords_query, $articles_db);
            
            /* make a topic in the forums database, so the users can post comments */
            /* some of the code is taken from the phpbb.com site
             * http://www.phpbb.com/mods/documentation/phpbb-documentation/basic_api/index.php#example-api-generate-text-insert-post
             */
            
			require ("$DOCUMENT_ROOT/forums/includes/functions_posting.php");
			
            /* first we create a link to the article itself */
            $article_url = 'http://www.unix-heaven.org/' . ($article['category'] == 'news' ? "news.php?id={$article_id['article_id']}" : "docs.php?category={$article['category']}&id={$article_id['article_id']}");
			
            /* note that multibyte support is enabled here */
            $my_subject = utf8_normalize_nfc($article['title']);
            $my_text = utf8_normalize_nfc("[url={$article_url}]{$article_url}[/url]");

            /* variables to hold the parameters for submit_post */
            $poll = $uid = $bitfield = $options = ''; 

            generate_text_for_storage($my_subject, $uid, $bitfield, $options, false, false, false);
            generate_text_for_storage($my_text, $uid, $bitfield, $options, true, true, true);

            $data = array( 
               'forum_id'        	=> 38,
               'icon_id'         	=> false,

               'enable_bbcode'   	=> true,
               'enable_smilies'  	=> true,
               'enable_urls'     	=> true,
               'enable_sig'      	=> true,

               'message'            => $my_text,
               'message_md5'        => md5($my_text),
                        
               'bbcode_bitfield'    => $bitfield,
               'bbcode_uid'         => $uid,

               'post_edit_locked'	=> 0,
               'topic_title'     	=> $my_subject,
               'notify_set'      	=> false,
               'notify'       		=> false,
               'post_time'       	=> 0,
               'forum_name'      	=> '',
               'enable_indexing' 	=> true,
            );

			/* submit the post and save the returned url */
            $topic_url = submit_post('post', $my_subject, '', POST_NORMAL, $poll, $data);
			
			/* get the id of the topic for commenting */
			ereg ('t=([0-9]+)$', $topic_url, $topic_id);
			
			/* check if the post needs to be approved */
			if ($approved == 'N') {
				/* init the database */
				Init_phpbb3_db ($phpbb3_db);
				
				$update_query = "UPDATE `phpbb_topics` SET
								 `topic_approved` = 0
								 WHERE `topic_id` = {$topic_id[1]}";
				
				mysql_query ($update_query, $phpbb3_db);
				mysql_close ($phpbb3_db);
			}
			
			/* update the topic_id column in the articles database */
			$update_query = "UPDATE `articles` SET
							 `topic_id` = {$topic_id[1]}
							 WHERE `article_id` = {$article_id['article_id']}";
			
			mysql_query ($update_query, $articles_db);		
			mysql_close ($articles_db);
         }
                          
         header ("Location: /thankyou.php");
      }
      
      /* the data has not been validated */
      else 
         $pagecontent .= '<p class="notice">Не сте въвели всички полета.
                         Моля въведете необходимата информация първо.</p>';
   }
   
   /* shows a list of registered users */
   function ShowUsersList ($firstauthor)
   {
      if (!defined('__www_db_config_php__')) {
            $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
            require ("$DOCUMENT_ROOT/../site/www_db_config.php");
      }
      
      Init_phpbb3_db ($phpbb3_db);
      
      $users_query = "SELECT `username`, `user_id`
                      FROM `phpbb_users`
                      WHERE `group_id` = 2 OR
                      `group_id` = 4 OR
                      `group_id` = 5";
      
      $users_result = mysql_query ($users_query, $phpbb3_db);
      
      $list = '<div class="section">Промени автора на статията</div>
               <select name="article_author">';
      while ($row = mysql_fetch_array ($users_result))
         $list .= "<option name=\"{$row['username']}\" value=\"{$row['user_id']}\" "
                   . (($firstauthor == $row['user_id']) ? 'selected="selected"' : '') . ">{$row['username']}</option>\n";
         
      $list .= '</select><br /><br />';
      
      mysql_free_result ($users_result);
      mysql_close ($phpbb3_db);
      
      return $list;
   }

   /* creates a form for submitting articles */
   function CreateArticleForm (&$pagecontent, $article)
   {
      global $user;
      
      $pagecontent .= '
         <p class="notice">Преди да изпратите Вашата статия, моля прочетете <a href="/docs.php?category=howtos&id=1">това</a>.</p>
         <div align="center">
         <form action="/submit.php" method="post">
         <table class="nav">
         <tr>
         <td>
            <div class="section">Категория на статията</div>
            <select name="category">
            <option value="news" ' . (((isset($_POST['edit_mode']) || isset($_POST['preview'])) && !strcmp($article['category'], 'news')) ? 'selected="selected"' : '') . '>Новини</option>
            <option value="howtos" ' . (((isset($_POST['edit_mode']) || isset($_POST['preview'])) && !strcmp($article['category'], 'howtos')) ? 'selected="selected"' : '') . '>Ръководства</option>
            <option value="translations" ' . (((isset($_POST['edit_mode']) || isset($_POST['preview'])) && !strcmp($article['category'], 'translations')) ? 'selected="selected"' : '') . '>Преводи</option>
            <option value="programs" ' . (((isset($_POST['edit_mode']) || isset($_POST['preview'])) && !strcmp($article['category'], 'programs')) ? 'selected="selected"' : '') . '>Програми</option>
            <option value="programming" ' . (((isset($_POST['edit_mode']) || isset($_POST['preview'])) && !strcmp($article['category'], 'programming')) ? 'selected="selected"' : '') . '>Програмиране</option>
            </select>
            <br /><br />
    
            <div class="section">Заглавие на статията:</div>
            <input type="text" name="title" size="80" maxlength="80" value="' . ((isset($_POST['edit_mode']) || isset($_POST['preview'])) ? htmlspecialchars($article['title']) : '') . '" />
            <br /><br />
            
            <div class="section">Ключови думи</div>
            <input type="text" name="keywords" size="80" maxlength="80" value="' . ((isset($_POST['edit_mode']) || isset($_POST['preview'])) ? $article['keywords'] : '') . '" />
            <br /><br />
            
            ' . ((isset($_POST['edit_id']) && (isAdministrator($user) || isModerator($user))) ? 
                  ShowUsersList ($article['user_id']) : '') . '
            
            <div class="section">Съдържание на статията</div>
            <script type="text/javascript">edToolbar("myTextArea");</script>
            <textarea rows="25" cols="65" id="myTextArea" name="content">' . ((isset($_POST['edit_mode']) || isset($_POST['preview'])) ? $article['content'] : '' ) . '</textarea>
            <br /> <br />
         </td>
         </tr>
         <tr>
         <td align="right">
            ' . ((isset($_POST['edit_id'])) ? 
                '<input type="hidden" name="edit_id" value="' . $_POST['edit_id'] . '" />' : '') .
            '<input type="submit" name="preview" value="Преглед" />&nbsp;&nbsp;
            <input type="submit" name="add" value="Изпрати" />
         </td>
         </tr>
         </table>
         </form>
         </div>';
   }
   
   /* displays the result in different pages */
   function ShowResultByPages ($db, $query, &$offset, $results_per_page = 10, $show_approved = FALSE, &$total)
   {      
      /* get the current page - defaults to first page */
      $curr_page = (isset($_GET['page']) && ereg("^[0-9]+$", $_GET['page']) && $_GET['page'] > 0 ? $_GET['page'] : 1);
      
      $_SERVER['REQUEST_URI'] .= (isset($_GET['page']) ? '' : '&page=1');
      
      $offset = ($curr_page - 1) * $results_per_page;
      
      /* find the total results */                     
      $total_result = mysql_query ($query, $db);     
      $total = mysql_fetch_array($total_result);
      mysql_free_result ($total_result);
      
      /* get the last page number */
      $last_page = ceil((float)$total['total_results'] / (float)$results_per_page);
           
      /* set the previous and next pages */
      $prev_page = $curr_page - 1;
      $next_page = $curr_page + 1;
      
      /* make the page links */
      $pagelinks    = ($curr_page > 1 ? '<p><a href="' . ereg_replace ('page=[0-9]+', "page={$prev_page}", $_SERVER['REQUEST_URI']) . '">&lt;&lt; Предишна</a>&nbsp;&nbsp;&nbsp;' : '<p>&lt;&lt; Предишна&nbsp;&nbsp;&nbsp;');
      $pagelinks   .= ($curr_page < $last_page ? '<a href="' . ereg_replace('page=[0-9]+', "page={$next_page}", $_SERVER['REQUEST_URI']) . '">Следваща &gt;&gt;</a><br /><br />' : 'Следваща &gt;&gt;<br /><br />');
      $pagelinks   .= ($curr_page > 1 ? '<a href="' . ereg_replace ('page=[0-9]+', "page=1", $_SERVER['REQUEST_URI']) . '">[Първа]</a>&nbsp;&nbsp;' : '[Първа]&nbsp;&nbsp;');
      $pagelinks   .= "[Страница <b>$curr_page</b> от <b>$last_page</b>]&nbsp;&nbsp;";
      $pagelinks   .= ($curr_page < $last_page ? '<a href="' . ereg_replace ('page=[0-9]+', "page={$last_page}", $_SERVER['REQUEST_URI']) . '">[Последна]</a></p>' : '[Последна]</p>');
      
      return $pagelinks;
   }  
?>
