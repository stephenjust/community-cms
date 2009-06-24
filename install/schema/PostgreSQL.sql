create sequence "<!-- $DB_PREFIX$ -->acl_record_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->acl_keys_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->blocks_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->calendar_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->calendar_categories_cat_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->files_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->logs_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->news_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->newsletters_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->pages_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->pagetypes_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->page_messages_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->permissions_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->poll_questions_question_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->poll_answers_answer_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->poll_responses_response_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->templates_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->messages_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->user_groups_id_seq";;
create sequence "<!-- $DB_PREFIX$ -->users_id_seq";;

CREATE TABLE "<!-- $DB_PREFIX$ -->acl" (
	"acl_record_id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->acl_record_id_seq') PRIMARY KEY,
	"acl_id" integer NOT NULL,
	"user" integer NOT NULL,
	"is_group" integer NOT NULL default 0,
	"value" integer NOT NULL default 0
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->acl_keys" (
	"acl_id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->acl_keys_id_seq') PRIMARY KEY,
	"acl_name" text NOT NULL,
	"acl_longname" text NOT NULL,
	"acl_description" text NOT NULL,
	"acl_value_default" integer NOT NULL default 0
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->blocks" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->blocks_id_seq') PRIMARY KEY ,
	"type" text NOT NULL,
	"attributes" text NOT NULL
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->calendar" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->calendar_id_seq'),
	"category" integer NOT NULL,
	"starttime" timestamp NOT NULL,
	"endtime" timestamp NOT NULL,
	"year" integer NOT NULL,
	"month" integer NOT NULL,
	"day" integer NOT NULL,
	header text NOT NULL,
	description text,
	location text,
	author text,
	image text default NULL,
	hidden integer NOT NULL,
	PRIMARY KEY ("id")
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->calendar_categories" (
	cat_id integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->calendar_categories_cat_id_seq'),
	label text NOT NULL,
	colour text NOT NULL,
	description text NOT NULL,
	PRIMARY KEY ("cat_id")
);;


CREATE TABLE "<!-- $DB_PREFIX$ -->config" (
	db_version decimal(6,2) NOT NULL,
	"name" text NOT NULL,
	url text NOT NULL,
	admin_email text NULL,
	comment text NOT NULL,
	template integer NOT NULL,
	footer text NOT NULL,
	active integer NOT NULL,
	home integer NOT NULL default '1'
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->files" (
  id integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->files_id_seq'),
  type integer NOT NULL,
  label text NOT NULL,
  path text NOT NULL,
  PRIMARY KEY  (id)
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->logs" (
  "log_id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->logs_id_seq'),
  "date" timestamp NULL default CURRENT_TIMESTAMP,
  "user_id" integer NOT NULL,
  "action" text NOT NULL,
  "ip_addr" integer NOT NULL,
  PRIMARY KEY  ("log_id")
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->news" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->news_id_seq'),
	"page" integer default NULL,
	"name" text,
	"description" text,
	"author" text,
	"date" timestamp NOT NULL default CURRENT_TIMESTAMP,
	"date_edited" timestamp NULL default NULL,
	"image" text,
	"showdate" integer NOT NULL default '1',
	PRIMARY KEY ("id")
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->news_settings" (
	"num_articles" integer NOT NULL ,
    "default_date_setting" integer NOT NULL ,
    "show_author" integer NOT NULL ,
    "show_edit_time" integer NOT NULL
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->newsletters" (
  id integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->newsletters_id_seq'),
  page integer NOT NULL,
  year integer NOT NULL default '2008',
  month integer NOT NULL default '1',
  label text NOT NULL,
  path text NOT NULL,
  hidden integer NOT NULL default '0',
  PRIMARY KEY  (id)
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->pages" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->pages_id_seq'),
	"text_id" text NOT NULL,
	"title" text NOT NULL,
	"show_title" integer NOT NULL default '1',
	"type" integer NOT NULL,
	"menu" integer NOT NULL,
	"list" integer NOT NULL default '0',
	"blocks_left" text NULL,
	"blocks_right" text NULL,
	"hidden" integer NOT NULL default '0',
	PRIMARY KEY  ("id")
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->page_messages" (
	message_id integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->page_messages_id_seq') PRIMARY KEY,
	page_id integer NOT NULL,
	start_date DATE NOT NULL,
	end_date DATE NOT NULL,
	"end" integer NOT NULL DEFAULT '1',
	text text NOT NULL,
	"order" integer NOT NULL
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->pagetypes" (
	id integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->pagetypes_id_seq'),
	name text NOT NULL,
	description text NOT NULL,
	author text NOT NULL,
	filename text NOT NULL,
	PRIMARY KEY  (id)
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->permissions" (
	id integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->permissions_id_seq') PRIMARY KEY ,
	"user" integer NOT NULL ,
	files integer NOT NULL DEFAULT '0'
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->poll_questions" (
	"question_id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->poll_questions_question_id_seq'),
	"question" text NOT NULL,
	"short_name" text NOT NULL,
	"type" integer NOT NULL default '1',
	"active" integer NOT NULL default '1',
	PRIMARY KEY  ("question_id")
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->poll_answers" (
  answer_id integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->poll_answers_answer_id_seq'),
  question_id integer NOT NULL,
  answer text NOT NULL,
  answer_order integer NOT NULL,
  PRIMARY KEY  (answer_id)
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->poll_responses" (
  response_id integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->poll_responses_response_id_seq'),
  question_id integer NOT NULL,
  answer_id integer NOT NULL,
  value text,
  ip_addr integer NOT NULL,
  PRIMARY KEY  (response_id)
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->sessions" (
	"uid" integer NOT NULL,
	"sid" text NOT NULL,
	"timestamp" integer NOT NULL default '0',
	"id_addr" integer NOT NULL,
	PRIMARY KEY ("sid")
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->templates" (
	id integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->templates_id_seq'),
	path text NOT NULL,
	name text NOT NULL,
	description text NOT NULL,
	author text NOT NULL,
	PRIMARY KEY  (id)
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->messages" (
	id integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->messages_id_seq'),
	recipient integer NOT NULL DEFAULT '1',
	message text NOT NULL,
	PRIMARY KEY (id)
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->user_groups" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->user_groups_id_seq'),
	"name" text NOT NULL,
	"label_format" text NOT NULL,
	PRIMARY KEY ("id")
);;

