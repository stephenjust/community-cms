<?php
	// Security Check
	if (@SECURITY != 1 || @ADMIN != 1) {
		die ('You cannot access this page directly.');
		}
	if(!isset($_GET['create'])) {
		$_GET['create'] = NULL;
		}
	if ($_GET['create'] == 1) {
		if (isset($_POST['username']) && isset($_POST['pass']) && isset($_POST['pass_conf']) && isset($_POST['first_name']) && isset($_POST['surname']) && isset($_POST['telephone']) && isset($_POST['address']) && isset($_POST['email'])) {
			$content = NULL;
			$username = addslashes($_POST['username']);
			$pass = $_POST['pass'];
			$pass_conf = $_POST['pass_conf'];
			if (eregi(',',$_POST['surname'])) {
				$content .= 'You cannot have a comma in your surname.<br />';
				$error = 1;
				}
			if (eregi(',',$_POST['first_name'])) {
				$content .= 'You cannot have a comma in your first name.<br />';
				$error = 1;
				}
			$real_name = addslashes($_POST['surname']).', '.addslashes($_POST['first_name']);
			$title = addslashes($_POST['title']);
			if (strlen($title) <= 1) {
				$title = NULL;
				}
            $groups = NULL;
            if(isset($_POST['groups'])) {
                $num_sel_groups = count($_POST['groups']);
                for($i = 0; $i < $num_sel_groups; $i++) {
                    $groups .= $_POST['groups'][$i].',';
                }
            }
			$telephone = addslashes($_POST['telephone']);
			$address = addslashes($_POST['address']);
			$email = addslashes($_POST['email']);
			$telephone_hide = checkbox($_POST['telephone_hide']);
			$address_hide = checkbox($_POST['address_hide']);
			$email_hide = checkbox($_POST['email_hide']);
			$hide = checkbox($_POST['hide']);
			$message = checkbox($_POST['message']);
			if (strlen($username) <= 5) {
				$content .= 'Your user name should be at least six characters.<br />';
				$error = 1;
				}
			if ($pass != $pass_conf || $pass == "" || $pass_conf == "") {
				$content .= 'Your passwords do not match, or you did not fill in one or more of the password fields.<br />';
				$error = 1;
				}
			if (strlen($pass) <= 7) {
				$content .= 'Your password must be at least eight characters.<br />';
				$error = 1;
				}
			if (!eregi('^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$',$email)) {
				$content .= 'You did not enter a valid email address.<br />';
				$error = 1;
				}
			if (strlen($telephone) <= 11 || !eregi('^[0-9\-]+\-[0-9]+\-[0-9]+$',$telephone)) {
				$content .= 'Your telephone number should include the area code, and should be in the format 555-555-1234 or 1-555-555-1234.<br />';
				$error = 1;
				}
			$check_user_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'users WHERE username = "'.$username.'"';
			$check_user_handle = $db->query($check_user_query);
			if (!$check_user_handle) {
				$content .= 'Failed to check if your username is already taken. '.$mysqli_error($db);
				}
			$check_user_num_rows = $check_user_handle->num_rows;
			if ($check_user_num_rows != 0) {
				$content .= 'Your username has already been taken. Please choose another.';
				$error = 1;
				}
			if ($error != 1) {
				$create_user_query = 'INSERT INTO '.$CONFIG['db_prefix']."users 
                    (type,username,password,realname,title,groups,phone,email,
                    address,phone_hide,email_hide,address_hide,hide,message)
                    VALUES (2,'$username','".md5($pass)."','$real_name',
                    '$title','$groups','$telephone','$email','$address',
                    $telephone_hide,$email_hide,$address_hide,$hide,$message)";
				$create_user = $db->query($create_user_query);
				if (!$create_user) {
					$content .= 'Your account could not be created.';
					} else {
					$content .= "Thank you, $real_name, your account has been created. ".log_action('New user \''.$real_name.'\'');
					}
				}
			} else {
			$content = 'You did not enter some required information. Please fill in all fields.';
			}
        } else {
        $tab_layout = new tabs;
        $form = new form;
        $form->set_target('admin.php?module=user_create&amp;create=1');
        $form->set_method('post');
        $form->add_textbox('username','User Name');
        $form->add_password('pass','Password');
        $form->add_password('pass_conf','Confirm Password');
        $form->add_textbox('first_name','First Name');
        $form->add_textbox('surname','Surname');
        $form->add_textbox('title','Title/Position');
        $form->add_textbox('telephone','Phone Number');
        $form->add_checkbox('telephone_hide','Hide Phone Number',1);
        $form->add_textbox('address','Address');
        $form->add_checkbox('address_hide','Hide Address',1);
        $form->add_textbox('email','Email Address');
        $form->add_checkbox('email_hide','Hide Email Address',1);
        $form->add_text('If you would not like your phone number, email, or
            address to be displayed publicly, please check the boxes above.
            However, if you would like to allow people to see all or some of
            this information, uncheck the boxes corresponding to the values
            that you would like to be visible. You must enter this information
            so that the website administrators may contact you if the need
            arises. To hide your contact entry completely, check the box
            below.');
        $form->add_checkbox('hide','Hide on Contacts Page');
        $form->add_checkbox('message','Allow Recieving Messages');
        $group_list_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'user_groups ORDER BY name ASC';
        $group_list_handle = $db->query($group_list_query);
        $group_list_rows = $group_list_handle->num_rows;
        if($group_list_rows == 0) {
            $form->add_text(' An error may have occured. No groups were found.');
        } else {
            for ($i = 0; $i < $group_list_rows; $i++) {
                $group_list = $group_list_handle->fetch_assoc();
                $group_list_id[$i] = $group_list['id'];
                $group_list_name[$i] = $group_list['name'];
            }
            $form->add_multiselect('groups','Groups',$group_list_id,$group_list_name);
        }
        $form->add_submit('submit','Create User');
        $tab_content['create'] = $form;
        $tab_layout->add_tab('Create User',$tab_content['create']);
        $content .= $tab_layout;
		}
?>