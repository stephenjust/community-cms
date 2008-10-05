<?php $notebook_content = "<script type='text/javascript'>
var notebook=new Array()\n";
$notebook = get_row_from_db('notebook');
$i = 0;
$j = 1;
while ($i < $notebook['num_rows']) {
  $notebook_content = $notebook_content."notebook[$i]='".$notebook[$j]['message']."'\n";
  $i++;
  $j++;
}

$notebook_content = $notebook_content."</script>";
?>