CREATE TABLE "<!-- $DB_PREFIX$ -->users" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->users_id_seq'),
	"type" integer NOT NULL default '1',
	"username" text NOT NULL,
	"password" text NOT NULL,
	"realname" text NOT NULL,
	"title" text NULL,
	"groups" text NULL,
	"phone" text NOT NULL,
	"email" text NOT NULL,
	"address" text NOT NULL,
	"phone_hide" integer NOT NULL default '1',
	"email_hide" integer NOT NULL default '1',
	"address_hide" integer NOT NULL default '1',
	"hide" integer NOT NULL default '0',
	"message" integer NOT NULL default '0',
	"lastlogin" integer NOT NULL default '0',
	PRIMARY KEY ("id")
);;

select setval('<!-- $DB_PREFIX$ -->acl_record_id_seq', (select max(acl_record_id) from "<!-- $DB_PREFIX$ -->acl"));;
select setval('<!-- $DB_PREFIX$ -->acl_keys_id_seq', (select max(acl_id) from "<!-- $DB_PREFIX$ -->acl_keys"));;
select setval('<!-- $DB_PREFIX$ -->blocks_id_seq', (select max(id) from "<!-- $DB_PREFIX$ -->blocks"));;
select setval('<!-- $DB_PREFIX$ -->calendar_id_seq', (select max(id) from "<!-- $DB_PREFIX$ -->calendar"));;
select setval('<!-- $DB_PREFIX$ -->calendar_categories_cat_id_seq', (select max(cat_id) from "<!-- $DB_PREFIX$ -->calendar_categories"));;
select setval('<!-- $DB_PREFIX$ -->files_id_seq', (select max(id) from "<!-- $DB_PREFIX$ -->files"));;
select setval('<!-- $DB_PREFIX$ -->logs_id_seq', (select max(log_id) from "<!-- $DB_PREFIX$ -->logs"));;
select setval('<!-- $DB_PREFIX$ -->news_id_seq', (select max(id) from "<!-- $DB_PREFIX$ -->news"));;
select setval('<!-- $DB_PREFIX$ -->newsletters_id_seq', (select max(id) from "<!-- $DB_PREFIX$ -->newsletters"));;
select setval('<!-- $DB_PREFIX$ -->pages_id_seq', (select max(id) from "<!-- $DB_PREFIX$ -->pages"));;
select setval('<!-- $DB_PREFIX$ -->pagetypes_id_seq', (select max(id) from "<!-- $DB_PREFIX$ -->pagetypes"));;
select setval('<!-- $DB_PREFIX$ -->page_messages_id_seq', (select max(message_id) from "<!-- $DB_PREFIX$ -->page_messages"));;
select setval('<!-- $DB_PREFIX$ -->permissions_id_seq', (select max(id) from "<!-- $DB_PREFIX$ -->permissions"));;
select setval('<!-- $DB_PREFIX$ -->poll_questions_question_id_seq', (select max(question_id) from "<!-- $DB_PREFIX$ -->poll_questions"));;
select setval('<!-- $DB_PREFIX$ -->poll_answers_answer_id_seq', (select max(answer_id) from "<!-- $DB_PREFIX$ -->poll_answers"));;
select setval('<!-- $DB_PREFIX$ -->poll_responses_response_id_seq', (select max(response_id) from "<!-- $DB_PREFIX$ -->poll_responses"));;
select setval('<!-- $DB_PREFIX$ -->templates_id_seq', (select max(id) from "<!-- $DB_PREFIX$ -->templates"));;
select setval('<!-- $DB_PREFIX$ -->messages_id_seq', (select max(id) from "<!-- $DB_PREFIX$ -->messages"));;
select setval('<!-- $DB_PREFIX$ -->user_groups_id_seq', (select max(id) from "<!-- $DB_PREFIX$ -->user_groups"));;
select setval('<!-- $DB_PREFIX$ -->users_id_seq', (select max(id) from "<!-- $DB_PREFIX$ -->users"));;

