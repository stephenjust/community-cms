<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.admin
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2007-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS;

use CommunityCMS\Component\TableComponent;

// Security Check
if (@SECURITY != 1 || @ADMIN != 1) {
    die ('You cannot access this page directly.');
}

if (!acl::get()->check_permission('adm_config_view')) {
    throw new AdminException('You do not have the necessary permissions to access this module.'); 
}

// ----------------------------------------------------------------------------

// Get all configuration values
$config_values = SysConfig::get()->getAll();

$config_table_values = array();
foreach ($config_values as $key => $value) {
    $config_table_values[] = [HTML::schars($key), HTML::schars($value)];
}

// Draw the interface
$tab_layout = new Tabs;
$tab_layout->add_tab('View Configuration', TableComponent::create(array('Name','Value'), $config_table_values));

echo $tab_layout;
