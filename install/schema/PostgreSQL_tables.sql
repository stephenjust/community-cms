-- ----------------------------------------------------------------------------
-- comcms_acl
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->acl_record_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->acl" (
	"acl_record_id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->acl_record_id_seq') PRIMARY KEY,
	"acl_id" integer NOT NULL,
	"group" integer NOT NULL,
	"value" integer NOT NULL default 0
);
SELECT setval('<!-- $DB_PREFIX$ -->acl_record_id_seq', (SELECT max("acl_record_id") FROM "<!-- $DB_PREFIX$ -->acl"));

-- ----------------------------------------------------------------------------
-- comcms_acl_keys
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->acl_keys_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->acl_keys" (
	"acl_id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->acl_keys_id_seq') PRIMARY KEY,
	"acl_name" text NOT NULL,
	"acl_longname" text NOT NULL,
	"acl_description" text NOT NULL,
	"acl_value_default" integer NOT NULL default 0
);
SELECT setval('<!-- $DB_PREFIX$ -->acl_keys_id_seq', (SELECT max("acl_id") FROM "<!-- $DB_PREFIX$ -->acl_keys"));

-- ----------------------------------------------------------------------------
-- comcms_blocks
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->blocks_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->blocks" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->blocks_id_seq') PRIMARY KEY ,
	"type" text NOT NULL,
	"attributes" text NOT NULL
);
SELECT setval('<!-- $DB_PREFIX$ -->blocks_id_seq', (SELECT max("id") FROM "<!-- $DB_PREFIX$ -->blocks"));

-- ----------------------------------------------------------------------------
-- comcms_calendar
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->calendar_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->calendar" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->calendar_id_seq'),
	"category" integer NOT NULL,
	"category_hide" integer NOT NULL default 0,
	"start" timestamp NOT NULL,
	"end" timestamp NOT NULL,
	"header" text NOT NULL,
	"description" text,
	"location" text,
	"location_hide" integer NOT NULL default 0,
	"author" text,
	"image" text default NULL,
	"hidden" integer NOT NULL,
	"imported" text,
	PRIMARY KEY ("id")
);
SELECT setval('<!-- $DB_PREFIX$ -->calendar_id_seq', (SELECT max("id") FROM "<!-- $DB_PREFIX$ -->calendar"));

-- ----------------------------------------------------------------------------
-- comcms_calendar_categories
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->calendar_categories_cat_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->calendar_categories" (
	"cat_id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->calendar_categories_cat_id_seq'),
	"label" text NOT NULL,
	"colour" text NOT NULL,
	"description" text NULL default NULL,
	PRIMARY KEY ("cat_id")
);
SELECT setval('<!-- $DB_PREFIX$ -->calendar_categories_cat_id_seq', (SELECT max("cat_id") FROM "<!-- $DB_PREFIX$ -->calendar_categories"));

-- ----------------------------------------------------------------------------
-- comcms_calendar_sources
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->calendar_sources_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->calendar_sources" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->calendar_sources_id_seq'),
	"desc" text NOT NULL,
	"url" text NOT NULL,
	PRIMARY KEY ("id")
);
SELECT setval('<!-- $DB_PREFIX$ -->calendar_sources_id_seq', (SELECT max("id") FROM "<!-- $DB_PREFIX$ -->calendar_sources"));

-- ----------------------------------------------------------------------------
-- comcms_config
-- ----------------------------------------------------------------------------
CREATE TABLE "<!-- $DB_PREFIX$ -->config" (
	"config_name" varchar(255) NOT NULL,
	"config_value" varchar(255) NOT NULL,
	PRIMARY KEY ("config_name")
);

-- ----------------------------------------------------------------------------
-- comcms_contacts
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->contacts_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->contacts" (
	"id" INT NOT NULL default nextval('<!-- $DB_PREFIX$ -->contacts_id_seq') ,
	"name" TEXT NOT NULL ,
	"phone" CHAR( 11 ) NULL default NULL ,
	"address" TEXT NOT NULL ,
	"email" TEXT NOT NULL ,
	"title" TEXT NOT NULL,
	PRIMARY KEY ("id")
);
SELECT setval('<!-- $DB_PREFIX$ -->contacts_id_seq', (SELECT max("id") FROM "<!-- $DB_PREFIX$ -->contacts"));

-- ----------------------------------------------------------------------------
-- comcms_content
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->content_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->content" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->content_id_seq'),
	"page_id" integer NOT NULL default 0,
	"ref_type" text NOT NULL,
	"ref_id" integer NOT NULL default 0,
	"order" integer NOT NULL default 0,
	PRIMARY KEY ("id")
);
SELECT setval('<!-- $DB_PREFIX$ -->content_id_seq', (SELECT max("id") FROM "<!-- $DB_PREFIX$ -->content"));

