<?php
  $nav_bar = "<div align='center'><span style='color: #00CC00;'>Check file permissions</span><hr />\n<span style='color: #CCCC00;'>Configure settings</span><hr />\n<span style='color: #CC0000;'>Download/save config file</span></div>\n";
  $content = "<h1>Configure Settings</h1>\n";
  $content .= "<form method='post' action='index.php?page=3'>\n";
  $content .= "<table>\n<tr>\n<th>\nSetting\n</th>\n<th align='left'>\nValue\n</th>\n</tr>\n";
  $content .= "<tr>\n<td>\nWebsite Name\n</td>\n<td>\n<input type='text' name='sitename' />\n</td>\n</tr>\n";
  $content .= "<tr>\n<td>\nMySQL Host\n</td>\n<td>\n<input type='text' name='dbhost' value='localhost' />\n</td>\n</tr>\n";
  $content .= "<tr>\n<td>\nMySQL Database\n</td>\n<td>\n<input type='text' name='dbname' />\n</td>\n</tr>\n";
  $content .= "<tr>\n<td>\nMySQL User\n</td>\n<td>\n<input type='text' name='dbuser' />\n</td>\n</tr>\n";
  $content .= "<tr>\n<td>\nMySQL Password\n</td>\n<td>\n<input type='text' name='dbpass' />\n</td>\n</tr>\n";
  $table_prefix = 'comcms_'.rand(1000,9999).'_';
  $content .= "<tr>\n<td>\nMySQL Table Prefix\n</td>\n<td>\n<input type='text' name='dbpfix' value='$table_prefix' />
  This value is randomized to add a small layer of security.\n</td>\n</tr>\n";
  $content .= "<tr>\n<td>\nAdmin Username\n</td>\n<td>\n<input type='text' name='admin_username' value='admin' />";
  $content .= "<tr>\n<td>\nAdmin Password\n</td>\n<td>\n<input type='text' name='admin_pwd' value='password' />";
  $content .= "<tr>\n<td>\nConfirm Password\n</td>\n<td>\n<input type='text' name='admin_pwd_conf' value='password' />";
  $content .= "<tr>\n<td>\n&nbsp;\n</td>\n<td>\n<input type='submit' value='Submit' />\n</td>\n</tr>\n";
  $content .= "</table>\n";
  $content .= "</form>";
?>