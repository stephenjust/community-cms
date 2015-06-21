<?php
/**
 * Community CMS Help Browser
 *
 * @copyright Copyright (C) 2010 Stephen Just
 * @author    stephenjust@users.sourceforge.net
 * @package   CommunityCMS.help
 */

namespace CommunityCMS;
function help_read_list() 
{
    $listfile = ROOT.'help/help_contents.xml';
    $help_files = array();
    $help_files['by-id'] = array();
    $help_files['by-label'] = array();
    $help_files['by-file'] = array();
    $help_files['by-cat'] = array();

    $xmlreader = new XMLReader;
    $xmlreader->open($listfile);
    while ($xmlreader->read()) {
        // Skip comments and other useless nodes
        if ($xmlreader->nodeType == XMLREADER::DOC_TYPE 
            || $xmlreader->nodeType == XMLREADER::COMMENT 
            || $xmlreader->nodeType == XMLREADER::XML_DECLARATION
        ) {
            continue;
        }
        // Handle categories
        if ($xmlreader->name == 'category' && $xmlreader->nodeType == XMLREADER::ELEMENT) {
            $cat_name = $xmlreader->getAttribute('name');
        }
        if ($xmlreader->name == 'category' && $xmlreader->nodeType == XMLREADER::END_ELEMENT) {
            $cat_name = null;
        }
        // Handle items
        if ($xmlreader->name == 'helpfile' && $xmlreader->nodeType == XMLREADER::ELEMENT) {
            $item_id = $xmlreader->getAttribute('id');
            $item_label = $xmlreader->getAttribute('label');
            $item_file = $xmlreader->getAttribute('file');
            $help_files['by-cat'][$cat_name][] = $item_id;
            $id_array = array('cat'=>$cat_name,'label'=>$item_label,'file'=>$item_file);
            $help_files['by-id'][$item_id] = $id_array;
            $label_array = array('cat'=>$cat_name,'id'=>$item_label,'file'=>$item_file);
            $help_files['by-label'][$item_label] = $label_array;
            $file_array = array('cat'=>$cat_name,'id'=>$item_id,'label'=>$item_label);
            $help_files['by-file'][$item_file] = $file_array;
        }
    }
    $xmlreader->close();
    return $help_files;
}

function help_menu($help_files) 
{
    $return = null;
    foreach ($help_files['by-cat'] AS $cat=>$items) {
        $return .= '<h2>'.$cat.'</h2>'."\n";
        $return .= "<ul>\n";
        foreach ($items AS $item) {
            $return .= "\t<li><a href=\"index.php?id=$item\">{$help_files['by-id'][$item]['label']}</a></li>\n";
        }
        $return .= "</ul>\n";
    }
    return $return;
}
?>
