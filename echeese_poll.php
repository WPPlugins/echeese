<?php
/*
Plugin Name: eCheese Poll
Plugin URI: http://echeese.net/wordpress/
Description: Display polls created on eCheese.net
Version: 1.0
Author: eCheese
License: GPL2
*/
?>

<?php
/*  Copyright 2011  eCheese  (email : contact@echeese.net)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>

<?php
$ECHEESE_HTTP = 'http://echeese.net';

add_action('admin_menu', 'echeese_plugin_menu');
add_action('admin_head', 'load_into_head');
add_action( 'widgets_init', 'eCheeseWidgetInit' );

function eCheeseWidgetInit() {
	register_widget( 'eCheeseWidget' );
}

function echeese_plugin_menu() {
	add_menu_page('eCheese polls', 'eCheese polls', 'edit_posts', 'echeese_polls_view', 'view_polls', plugins_url('echeese_poll') . '/echeese-16x16.png', 28);
	add_submenu_page('echeese_polls_view', 'Add New', 'Add New', 'edit_posts', 'echeese_new_poll', new_poll);
	add_options_page('eCheese polls', 'eCheese polls', 'manage_options', 'echeese_polls_options', 'manage_options');
}

function load_into_head() {
	?>
	<script type="text/javascript" src="<?php echo plugins_url('echeese_poll') . '/echeese_poll.js' ?>"></script>
	<?php
}

function manage_options() {
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	global $ECHEESE_HTTP;

	// variables for the field and option names
	$hidden_field_name = 'echeese_id_hidden';

	// Read in existing option value from database
	$echeese_id = get_option( 'echeese_id' );
	$show_link = get_option( 'echeese_show_link', true );

	// See if the user has posted us some information
	// If they did, this hidden field will be set to 'Y'
	if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
		// Read their posted value
		$echeese_id = $_POST[ 'echeese_id' ];
		$show_link = $_POST[ 'show_link' ];

		update_option( 'echeese_id', $echeese_id );
		update_option( 'echeese_show_link', $show_link );

		// Put an settings updated message on the screen

		?>
		<div class="updated"><p><strong><?php _e('Settings saved.', 'menu-test' ); ?></strong></p></div>
		<?php

	}

	// Now display the settings editing screen
	echo '<div class="wrap">';
	echo "<h2>eCheese settings</h2>";

	?>

<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<table width="100%">
	<tr>
		<td>eCheese ID</td>
		<td><input type="text" name="echeese_id" value="<?php echo $echeese_id; ?>" size="20"></td>
		<td><a href="<?php echo $ECHEESE_HTTP; ?>/create_id/1" target="_blank">Create an ID on eCheese</a></td>
	</tr>
	<tr>
		<td>Correlation link</td>
		<td colspan="2"><input type="checkbox" name="show_link" <?php if ($show_link) { echo "checked"; } ?> >show eCheese.net correlation link in widget</a></td>
	</tr>
</table>
<hr />

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>

</form>
</div>

<?php


}

function check_perms_and_conf() {
	if (!current_user_can('edit_posts')) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	global $ECHEESE_HTTP;
	$echeese_id = get_option( 'echeese_id' );

	if(! isset($echeese_id) or $echeese_id == '') {
		?>
		<div class="wrap">
		<div><img class="icon32" src="<?php echo plugins_url('echeese_poll') . '/echeese-32x32.png'; ?>" /></div>
		<h2>eCheese polls</h2>
		<hr/>
		You need to configure your eCheese polls in the <a href="options-general.php?page=echeese_polls_options">Settings page</a>
		before you can create a poll.
		</div>
		<?php
		return false;
	}
	return true;
}

function view_polls() {
	if(!check_perms_and_conf())
		return;

	global $ECHEESE_HTTP;
	$polls_json = file_get_contents($ECHEESE_HTTP . "/embedded_get_polls/1/" . get_option('echeese_id') );
	$polls = json_decode($polls_json);

	?>
	<div class="wrap">
	<div><img class="icon32" src="<?php echo plugins_url('echeese_poll') . '/echeese-32x32.png'; ?>" /></div>
	<table>
		<tr><td><h2>eCheese polls</h2></td>
		<td>
			<a href="admin.php?page=echeese_new_poll" class="button h2-new">Add New</a>
			</form>
		</td>
	</table>

	<div class="wrap">
		<table class="widefat post fixed" cellspacing="0">
			<thead>
				<tr>
					<th scope="col" width="50%" class="manage-column">Poll title</th>
					<th scope="col" class="manage-column">Voters</th>
					<th scope="col" class="manage-column">CSV</th>
					<th scope="col" class="manage-column">Delete</th>
				</tr>
			</thead>
			<tbody>
	<?php
		foreach($polls->{'polls'} as $poll) {
	?>
	<tr>
		<td><a href="<?php echo $ECHEESE_HTTP; ?>/poll_view?no=<?php echo $poll->{"id"}; ?>&token=<?php echo get_option( 'echeese_id' ); ?>" target="_blank"><?php echo $poll->{"title"}; ?></a></td>
		<td><?php echo $poll->{"votes"}; ?></td>
		<td><a href="<?php echo $ECHEESE_HTTP; ?>/embedded_csv_download/1/<?php echo get_option( 'echeese_id' ); ?>?no=<?php echo $poll->{"id"}; ?>">Download</a></td>
		<td><a href="<?php echo $ECHEESE_HTTP; ?>/embedded_delete/1/<?php echo get_option( 'echeese_id' ); ?>?no=<?php echo $poll->{"id"}; ?>">Delete</a></td>
	</tr>
	<?php
		}

	?>
			<tfoot>
				<tr>
					<th scope="col" class="manage-column">Poll title</th>
					<th scope="col" class="manage-column">Voters</th>
					<th scope="col" class="manage-column">CSV</th>
					<th scope="col" class="manage-column">Delete</th>
				</tr>
			</tfoot>
		</table>
		Use the <a href="widgets.php">eCheese widget</a> to select where to display your poll.
	</div>
	<?php
}

function new_poll() {
	if(!check_perms_and_conf())
		return;

	global $ECHEESE_HTTP;

	?>
	<div class="wrap">
	<div><img class="icon32" src="<?php echo plugins_url('echeese_poll') . '/echeese-32x32.png'; ?>" /></div>
	<h2>Add New Poll</h2>
	<form method="post" onsubmit="return checkNewPoll()" action="<?php echo $ECHEESE_HTTP; ?>/embedded_new_poll/1/<?php echo get_option('echeese_id'); ?>">
		<h3 class='hndle'>Title</h3>
		<input type="text" name="title" size="30" value="" id="title_id" autocomplete="off" />
		<h3 class='hndle'>Choices</h3>
		<ul id="poll_choice_list">
			<li><input type="text" name="choice1_in" size="30" value="" id="choice1_in_id" autocomplete="off" /></li>
			<li><input type="text" name="choice2_in" size="30" value="" id="choice2_in_id" autocomplete="off" /></li>
		</ul>
		<a href="javascript:poll_add_choice()" class="button-secondary" >Add choice</a>
		<a href="javascript:poll_del_choice()" class="button-secondary" >Remove choice</a>
		<br/>
		<p><label style="color:red;" id="error_id"></label></p>
		<hr/>
		<input type="submit" name="Submit" class="button-primary" value="Create" />
		<a href="javascript:history.back()" class="button-primary">Cancel</a>
	</form>

	<?php
}

/****** Widget part *******/
class eCheeseWidget extends WP_Widget {
	function eCheeseWidget() {
		parent::WP_Widget( false, $name = 'eCheese poll' );
	}

