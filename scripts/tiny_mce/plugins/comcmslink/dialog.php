<?php

namespace CommunityCMS;

/**
 * @ignore
 */
DEFINE('SECURITY', 1);
DEFINE('ADMIN', 1);
DEFINE('ROOT', '../../../../');
require_once(ROOT.'vendor/autoload.php');
include (ROOT.'include.php');
initialize();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>{#comcmslink_dlg.title}</title>
        <script type="text/javascript" src="../../tiny_mce_popup.js"></script>
        <script type="text/javascript" src="../../utils/mctabs.js"></script>
        <script type="text/javascript" src="../../utils/form_utils.js"></script>
        <script type="text/javascript" src="../../utils/validate.js"></script>
        <script type="text/javascript" src="js/dialog.js"></script>
        <script type="text/javascript">
            var urlBaseDFL = "../../../../admin/scripts/dynamic_file_list.php";

            function update_dynamic_file_list() {
                var dynamiclistdiv = document.getElementById('dynamic_file_list');
                var folderlist = document.getElementById('dynamic_folder_dropdown_box');
                var newfolder = folderlist.value;
                loadHTML(urlBaseDFL + "?newfolder=" + encodeURI(newfolder), dynamiclistdiv);
            }

            var urlBaseDALL = "../../../../admin/scripts/dynamic_article_link_list.php";

            function update_dynamic_article_link_list() {
                var dynamiclistdiv = document.getElementById('dynamic_article_link_list');
                var pagelist = document.getElementById('page_select');
                var page = pagelist.value;
                loadHTML(urlBaseDALL + "?page=" + encodeURI(page), dynamiclistdiv);
            }
        </script>
        <script type="text/javascript" src="../../../../scripts/ajax.js"></script>
    </head>
    <body id="comcmslink" style="display: none;">
        <form onsubmit="ComCMSLinkDialog.insert();
                return false;" action="#">
            <div id="panel" class="panel current">
                <table border="0" cellpadding="4" cellspacing="0">
                    <tr>
                        <td class="nowrap"><label id="linktextpagelabel" for="linktext">{#comcmslink_dlg.link_text}</label></td>
                        <td><input id="linktext" name="linktext" type="text" value="" /></td>
                    </tr>
                </table>
                <fieldset>
                    <legend>{#comcmslink_dlg.link_to_what}</legend>
                    <table width="100%">
                        <tr>
                            <td style="text-align: right;">{#comcmslink_dlg.page}</td>
                            <td><input type="radio" id="type_page" name="type" value="page" checked /></td>
                            <td style="text-align: right;">{#comcmslink_dlg.file}</td>
                            <td><input type="radio" id="type_file" name="type" value="file" /></td>
                            <td style="text-align: right;">{#comcmslink_dlg.article}</td>
                            <td><input type="radio" id="type_article" name="type" value="article" /></td>
                        </tr>
                    </table>
                </fieldset>
                <fieldset>
                    <legend>{#comcmslink_dlg.page}</legend>
                    <table border="0" cellpadding="4" cellspacing="0">
                        <tr>
                            <td class="nowrap"><label id="pagelabel" for="page">{#comcmslink_dlg.page}</label></td>
                            <td>TODO</td>
                        </tr>
                    </table>
                </fieldset>
                <fieldset>
                    <legend>{#comcmslink_dlg.file}</legend>
                    File <div id="dynamic_file_list"><?php echo dynamic_file_list(); ?></div>
                </fieldset>
                <fieldset>
                    <legend>{#comcmslink_dlg.article}</legend>
                    <div id="dynamic_article_link_list"><?php echo dynamic_article_link_list(); ?></div>
                    <fieldset>
                        <legend>{#comcmslink_dlg.ar_link_type}</legend>
                        <label for="type_onpage">{#comcmslink_dlg.type_onpage}</label>
                        <input type="radio" name="mode" id="type_onpage" value="onpage" /><br />
                        <label for="type_nopage">{#comcmslink_dlg.type_nopage}</label>
                        <input type="radio" name="mode" id="type_nopage" value="nopage" checked /><br />
                        <label for="type_ownpage">{#comcmslink_dlg.type_ownpage}</label>
                        <input type="radio" name="mode" id="type_ownpage" value="ownpage" /><br />
                    </fieldset>
                </fieldset>
            </div>
            <div class="mceActionPanel">
                <div style="float: left">
                    <input type="button" id="insert" name="insert" value="{#insert}" onclick="ComCMSLinkDialog.insert();" />
                </div>

                <div style="float: right">
                    <input type="button" id="cancel" name="cancel" value="{#cancel}" onclick="tinyMCEPopup.close();" />
                </div>
            </div>
        </form>
<?php clean_up(); ?>
    </body>
</html>