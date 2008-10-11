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
	  if(strlen($image) <= 3) { 
	  	$image = NULL;
	  	}
	  $new_article_query = 'INSERT INTO '.$CONFIG['db_prefix']."news (page,name,description,author,image,date) VALUES ($page,'$title','$content','$author','$image','$date')";
		$new_article = $db->query($new_article_query);
		if(!$new_article) {
			$content = 'Failed to add article. '.errormesg(mysqli_error());
			} else {
			$content = 'Successfully added article.';
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
		$content = $content.'</select></td></tr>
<tr><td width="150" valign="top" class="row2">Image:</td><td class="row2">'.file_list('newsicons',2).'</td></tr>
<tr><td width="150" class="row1">&nbsp;</td><td class="row1"><input type=\'submit\' value=\'Submit\' /></td></tr>
</table>
</form>
<form method="POST" action="?module=news&action=del">';
		}
	?>