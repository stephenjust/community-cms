<?php
    $template_path = '../templates/default/';
    $template_file = $template_path."index.html";
    $handle = fopen($template_file, "r");
    $template = fread($handle, filesize($template_file));
    fclose($handle);
    $css_include = "<link rel='StyleSheet' type='text/css' href='".$template_path."style.css' />";
    $image_path = $template_path.'images/';
    switch($_GET[page]) {
      default:
      include ('./install.php');
      break;
      case 2:
      include ('./install2.php');
      break;
      case 3:
      include ('./install3.php');
      break;
    }
    $template = str_replace('<!-- $PAGE_TITLE$ -->','Community CMS Installer',$template);
    $template = str_replace('<!-- $CSS_INCLUDE$ -->',$css_include,$template);
    $template = str_replace('<!-- $IMAGE_PATH$ -->',$image_path,$template);
    $template = str_replace('<!-- $NAV_BAR$ -->',$nav_bar,$template);
    $template = str_replace('<!-- $NAV_LOGIN$ -->',$nav_login,$template);
    $template = str_replace('<!-- $CONTENT$ -->',$content,$template);
    $template = str_replace('<!-- $FOOTER$ -->','Community CMS Copyright&copy; 2008 Stephen Just',$template);
    echo $template;
?>