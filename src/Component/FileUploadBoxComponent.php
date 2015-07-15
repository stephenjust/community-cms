<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.Component
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2010-2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS\Component;

use CommunityCMS\acl;
use CommunityCMS\Tpl;
use CommunityCMS\UISelectDirList;

/**
 * Displays a file upload form
 */
class FileUploadBoxComponent extends BaseComponent
{
    private $directory = null;
    private $show_dirs = false;
    private $extra_fields = array();

    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    public function setShowDirectories($show_dirs)
    {
        $this->show_dirs = $show_dirs;
    }

    public function addExtraField($name, $value)
    {
        $this->extra_fields[$name] = $value;
    }

    public function render()
    {
        acl::get()->require_permission("file_upload");
        
        $form_args = $_GET;
        $form_args["upload"] = "upload";
        if (array_key_exists("action", $form_args)) {
            unset($form_args["action"]);
        }

        $tpl = new Tpl();
        $tpl->assign("action", sprintf("%s?%s", $_SERVER["SCRIPT_NAME"], http_build_query($form_args)));
        $tpl->assign("directory", $this->directory);
        $tpl->assign("show_directories", $this->show_dirs);
        $tpl->assign("directory_list_form", $this->directoryList());
        $tpl->assign("extra_fields", $this->extra_fields);
        return $tpl->fetch("fileUploadBox.tpl");
    }

    /**
     * FIXME: Break this out into a different component
     * @return string
     */
    private function directoryList()
    {
        // Remember path from previous upload
        if (isset($_POST['path'])) {
            $current_dir = $_POST['path'];
        } else {
            $current_dir = '';
        }
        $return = '<br /><label for="path">Where would you like to save the file?</label> ';
        $dir_list = new UISelectDirList(array('name' => 'path'));
        $dir_list->setChecked($current_dir);
        $return .= $dir_list.'<br /><br />'."\n";
        return $return;
    }
}
