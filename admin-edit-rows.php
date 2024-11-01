<?php
wp_enqueue_script( 'wp-lists' );
?>
<script>
/* <![CDATA[ */
jQuery(function($){$('#the-list').wpList();});
/* ]]> */
</script>
<div class="wrap">
<h2><?php
// Use $_GET instead of is_ since they can override each other
$h2_search = isset($_GET['s']) && $_GET['s'] ? ' ' . sprintf(__('matching &#8220;%s&#8221;'), wp_specialchars( stripslashes( $_GET['s'] ) ) ) : '';
$h2_author = '';
if ( isset($_GET['author']) && $_GET['author'] ) {
	$author_user = get_userdata( (int) $_GET['author'] );
	$h2_author = ' ' . sprintf(__('by %s'), wp_specialchars( $author_user->display_name ));
}
printf( _c( '%1$s%2$s%3$s|You can reorder these: 1: Torrents, 2: by {s}, 3: matching {s}' ), $post_status_label, $h2_author, $h2_search );
?></h2>

<p><?php _e('Torrents are like posts and pages except they are much cooler and live outside of the normal blog chronology. You can use torrents to share small and large files, to provide a P2P experience like no other.'); ?></p>

<form name="searchform" id="searchform" action="" method="get">
	<fieldset><legend><?php _e('Search Terms&hellip;') ?></legend>
		<input type="text" name="s" id="s" value="<?php echo attribute_escape( stripslashes( $_GET['s'] ) ); ?>" size="17" />
	</fieldset>

<?php
	global $user_ID;
	$editable_ids = get_editable_user_ids( $user_ID );
	if ( $editable_ids && count( $editable_ids ) > 1 ) : ?>

	<fieldset><legend><?php _e('Author&hellip;'); ?></legend>
		<?php wp_dropdown_users( array('include' => $editable_ids, 'show_option_all' => __('Any'), 'name' => 'author', 'selected' => isset($_GET['author']) ? $_GET['author'] : 0) ); ?>
	</fieldset>

<?php endif; ?>

	<input type="submit" id="post-query-submit" value="<?php _e('Filter &#187;'); ?>" class="button" />
</form>

<br style="clear:both;" />

<?php
$query_str = "post_type=torrent&orderby=menu_order&posts_per_page=-1&posts_per_archive_page=-1&order=asc";
wp($query_str);

$all = !( $h2_search || $post_status_q );
global $posts, $post;
if ($posts) {
?>
<table class="widefat">
  <thead>
  <tr>
    <th scope="col" style="text-align: center"><?php _e('ID') ?></th>
    <th scope="col"><?php _e('Title') ?></th>
    <th scope="col"><?php _e('Uploader') ?></th>
	<th scope="col"><?php _e('Last Modified') ?></th>
	<th scope="col"><?php _e('Seeds/Peers') ?></th>
	<th scope="col" colspan="3" style="text-align: center"><?php _e('Action'); ?></th>
  </tr>
  </thead>
  <tbody id="the-list" class="list:page">
<?php
	foreach($posts as $torrent){
		$post = $torrent;
		setup_postdata($torrent);

		$torrent->post_title = wp_specialchars( $torrent->post_title );
		$id = (int) $torrent->ID;
		$class = ('alternate' == $class ) ? '' : 'alternate';
		
		$seeds = count( (array)get_post_meta($torrent->ID, 'seeds'));
		$peers = count( (array)get_post_meta($torrent->ID, 'peers'));
?>
  <tr id='torrent-<?php echo $id; ?>' class='<?php echo $class; ?>'>
    <th scope="row" style="text-align: center"><?php echo $torrent->ID; ?></th>
    <td><?php the_title(); ?></td>
    <td><?php the_author() ?></td>
    <td><?php 	if ( '0000-00-00 00:00:00' == $torrent->post_modified )
					_e('Unpublished');
			  	else
					echo mysql2date( __('Y-m-d g:i a'), $torrent->post_modified ); 
				?></td>
	<td><?php echo $seeds, '/', $peers; ?></td>
    <td><a href="<?php the_permalink(); ?>" rel="permalink" class="view"><?php _e( 'View' ); ?></a></td>
    <td><?php if ( current_user_can( 'edit_post', $id ) ) { echo "<a href='?page=torrentpress/edit&amp;action=edit&amp;id=$id' class='edit'>" . __( 'Edit' ) . "</a>"; } ?></td>
    <td><?php if ( current_user_can( 'delete_post', $id ) ) { echo "<a href='" . wp_nonce_url( "?page=torrentpress/edit&amp;action=delete&amp;id=$id", 'delete_torrent-' . $id ) .  "' class='delete:the-list:page-$id delete'>" . __( 'Delete' ) . "</a>"; } ?></td>
  </tr>

<?php } ?>
  </tbody>
</table>

<div id="ajax-response"></div>

<?php
} else {
?>
<p><?php _e('No torrents found.') ?></p>
<?php
} // end if ($posts)
?>

</div>