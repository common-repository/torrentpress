<?php
/*
Plugin Name: TorrentPress
Version: 0.1-alpha
Description: A Torrent Tracker builtin to your Blog.
Author: Dion Hulse

Author URI: http://dd32.id.au/
Plugin URI: http://dd32.id.au/wordpress-plugins/torrentpress/
*/

add_action('init', 'tp_init');
function tp_init(){
	global $torrentpress;
	if( ! $torrentpress ){
		include 'class/torrentpress.class.php';
		$torrentpress = new torrentpress();
		do_action('torrentpress_init'); //For extra TP plugins/etc
	}
}

add_action('tp_admin-dashboard','tp_dashboard');
function tp_dashboard(){
	include 'admin-main.php';
}
add_action('tp_admin-edit','tp_edit');
function tp_edit(){
	if ( isset($_GET['action']) && in_array($_GET['action'], array('edit','delete','update') ) )
		include 'admin-edit-actions.php';
	include 'admin-edit-rows.php';
}

add_action('tp-announce','tp_announce');
function tp_announce(){
/*
$_GET:
   'info_hash' => 'h ò(žS¹a·äË\\àeÊòì',
   'peer_id' => '-UT161B-ãÌT½Ùä½â',
   'port' => '31718',
   'uploaded' => '0',
   'downloaded' => '0',
   'left' => '8919336',
   'key' => 'BC93E32F',
   'event' => 'started',
   'numwant' => '200',
   'compact' => '1',
   'no_peer_id' => '1',
   (*/
	global $wp_query;
	
	$hash = $wp_query->query_vars['name']; //bin2hex($_GET['info_hash']);
	
	//$posts = query_posts('post_type=torrent&name=' . $hash);
	
	echo "Announce script here for torrent $hash";
//0005510182015821425
}




add_filter('posts_where','tp_add_torrents');
function tp_add_torrents($where){
	global $wpdb;
	if( ! is_admin() )
		$where = str_replace("{$wpdb->posts}.post_type = 'post'", "({$wpdb->posts}.post_type = 'post' OR {$wpdb->posts}.post_type = 'torrent')", $where);
	return $where;
}

/** Rewrites **/
add_action('template_redirect','tp_template', 7);
function tp_template($arg){
    global $wp_query;
    if( !isset($wp_query->query_vars['tp']) )
        return $arg;

	do_action('tp-' . $wp_query->query_vars['tp'], $wp_query->query_vars['tp']);
	die();
    //die("TorrentPress: " . $wp_query->query_vars['tp'] . '; Torrent:' . $wp_query->query_vars['torrent'] );
}

add_action('parse_request', 'tp_request');
function tp_request($wp){
	switch( $wp->request) {
		case 'announce':
		case 'scrape':
			if( isset($_GET['info_hash']) )
				$wp->query_vars['name'] = $_GET['info_hash']; //bin2hex($_GET['info_hash']);
			break;
		default:
	}
}

add_action('generate_rewrite_rules', 'tp_add_rewrite_rules');
function tp_add_rewrite_rules( $wp_rewrite ) {
	$new_rules = array( 
						"(torrent)/(.*)" => 'index.php?tp=view&name=' . $wp_rewrite->preg_index(2),
						"(announce|scrape)" => 'index.php?tp=' . $wp_rewrite->preg_index(1));
	$wp_rewrite->rules = $wp_rewrite->rules + $new_rules;
}

add_filter('query_vars', 'tp_queryvars' );
function tp_queryvars( $qvars ){
    $qvars[] = 'tp';
	$qvars[] = 'torrent';
    return $qvars;
}

add_action('init','tp_activate');
function tp_activate(){
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
}

?>