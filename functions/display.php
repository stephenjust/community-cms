<?php
	// Security Check
	if (@SECURITY != 1) {
		die ('You cannot access this page directly.');
		}
	function display_page($page_info,$site_info,$view="") {
		$template = get_row_from_db("templates","WHERE id = ".$site_info['template']);
		$template_path = $template[1]['path'];
		$template_file = $template_path."index.html";
		$handle = fopen($template_file, "r");
		$template = fread($handle, filesize($template_file));
		fclose($handle);
		$page_title = $page_info['title'];
		// Initialize session variable if unset.
		if(!isset($_SESSION['type'])) {
			$_SESSION['type'] = 0;
			}
		if($_SESSION['type'] == 1) {
			$admin_include = "<script src=\"./scripts/admin.js\" type=\"text/javascript\"></script>";
			} else {
			$admin_include = NULL;
			}
		$css_include = "<link rel='StyleSheet' type='text/css' href='".$template_path."style.css' />";
		$image_path = $template_path.'images/';
		include ('notebook_content.php');
		$nav_bar = display_nav_bar();
		$nav_login = display_login_box($page_info,$site_info);
		$content = get_page_content($page_info['id'],$page_info['type'],$view);
		$template = str_replace('<!-- $PAGE_TITLE$ -->',$page_title,$template);
		$template = str_replace('<!-- $ADMIN_INCLUDE$ -->',$admin_include,$template);
		$template = str_replace('<!-- $CSS_INCLUDE$ -->',$css_include,$template);
		$template = str_replace('<!-- $NOTEBOOK_CONTENT$ -->',$notebook_content,$template);
		$template = str_replace('<!-- $NAV_BAR$ -->',$nav_bar,$template);
		$template = str_replace('<!-- $NAV_LOGIN$ -->',$nav_login,$template);
		$template = str_replace('<!-- $CONTENT$ -->',$content,$template);
		$template = str_replace('<!-- $IMAGE_PATH$ -->',$image_path,$template);
		$template = str_replace('<!-- $FOOTER$ -->','<a href="http://sourceforge.net"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=223968&amp;type=1" width="88" height="31" border="0" type="image/png" alt="SourceForge.net Logo" /></a>
 Powered by Community CMS',$template);
		echo $template;
		}

	function display_nav_bar($mode = "1") {
		global $page_info;
		global $db;
		global $CONFIG;
		$nav_menu_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE menu = '.$mode.' ORDER BY list ASC';
		$nav_menu_handle = $db->query($nav_menu_query);
		$i = 1;
		$return = NULL;
		while ($nav_menu_handle->num_rows >= $i) {
			$nav_menu = $nav_menu_handle->fetch_assoc();
			if ($nav_menu['id'] == $page_info['id']) {
				$return .= $nav_menu['title']."<br />";
				} else {
				$return .= "<a href='index.php?id=".$nav_menu['id']."'>".$nav_menu['title']."</a><br />";
				}
			$i++;
			}
		return $return;
		}
	
	function display_login_box($page_info,$site_info) {
		global $db;
		global $CONFIG;
	  if(!isset($_SESSION['user']) || !isset($_SESSION['pass'])) {
	    $return = "<form method='POST' action='index.php?id=".$page_info['id']."&login=1'>\nUser: <input type='text' name='user' /><br />\nPass: <input type='password' name='passwd' /><br />\n<input type='submit' value='Login!' /></form>\n";
	  } else { 
	    $return = $_SESSION['name']."<br />\n<a href='index.php?login=2'>Log Out</a><br />\n";
	    $check_message_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'messages WHERE recipient = '.$_SESSION['userid'];
	    $check_message_handle = $db->query($check_message_query);
	    $check_message = $check_message_handle->num_rows;
	    $return .= '<a href="messages.php">'.$check_message." new messages</a><br />\n";
	    $return .= mysqli_error($db);
	    if($_SESSION['type'] == 1) {
	      $return .= "<a href='admin.php?".SID."'>Admin</a>";
	    }
	  }
	  return $return;
	}
	
	function display_news_content($page = 1,$entry = 0,$entries = 10,$custom = "") {
		global $site_info;
		$i = 1;
		$news_row = get_row_from_db("news","WHERE page = ".$page." ".$custom." ORDER BY date desc LIMIT ".$entry.",".$entries);
		$template = get_row_from_db("templates","WHERE id = ".$site_info['template']);
		$template_path = $template[1]['path'];
		$template_file = $template_path."article.html";
		$handle = fopen($template_file, "r");
		$template = fread($handle, filesize($template_file));
		fclose($handle);
		// Initialize session variable if not initialized to prevent warnings.
		if(!isset($_SESSION['user'])) {
		  $_SESSION['user'] = NULL;
			}
		$return = '<script type="text/javascript">
setVarsForm("user='.$_SESSION['user'].'");
</script>';
		if($news_row['num_rows'] == 0) {
			$return = $return.'There are no articles to be displayed.';
			} else {
			while ($news_row['num_rows'] >= $i) {
				$article = $template;
				if (!isset($news_row[$i]['image']) || $news_row[$i]['image'] == "") {
					$picture = "";
					} else {
					$picture = "<img src='".$news_row[$i]['image']."' alt='".$news_row[$i]['image']."' align='left' width='100' height='100' style='padding: 5px; padding-right: 10px;' />";
					}
				$date = substr($news_row[$i]['date'],0,10);
				$date_parts = explode('-',$date);
				$date_year = $date_parts[0];
				$date_month = $date_parts[1];
				$date_day = $date_parts[2];
				$date_unix = mktime(0,0,0,$date_month,$date_day,$date_year);
				$date_month_text = date('M',$date_unix);
				$image_path = NULL;
				$article = str_replace('<!-- $ARTICLE_TITLE$ -->',stripslashes($news_row[$i]['name']),$article);
				$article = str_replace('<!-- $ARTICLE_CONTENT$ -->',stripslashes($news_row[$i]['description']),$article);
				$article = str_replace('<!-- $ARTICLE_IMAGE$ -->',$picture,$article);
				$article = str_replace('<!-- $ARTICLE_ID$ -->',$news_row[$i]['id'],$article);
				$article = str_replace('<!-- $ARTICLE_DATE_MONTH$ -->',$date_month,$article);
				$article = str_replace('<!-- $ARTICLE_DATE_MONTH_TEXT$ -->',strtoupper($date_month_text),$article);
				$article = str_replace('<!-- $ARTICLE_DATE_DAY$ -->',$date_day,$article);
				$article = str_replace('<!-- $ARTICLE_DATE_YEAR$ -->',$date_year,$article);
				$article = str_replace('<!-- $ARTICLE_DATE$ -->',$date,$article);
				$article = str_replace('<!-- $IMAGE_PATH$ -->',$image_path,$article);
				$article = str_replace('<!-- $ARTICLE_AUTHOR$ -->',stripslashes($news_row[$i]['author']),$article);
				$i++;
				$return = $return.$article;
				}
			}
		return $return;
		}
		
	function display_newsletters($id) {
		$i = 1;
		$newsletter = get_row_from_db("newsletters","WHERE page = ".$id." ORDER BY year desc, month desc LIMIT 0,30");
		if($newsletter['num_rows'] == 0) {
			$return = "No newsletters to display";
			} else {
			$currentyear = $newsletter[1]['year'];
			$return = "<div class='newsletter'><span class='newsletter_year'>".$currentyear."</span><br />\n";
			while ($newsletter['num_rows'] >= $i) {
				if ($currentyear != $newsletter[$i]['year']) {
					$currentyear = $newsletter[$i]['year'];
					$return = $return."<span class='newsletter_year'>".$currentyear."</span><br />\n";
					}
				if ($newsletter[$i]['hidden'] != 1) {
					$return = $return.'<a href="'.$newsletter[$i]['path'].'">'.$newsletter[$i]['label']."</a><br />\n";
					} else {
					$return = $return.$newsletter[$i]['label']."<br />\n";
					}
				$i++;
				}
			}
		return $return."</div>";
		}
?>