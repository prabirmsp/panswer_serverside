<?php
header ( 'Content-type: text/json' );

$link = mysqli_connect ( 'localhost', 'root', 'password', 'panswer' );

// check connection
if (mysqli_connect_errno ()) {
	printf ( "Connect failed: %s\n", mysqli_connect_error () );
	exit ();
}

$JSONData = new stdClass (); // class for JSONData

if (isset ( $_GET ['get_items_from_dspace_id'] )) { // field set in URL
	$dspace_id = $_GET ['get_items_from_dspace_id'];
	
	$query_str = "SELECT * FROM ans_dspace_item WHERE collection_id = '" . $dspace_id . "' ORDER BY title";
	$items = array ();
	if ($result = mysqli_query ( $link, $query_str )) { // query for items
		
		while ( $row = mysqli_fetch_array ( $result ) ) { // go through items
			$item_dspace_id = $row ['dspace_id'];
			
			$bitstream_id = 0; // 0 set as initializer
			$document_href;
			$document_thumb_href;
			$document_size;
			
			$bit_query = 'SELECT * FROM ans_dspace_bitstream WHERE parent_dspace_id = ' . $item_dspace_id;
			if ($bitstream_result = mysqli_query ( $link, $bit_query )) { // query for bitstream

			    // if more than one document exists, pick the one with the higher 'id' field
				while ( $bitstream_row = mysqli_fetch_array ( $bitstream_result ) ) {
					if (intval ( $bitstream_row ['id'] ) > intval ( $bitstream_id )) {
							$bitstream_id = $bitstream_row ['id'];
							$document_href = $bitstream_row ['href'];
							$document_thumb_href = $bitstream_row ['thumb_href'];
							$document_size = $bitstream_row ['size'];
							$type = $bitstream_row ['type'];
					}
				}
			} else {
				printf ( 'Query did not return result' );
			}
			
			$items [] = array (
					'item_dspace_id' => $item_dspace_id,
					'collection_id' => $row ['collection_id'],
					'title' => urlencode($row ['title']),
					'creator' => urlencode($row ['creator']),
					'publisher' => urlencode($row ['publisher']),
					'description' => urlencode ($row ['description']),
					'language' => $row ['language'],
					'date_issued' => $row ['date_issued'],
					'type' => $type,
					// document (from bitstream)
					'bitstream_id' => $bitstream_id,
					'document_thumb_href' => urlencode($document_thumb_href),
					'document_href' => urlencode($document_href),
					'document_size' => $document_size 
			);
		}
	} else {
		printf ( 'Query did not return result' );
	}
	
	$JSONData->community_items = $items;
} 

else { // field not set in URL
	$query_str = 'SELECT * FROM ans_dspace_community where lft > (SELECT lft FROM ans_dspace_community WHERE selection_type = 1) and lft < (SELECT RGT FROM ans_dspace_community WHERE selection_type = 1) ORDER BY lft';
	$posts = array ();
	if ($result = mysqli_query ( $link, $query_str )) {
		// printf ( "Select returned %d rows.\n", mysqli_num_rows ( $result ) );
		
		while ( $row = mysqli_fetch_array ( $result ) ) {
			$posts [] = array (
					'id' => $row ['id'],
					'dspace_id' => $row ['dspace_id'],
					'parent_id' => $row ['parent_id'],
					'rgt' => $row ['lft'],
					'lft' => $row ['rgt'],
					'level' => $row ['level'],
					'title' => urlencode ( $row ['label'] ), // url encoded!
					'description' => urlencode ( $row ['description'] ), // url encoded!
					'alias' => $row ['alias'],
					'imageurl' => urlencode($row ['imageurl'] )
			);
		}
		
		// free result set
		mysqli_free_result ( $result );
	} else {
		printf ( 'Query did not return result' );
	}
	
	$JSONData->communities = $posts;
}
echo json_encode ( $JSONData );
?>
