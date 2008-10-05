<?php
  	// Security Check
	if ($security != 1) {
		die ('You cannot access this page directly.');
		}
	if (!isset($_GET[id])) {
	  $current = 1;
	} else {
	  $current = $_GET[id];
	}
	echo ('
<body>
<div class="header"></div>
<div class="notebook">
		<script type="text/javascript">
		//new pausescroller(name_of_message_array, CSS_ID, CSS_classname, pause_in_miliseconds)
		new pausescroller(notebook, "pscroller1", "someclass", 3000)
		</script>
</div>
<div class="navigation">
'.display_nav_bar($connect,$current).'
</div>
');
?>