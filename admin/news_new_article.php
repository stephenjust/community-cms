<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	$root = "./";
	$message = NULL;
	$date = date('Y-m-d H:i:s');
	if ($_GET['action'] == 'new') {
		// Clean up variables.
		$title = addslashes($_POST['title']);
		$title = str_replace('"','&quot;',$title);
		$title = str_replace('<','&lt;',$title);
		$title = str_replace('>','&gt;',$title);
		$content = addslashes($_POST['content']);
		$author = addslashes($_POST['author']);
		$image = addslashes($_POST['image']);
		$page = addslashes($_POST['page']);
		$showdate = $_POST['date_params'];
	  if(strlen($image) <= 3) { 
	  	$image = NULL;
	  	}
	  $new_article_query = 'INSERT INTO '.$CONFIG['db_prefix']."news (page,name,description,author,image,date,showdate) VALUES ($page,'$title','$content','$author','$image','$date','$showdate')";
		$new_article = $db->query($new_article_query);
		if(!$new_article) {
			$content = 'Failed to add article. '.errormesg(mysqli_error());
			} else {
			$content = 'Successfully added article. '.log_action('New news article \''.$title.'\'');
			}
		} else {
            $tab_layout = new tabs;
            $form = new form;
            $form->set_target('admin.php?module=news_new_article&amp;action=new');
            $form->set_method('post');
            $form->add_textbox('title','Heading');
            $form->add_hidden('author',$_SESSION['name']);
            $form->add_textarea('content','Content',NULL,'rows="30"');
            $form->add_page_list('page','Page',1,1);
            $form->add_icon_list('image','Image','newsicons');
            $form->add_select('date_params','Date Settings',array(0,1,2),array('Hide','Show','Show Mini'),2);
            $form->add_submit('submit','Create Article');
            $tab_content['create'] .= $form;
            $tab_layout->add_tab('Create Article',$tab_content['create']);
            $content .= $tab_layout;
		}
?>