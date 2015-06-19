<?php
header('Content-type: text/json');
$link = mysql_connect('localhost', 'root', 'password');
if (!$link) {
	die('Could not connect: ' . mysql_error());
}else{
	$db = mysql_select_db("panswer", $link);

}
$jsonData = new stdClass();
$posts_items = array();
$var = "desc";
if(isset($_GET['dspace_id'])){
	$hh = new stdClass();
	$dspace_id = ($_GET['dspace_id']);
echo $dspace_id;
	$sql = 'SELECT * FROM ans_dspace_community WHERE parent_id = ' . $dspace_id;
	$result = mysql_query($sql,$link);
//echo mysql_num_rows($result);
	while($row=mysql_fetch_array($result)){
		$desc = $row['description'];

	}
	$sql_item = 'SELECT * FROM 	ans_dspace_item WHERE collection_id = ' . $dspace_id;
	$result_item = mysql_query($sql_item,$link);
	$rowCount = mysql_num_rows($result_item);


	while($row_item=mysql_fetch_array($result_item)){
		$id_item = $row_item['id'];
		$title_item = urlencode($row_item['title']);
		$desc_item = urlencode($row_item['description']);
		$dspace_id_item = $row_item['dspace_id'];
		$alias_item = $row_item['alias'];
		$desc_item = urlencode($row_item['description']);
		$sql_bitstream = 'SELECT * FROM ans_dspace_bitstream WHERE parent_dspace_id = '. $dspace_id_item.' AND size <> 0 ORDER BY id DESC LIMIT 0, 1';
		$result_bitstream = mysql_query($sql_bitstream,$link);
		while($row_bitstream = mysql_fetch_array($result_bitstream)){
			$thumb = $row_bitstream['thumb_href'];

		}
		$posts_items[] = array('item_id'=> $id_item, 'item_title'=> $title_item, 'item_desc'=> $desc_item, 'thumb'=>$thumb, 'alias'=>$alias_item, 'description'=>$desc_item);
	}

//$posts[] = array('desc'=> $desc);

print_r($posts_items);
	$hh->community = $posts_items;
	echo json_encode($hh);
}
else{


	$sql    = 'SELECT * FROM ans_dspace_community where lft > (SELECT lft FROM ans_dspace_community WHERE selection_type = 1) and lft < (SELECT RGT FROM ans_dspace_community WHERE selection_type = 1) ORDER BY lft';
	$result = mysql_query($sql, $link);
	$response = array();
	$posts = array();
	$sub_community_array = array();
	while($row=mysql_fetch_array($result)) 
	{ 
		$id=$row['id']; 
		$dspace_id=$row['dspace_id'];
		$title=$row['label']; 
		$parent_id=$row['parent_id'];
		$level=$row['level'];
		$lft=$row['lft'];
		$rgt=$row['rgt'];
		$img=$row['imageurl'];
		$posts[] = array('id'=> $id, 'title'=> $title, 'parent_id'=> $parent_id, 'level'=> $level, 'dspace_id'=> $dspace_id, 'rgt'=>$rgt, 'lft'=>$lft, 'imageurl'=> $img);


//$response['posts'] = $posts;
 //$json[] = $row;
	} 
	$jsonData->communities = $posts;
	echo json_encode($jsonData);
}
?> 
