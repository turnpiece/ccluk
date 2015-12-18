<?php

/**
 * Adding addtional markeup to the Archive widget
 * for custom styling
 */
add_filter('get_archives_link', 'politics_archive_count_span');
function politics_archive_count_span($links) {
  $links = str_replace('</a>&nbsp;(', '&nbsp;<span>', $links);
  $links = str_replace(')', '&nbsp;</span></a>', $links);
  $links = str_replace('(', '&nbsp;', $links);
  return $links;
}

/**
 * Adding addtional markeup to the Categories widget
 * fot custom styling
 */
add_filter('wp_list_categories', 'politics_cat_count_span');
function politics_cat_count_span($links) {
  $links = str_replace('</a> (', '&nbsp;<span>', $links);
  $links = str_replace(')', '</span>', $links);
  return $links;
}

?>
