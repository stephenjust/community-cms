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
		$content = '</form>
<form method="POST" action="admin.php?module=news_new_article&action=new">
<h1>Create New Article</h1>
<table class="admintable">
<input type="hidden" name="author" value="'.$_SESSION['name'].'" />
<tr><td width="150" class="row1">Heading:</td><td class="row1"><input type="text" name="title" /></td></tr>
<tr><td class="row2" valign="top">Content:</td>
<td class="row2"><textarea name="content" rows="30"></textarea></td></tr>
<tr><td width="150" class="row1">Page:</td><td class="row1">
<select name="page">';
		$page_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'pages WHERE type = 1 ORDER BY list ASC';
		$page_query_handle = $db->query($page_query);
 		$i = 1;
		while ($i <= $page_query_handle->num_rows) {
			$page = $page_query_handle->fetch_assoc();
			$content = $content.'<option value="'.$page['id'].'" />'.$page['title'].'</option>';
			$i++;
			}
		$content = $content.'<option value="0">No Page</option>
</select></td></tr>
<tr><td width="150" valign="top" class="row2">Image:</td><td class="row2">'.file_list('newsicons',2).'</td></tr>
<tr><td width="150" class="row1" valign="top">Date:</td><td class="row1">
<select name="date_params">
<option value="0">Hide Date</option>
<option value="1" selected>Show Date</option>
<option value="2">Show Mini</option>
</select>
</td></tr>
<tr><td width="150" class="row2">&nbsp;</td><td class="row2"><input type=\'submit\' value=\'Submit\' /></td></tr>
</table>
</form>
<form method="POST" action="?module=news&action=del">';
		}
	?>