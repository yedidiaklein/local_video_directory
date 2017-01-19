<?php

function human_filesize($bytes, $decimals = 2, $red = 0) {
  $sz = 'BKMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  	if (($red != 0) && ($bytes < $red))
	  return '<df style="color:red">' . sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor] . '</df>';
	else 
	  return '<df>' . sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor] . '</df>';
	
}

function local_video_directory_get_tagged_pages($tag, $exclusivemode = false, $fromctx = 0, $ctx = 0, $rec = 1, $page = 0) {
	global $CFG;
	//file_put_contents( $CFG->dataroot . "/tags.log", $tag, FILE_APPEND);
    // Find items.
    // Please refer to existing callbacks in core for examples.
 
    // ...
 
    // Use core_tag_index_builder to build and filter the list of items. 
    // Notice how we search for 6 items when we need to display 5 - this way we will know that we need to display a link to the next page.
    $builder = new core_tag_index_builder('local_video_directory', 'local_video_directory', $query, $params, $page * $perpage, $perpage + 1);
 
    // ...
 
//    $items = $builder->get_items();
//    if (count($items) > $perpage) {
//        $totalpages = $page + 2; // We don't need exact page count, just indicate that the next page exists.
//        array_pop($items);
//    }
 
    // Build the display contents.
//    if ($items) {
//        $tagfeed = new core_tag\output\tagfeed();
//        foreach ($items as $item) {
//            $tagfeed->add("hello");
//        }
 
//        $content = $OUTPUT->render_from_template('core_tag/tagfeed', $tagfeed->export_for_template($OUTPUT));
 
//        return new core_tag\output\tagindex($tag, 'local_video_directory', 'local_video_directory', $content,
//                $exclusivemode, $fromctx, $ctx, $rec, $page, $totalpages);
//    }
	return 1;
}
