<?php
    // Security Check
    if (@SECURITY != 1) {
        die ('You cannot access this page directly.');
    }
    /**
     * get_block - Get contents of a block
     * @global array $CONFIG
     * @global resource $db
     * @param int $block_id ID of block to display
     * @return string
     */
    function get_block($block_id = NULL) {
        $block_id = (int)$block_id;
        if(strlen($block_id) < 1 || $block_id <= 0) {
            return;
        }
        global $CONFIG;
        global $db;
        $block_content = NULL;
        $block_query = 'SELECT * FROM '.$CONFIG['db_prefix'].'blocks WHERE id = '.$block_id.' LIMIT 1';
        $block_handle = $db->query($block_query);
        if($block_handle) {
            if($block_handle->num_rows == 1) {
                $block_info = $block_handle->fetch_assoc();
                $block_content .= include(ROOT.'content_blocks/'.$block_info['type'].'_block.php');
            } else {
                $block_content .= '<div class="notification"><strong>Error:</strong> Could not load block '.$block_id.'.</div>';
            }
        }
        return $block_content;
    }
?>