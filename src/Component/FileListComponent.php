<?php
/**
 * Community CMS
 *
 * PHP Version 5
 *
 * @category  CommunityCMS
 * @package   CommunityCMS.Component
 * @author    Stephen Just <stephenjust@gmail.com>
 * @copyright 2015 Stephen Just
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, 2.0
 * @link      https://github.com/stephenjust/community-cms
 */

namespace CommunityCMS\Component;

use CommunityCMS\acl;
use CommunityCMS\File;

/**
 * Component to display a list of files
 */
class FileListComponent extends BaseComponent
{
    protected $directory = null;

    public function setDirectory($directory)
    {
        if (!preg_match("#^[a-z0-9_\\-/]*$#i", $directory)) {
            throw new \InvalidArgumentException("Illegal directory name!");
        }
        if ($directory != '') {
            $directory .= '/';
            $this->directory = str_replace('//', '/', $directory);
        } else {
            $this->directory = $directory;
        }
    }

    public function render()
    {
        $tpl = new \Smarty();
        $tpl->addTemplateDir(ROOT.'templates/');
        $tpl->setCompileDir(ROOT.'templates_c/');
        $tpl->assign("edit_url", $this->getEditUrl());
        $tpl->assign("delete_url", $this->getDeleteUrl());
        $tpl->assign("show_delete", acl::get()->check_permission('file_delete'));
        $tpl->assign("files", $this->getFileList());
        return $tpl->fetch("fileList.tpl");
    }

    protected function getFileList()
    {
        if ($this->directory === null) {
            throw new \Exception("Directory must be set before calling getFileList.");
        }

        $result = array();
        $files = scandir(FILES_ROOT.$this->directory);
        foreach ($files AS $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (is_dir(FILES_ROOT.$this->directory.$file)) {
                continue;
            }
            $result[] = new File($this->directory.$file);
        }
        return $result;
    }

    protected function getEditUrl()
    {
        return sprintf("admin.php?module=filemanager&amp;action=edit&amp;file=FILE&amp;path=%s", $this->directory);
    }

    protected function getDeleteUrl()
    {
        return sprintf("admin.php?module=filemanager&amp;action=delete&amp;file=FILE&amp;path=%s", $this->directory);
    }
}