	function widget( $args, $instance ) {

		global $ECHEESE_HTTP;
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$poll_no = apply_filters( 'widget_title', $instance['selected'] );
		$poll_no = substr($poll_no, 4);
		$show_link = get_option( 'echeese_show_link', true );
		?>

		<?php
		echo $before_widget;
		?>

		<?php
			if ($title) {
		echo $before_title . $title . $after_title;
			}
		?>

		<div class="poll">
			<iframe frameborder="0" align="center" border="0" src="<?php echo $ECHEESE_HTTP; ?>/embedded_view?no=<?php echo $poll_no; ?>&width=200&height=200&show_link=<?php echo $show_link; ?>" width="200" height="200"></iframe>
		</div>

		 <?php
			 echo $after_widget;
		 ?>
		 <?php
	}

	function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	function form( $instance ) {
		global $ECHEESE_HTTP;
		$title = esc_attr( $instance['title'] );
		$selected = esc_attr( $instance['selected'] );
		$polls_json = file_get_contents($ECHEESE_HTTP . "/embedded_get_polls/1/" . get_option('echeese_id') );
		$polls = json_decode($polls_json);
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
			</label><br/>
			<label for="<?php echo $this->get_field_id( 'poll_select' ); ?>"><?php _e( 'Poll:' ); ?>
			<select class="widefat" id="<?php echo $this->get_field_id( 'selected' ); ?>" name="<?php echo $this->get_field_name( 'selected' ); ?>">
		<?php
		if($selected == "-1")
			echo "<option value='-1' selected='selected'>Select a poll</option>";
		else
			echo "<option value='-1'>Select a poll</option>";

		foreach($polls->{'polls'} as $poll) {
			$poll_id = 'poll' . $poll->{"id"};
			$poll_title = $poll->{"title"};
			$tag_id = $this->get_field_id( 'poll' . $poll_id );
			if($selected == $poll_id)
				echo "<option id='$tag_id' value='$poll_id' selected='selected'>$poll_title</option>";
			else
				echo "<option id='$tag_id' value='$poll_id' >$poll_title</option>";
		}
		?>
			</select>
		</p>
		<?php
	}
}

?>