-- ----------------------------------------------------------------------------
-- comcms_dir_props
-- ----------------------------------------------------------------------------
CREATE TABLE "<!-- $DB_PREFIX$ -->dir_props" (
	"directory" text NOT NULL,
	"property" text NOT NULL,
	"value" integer UNSIGNED default 0
);

-- ----------------------------------------------------------------------------
-- comcms_files
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->files_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->files" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->files_id_seq'),
	"type" integer NOT NULL default 0,
	"label" text NOT NULL,
	"path" text NOT NULL,
	PRIMARY KEY ("id")
);
SELECT setval('<!-- $DB_PREFIX$ -->files_id_seq', (SELECT max("id") FROM "<!-- $DB_PREFIX$ -->files"));

-- ----------------------------------------------------------------------------
-- comcms_galleries
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->galleries_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->galleries" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->galleries_id_seq'),
	"title" text NOT NULL,
	"description" text NOT NULL,
	"image_dir" text NOT NULL,
	PRIMARY KEY ("id")
);
SELECT setval('<!-- $DB_PREFIX$ -->galleries_id_seq', (SELECT max("id") FROM "<!-- $DB_PREFIX$ -->galleries"));

-- ----------------------------------------------------------------------------
-- comcms_logs
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->logs_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->logs" (
	"log_id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->logs_id_seq'),
	"date" timestamp NULL default CURRENT_TIMESTAMP,
	"user_id" integer NOT NULL,
	"action" text NOT NULL,
	"ip_addr" integer NOT NULL,
	PRIMARY KEY  ("log_id")
);
SELECT setval('<!-- $DB_PREFIX$ -->logs_id_seq', (SELECT max("log_id") FROM "<!-- $DB_PREFIX$ -->logs"));

-- ----------------------------------------------------------------------------
-- comcms_news
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->news_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->news" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->news_id_seq'),
	"page" integer default NULL,
	"priority" integer NOT NULL default 0,
	"name" text,
	"description" text,
	"author" text,
	"date" timestamp NOT NULL default CURRENT_TIMESTAMP,
	"date_edited" timestamp NULL default NULL,
	"delete_date" timestamp NULL default NULL,
	"image" text,
	"showdate" integer NOT NULL default '1',
	"publish" integer NOT NULL default '1',
	PRIMARY KEY ("id")
);
SELECT setval('<!-- $DB_PREFIX$ -->news_id_seq', (SELECT max("id") FROM "<!-- $DB_PREFIX$ -->news"));

-- ----------------------------------------------------------------------------
-- comcms_newsletters
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->newsletters_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->newsletters" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->newsletters_id_seq'),
	"page" integer NOT NULL,
	"year" integer NOT NULL default '2008',
	"month" integer NOT NULL default '1',
	"label" text NOT NULL,
	"path" text NOT NULL,
	"hidden" integer NOT NULL default '0',
	PRIMARY KEY ("id")
);
SELECT setval('<!-- $DB_PREFIX$ -->newsletters_id_seq', (SELECT max("id") FROM "<!-- $DB_PREFIX$ -->newsletters"));

-- ----------------------------------------------------------------------------
-- comcms_pages
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->pages_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->pages" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->pages_id_seq'),
	"text_id" text NOT NULL,
	"title" text NOT NULL,
	"meta_desc" text NOT NULL,
	"show_title" integer NOT NULL default '1',
	"type" integer NOT NULL,
	"menu" integer NOT NULL,
	"page_group" integer NOT NULL default '1',
	"parent" integer NOT NULL default '0',
	"list" integer NOT NULL default '0',
	"blocks_left" text NULL,
	"blocks_right" text NULL,
	"hidden" integer NOT NULL default '0',
	PRIMARY KEY  ("id")
);
SELECT setval('<!-- $DB_PREFIX$ -->pages_id_seq', (SELECT max("id") FROM "<!-- $DB_PREFIX$ -->pages"));

-- ----------------------------------------------------------------------------
-- comcms_page_groups
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->page_groups_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->page_groups" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->page_groups_id_seq'),
	"label" text NOT NULL,
	PRIMARY KEY("id")
);
SELECT setval('<!-- $DB_PREFIX$ -->page_groups_id_seq', (SELECT max(id) FROM "<!-- $DB_PREFIX$ -->page_groups"));