INSERT INTO "<!-- $DB_PREFIX$ -->acl" ("acl_id", "user", "is_group", "value") VALUES
(1, 1, 0, 1);;

INSERT INTO "<!-- $DB_PREFIX$ -->acl_keys" ("acl_name","acl_longname","acl_description","acl_value_default") VALUES
('all','All Permissions','Grant this permission to allow all actions within the CMS',0);;

INSERT INTO "<!-- $DB_PREFIX$ -->calendar_categories" ("cat_id", "label", "colour", "description") VALUES
(0, 'Default Category', 'red', ''),
(1, 'Other', 'yellow', '');;

INSERT INTO "<!-- $DB_PREFIX$ -->config" (db_version,name, url, comment, template, footer, active) VALUES
('0.02','<!-- $SITE_NAME$ -->', 'http://localhost/', 'Sourceforge.net', 1, '<a href="http://sourceforge.net"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=223968&amp;type=1" width="88" height="31" border="0" type="image/png" alt="SourceForge.net Logo" /></a><br />Powered by Community CMS', 1);;

INSERT INTO "<!-- $DB_PREFIX$ -->news" ("page", "name", "description", "author", "date", "image") VALUES
(1, 'Welcome to Community CMS ALPHA!', '<p>Welcome to Community CMS, the web content system aimed at non-profit organizations and communities. The CMS features a news bulletin board, a calendar, a system for displaying newsletters, and an administration system to make editing your content easy. Now you can edit content too! It works really well.</p>', 'Administrator', '2008-06-20 22:25:38', NULL);;

INSERT INTO "<!-- $DB_PREFIX$ -->news_settings"
    (num_articles ,default_date_setting ,show_author ,show_edit_time) VALUES
('10', '1', '1', '1');;

INSERT INTO "<!-- $DB_PREFIX$ -->pages" (text_id, title, type, menu, list) VALUES
('home', 'Home', 1, 1, 0),
('calendar', 'Calendar', 3, 1, 1),
('newsletters', 'Newsletters', 2, 1, 2);;

INSERT INTO "<!-- $DB_PREFIX$ -->pagetypes" (id, name, description, author, filename) VALUES
(1, 'News', 'A simple news posting system that acts as the main message centre for Community CMS', 'stephenjust', 'news.php'),
(2, 'Newsletter List', 'This pagetype creates a dynamic list of newsletters, sorted by timestamp. It is most useful for a monthly newsletter scenario.', 'stephenjust', 'newsletter.php'),
(3, 'Calendar', 'A complex timestamp management system supporting a full month view, week view, day view, and an event view. This pagetype by default displays the current month.', 'stephenjust', 'calendar.php'),
(4, 'Contacts', 'A page where all users whose information is set to be visible will be shown', 'stephenjust', 'contacts.php');;

INSERT INTO "<!-- $DB_PREFIX$ -->templates" ("id", "path", "name", "description", "author") VALUES
(1, 'templates/default/', 'Community CMS Default Template', 'Default template.', 'Stephen J');;

INSERT INTO "<!-- $DB_PREFIX$ -->user_groups"
("name","label_format") VALUES
('Administrator','font-weight: bold;; color: #009900;;');;

INSERT INTO "<!-- $DB_PREFIX$ -->users"
(id, type, username, password, groups, realname, phone, email, address, phone_hide, email_hide, address_hide, message) VALUES
(1, 1, '<!-- $ADMIN_USER$ -->', '<!-- $ADMIN_PWD$ -->', '1', 'Administrator', '555-555-5555', 'admin@example.com','Unknown',1,1,1,1),
(2, 0, 'user', '5f4dcc3b5aa765d61d8327deb882cf99', NULL, 'Default User', '555-555-5555', 'user@example.com','Unknown',1,1,1,0)
