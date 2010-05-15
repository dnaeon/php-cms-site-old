#  Filename       :  www_db.sql
#  Description    :  MySQL database for the site articles
#  Version        :  1.1
#  Last modified  :  2008/11/10 14:05:17

DROP DATABASE IF EXISTS www_db;
CREATE DATABASE www_db DEFAULT CHARACTER SET utf8;

USE www_db;

DROP TABLE IF EXISTS articles;
CREATE TABLE articles (
   article_id  INT (8) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
   topic_id    MEDIUMINT (8) NOT NULL DEFAULT 0,    # id of the article in the forums db
   title       VARCHAR (80) NOT NULL,
   category    ENUM ('news', 'howtos', 'translations', 'programs', 'programming') NOT NULL,
   content     TEXT NOT NULL,
   approved    ENUM ('N', 'Y') NOT NULL DEFAULT 'N',
   created     INT NOT NULL,
   modified    INT NOT NULL,
   published   INT NOT NULL,
   user_id     MEDIUMINT (8) NOT NULL,              # FOREIGN KEY - article author
   approved_by MEDIUMINT (8) NOT NULL               # FOREIGN KEY - approved by user
   
);

DROP TABLE IF EXISTS keywords;
CREATE TABLE keywords (
   article_id  INT (8) UNSIGNED NOT NULL,     # FOREIGN KEY - articles.article_id
   keywords    VARCHAR (80) NOT NULL
);

GRANT SELECT, INSERT, DELETE, UPDATE
ON www_db.*
TO 'www_user'@'localhost' IDENTIFIED BY 'mydb4wwws3cur3d';