-- ----------------------------------------------------------------------------
-- comcms_page_messages
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->page_messages_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->page_messages" (
	"message_id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->page_messages_id_seq') PRIMARY KEY,
	"page_id" integer NOT NULL,
	"text" text NOT NULL,
	"order" integer NOT NULL
);
SELECT setval('<!-- $DB_PREFIX$ -->page_messages_id_seq', (SELECT max(message_id) FROM "<!-- $DB_PREFIX$ -->page_messages"));

-- ----------------------------------------------------------------------------
-- comcms_pagetypes
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->pagetypes_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->pagetypes" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->pagetypes_id_seq'),
	"name" text NOT NULL,
	"description" text NOT NULL,
	"author" text NOT NULL,
	"filename" text NOT NULL,
	"class" text NOT NULL,
	PRIMARY KEY ("id")
);
SELECT setval('<!-- $DB_PREFIX$ -->pagetypes_id_seq', (SELECT max("id") FROM "<!-- $DB_PREFIX$ -->pagetypes"));

-- ----------------------------------------------------------------------------
-- comcms_poll_answers
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->poll_answers_answer_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->poll_answers" (
	"answer_id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->poll_answers_answer_id_seq'),
	"question_id" integer NOT NULL,
	"answer" text NOT NULL,
	"answer_order" integer NOT NULL,
	PRIMARY KEY ("answer_id")
);
SELECT setval('<!-- $DB_PREFIX$ -->poll_answers_answer_id_seq', (SELECT max("answer_id") FROM "<!-- $DB_PREFIX$ -->poll_answers"));

-- ----------------------------------------------------------------------------
-- comcms_poll_questions
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->poll_questions_question_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->poll_questions" (
	"question_id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->poll_questions_question_id_seq'),
	"question" text NOT NULL,
	"short_name" text NOT NULL,
	"type" integer NOT NULL default '1',
	"active" integer NOT NULL default '1',
	PRIMARY KEY  ("question_id")
);
SELECT setval('<!-- $DB_PREFIX$ -->poll_questions_question_id_seq', (SELECT max("question_id") FROM "<!-- $DB_PREFIX$ -->poll_questions"));

-- ----------------------------------------------------------------------------
-- comcms_poll_responses
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->poll_responses_response_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->poll_responses" (
	"response_id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->poll_responses_response_id_seq'),
	"question_id" integer NOT NULL,
	"answer_id" integer NOT NULL,
	"value" text,
	"ip_addr" integer NOT NULL,
	PRIMARY KEY ("response_id")
);
SELECT setval('<!-- $DB_PREFIX$ -->poll_responses_response_id_seq', (SELECT max("response_id") FROM "<!-- $DB_PREFIX$ -->poll_responses"));

-- ----------------------------------------------------------------------------
-- comcms_templates
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->templates_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->templates" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->templates_id_seq'),
	"path" text NOT NULL,
	"name" text NOT NULL,
	"description" text NOT NULL,
	"author" text NOT NULL,
	PRIMARY KEY ("id")
);
SELECT setval('<!-- $DB_PREFIX$ -->templates_id_seq', (SELECT max("id") FROM "<!-- $DB_PREFIX$ -->templates"));

-- ----------------------------------------------------------------------------
-- comcms_user_groups
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->user_groups_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->user_groups" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->user_groups_id_seq'),
	"name" text NOT NULL,
	"label_format" text NOT NULL,
	PRIMARY KEY ("id")
);
SELECT setval('<!-- $DB_PREFIX$ -->user_groups_id_seq', (SELECT max("id") FROM "<!-- $DB_PREFIX$ -->user_groups"));

-- ----------------------------------------------------------------------------
-- comcms_users
-- ----------------------------------------------------------------------------
CREATE SEQUENCE "<!-- $DB_PREFIX$ -->users_id_seq";
CREATE TABLE "<!-- $DB_PREFIX$ -->users" (
	"id" integer NOT NULL default nextval('<!-- $DB_PREFIX$ -->users_id_seq'),
	"type" integer NOT NULL default '1',
	"username" text NOT NULL,
	"password" text NOT NULL,
	"password_date" integer NOT NULL default '0',
	"realname" text NOT NULL,
	"title" text NULL,
	"groups" text NULL,
	"phone" text NOT NULL,
	"email" text NOT NULL,
	"address" text NOT NULL,
	"lastlogin" integer NOT NULL default '0',
	PRIMARY KEY ("id")
);
SELECT setval('<!-- $DB_PREFIX$ -->users_id_seq', (SELECT max("id") FROM "<!-- $DB_PREFIX$ -->users"))