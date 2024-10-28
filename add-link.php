<?php
/*
Plugin Name: Add Link Widget
Plugin URI: http://blogs.ubc.ca/support/plugins/add-links-widget/
Description: Adds a sidebar widget to submit links to blogroll
Author: OLT UBC
Version: 1.1
Author URI: http://olt.ubc.ca
*/

/*  Copyright 2008  UBC Office of Learning Technology  - http://olt.ubc.ca
    written for the University of British Columbia
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/**
 * Add function to widgets_init that'll load our widget.
 * @since 1
 */
add_action( 'widgets_init', 'olt_add_link_load_widgets' );
add_action('admin_enqueue_scripts', 'olt_add_link_admin_js');

# TODO
/*
 Create a Shortcode
 Add Categories 
 
 */

/**
 * Register our widget.
 * 'OLT_Add_Link_Widget Widget' is the widget class used below.
 *
 * @since 1
 */
function olt_add_link_load_widgets() {
	register_widget( 'OLT_Add_Link_Widget' );
}


function olt_add_link_admin_js()
{
   wp_enqueue_script('olt-add-link', plugins_url('/add-link/add-link.js'), array('jquery'));
   wp_enqueue_style('olt-add-link',plugins_url('/add-link/add-link.css'));
}

/**
 * OLT_Add_Link_Widget Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 1
 */
class OLT_Add_Link_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function OLT_Add_Link_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'add-link', 'description' => __('A Widgets that helps you add links to your site.', 'add-link') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 400, 'id_base' => 'add-link-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'add-link-widget', __('Add Link Widget', 'add-link'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		global $current_user,$wpdb;
		extract( $args );
		
		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title_label'] );
		$message = $instance['message'];
		
		
		$link_label 		= $instance[ 'link_label' ];		
		$name_check			= $instance[ 'name_check' ];			
		$name_label			= $instance[ 'name_label' ]; 
		 
		$description_check	= $instance[ 'description_check' ];
		$description_label	= $instance[ 'description_label' ];
		
		$feed_check			= $instance[ 'feed_check' ];
		$feed_label			= $instance[ 'feed_label' ];
				
		$notes_check		= $instance[ 'notes_check' ];
		
		$notes_label		= $instance[ 'notes_label' ];
		
		
		$password_check		= $instance[ 'password_check' ] ;
		$password_label		= $instance[ 'password_label' ];
		$password			= $instance[ 'password' ];
		
		$button_label		= $instance[ 'button_label' ]; 
		
		$link_category		= $instance[ 'link_category' ]; 
		
		$permissions		= $instance[ 'permissions' ]; 
		$owner_view_check	= $instance[ 'owner_view_check' ];
		$owner_view_label	= $instance[ 'owner_view_label' ];
		$owner_delete_check	= $instance[ 'owner_delete_check' ];
		$limit 				= $instance[ 'limit' ];	
		$form_creator 		= $instance[ 'form_creator'];
		
		// PERMISSIONS 
		if($permissions == "login"):
		
			get_currentuserinfo();
			
			if(!$current_user->ID)
				return; 
		
		// permission applies only for MU 
		// if you are registed with the blogs
		endif;
		if($permissions == "registered") :
		
			get_currentuserinfo();
			
			if(!$current_user->ID)
				return; 
			
			$user_blogs = get_blogs_of_user($current_user->ID);
			
			$user_blogs_ids = array();
			foreach($user_blogs as $user_blog):
				$user_blogs_ids[] = $user_blog->userblog_id;
			endforeach;
			if(!is_array($user_blogs_ids))
				return;
			
			$current_site = wpmu_current_site();
			
			if(!in_array($current_site->blog_id, $user_blogs_ids))
				return;
			
		endif;	
		
		// check the password 
		
		
		/*
		 * ADD LINK 
		 ******************************************************/
		// making sure that the form was submited from here... 
		$nonce = $_POST[$widget_id];
		
		if($limit):
			$query = "SELECT COUNT(*) FROM ". $wpdb->links ." WHERE `link_owner` = ".$current_user->ID;
			$link_count = $wpdb->get_var($query);
			if( $limit > $link_count ):
				$procced = true;
			endif;
		else:
				$procced = true;
		endif;
		if(wp_verify_nonce($nonce, $widget_id) ):
		
			
			
			
			if($password_check):
		 		if( $_POST['add-link-widget-password'] != $password ):
		 			echo "<p class='alert'>".__('Sorry, your <em>password</em> is wrong try again.', 'add-link')."</p>";
		 			$procced = false;
		 		else:
		 			$procced = true;
		 		endif;
		 	endif;
		 	
		 	if($procced):
			/* include wordpress so that it has the wp_insert_link function ) */
			$root = preg_replace('/wp-content.*/', '', __FILE__);
			require_once($root . 'wp-config.php');
			require_once($root . 'wp-admin/includes/admin.php');
			
			


			
			// store the data into the database 
			$link[ 'link_url' ] 		= (isset($_POST['add-link-widget-link']) ? esc_url(esc_html($_POST['add-link-widget-link'])) : "");
			$link[ 'link_name' ]		= (isset($_POST['add-link-widget-name']) ? esc_html($_POST['add-link-widget-name']) : "");
			$link[ 'link_description' ] = (isset($_POST['add-link-widget-description']) ? esc_html($_POST['add-link-widget-description']) : "");
			$link[ 'link_rss' ] 		= (isset($_POST['add-link-widget-feed']) ? esc_url(esc_html($_POST['add-link-widget-feed'])) : "");
			$link[ 'link_notes' ] 		= (isset($_POST['add-link-widget-notes']) ? esc_html($_POST['add-link-widget-notes']) : "");
			$link[ 'link_owner' ] 		= (isset($current_user->ID) ? $current_user->ID : $form_creator );
			
			
			wp_insert_link( $link );
			$link_count++;
			unset($_POST);
			echo "<p class='note'>".__('Thank you for submitting the form', 'add-link')."</p>";
			
			endif; // don't add the link to the database 
		endif;
		
		
		/*
		 * DELETE LINK 
		 ******************************************************/	
		if($owner_delete_check && $_GET['add-link-action'] == "delete"):
			if(is_numeric($_GET['id'])):
				// making sure that you can only delete your own links 
				$link_owner = $wpdb->get_var($wpdb->prepare("SELECT link_owner FROM $wpdb->links  WHERE link_id = %s", $_GET['id']));
				if($link_owner == $current_user->ID):
					// last check I primiss
					if(wp_verify_nonce($_GET['nonce'], "delete_add-link_".$_GET['id'])):
						// go ahead delete link
						$root = preg_replace('/wp-content.*/', '', __FILE__);
						require_once($root . 'wp-config.php');
						require_once($root . 'wp-admin/includes/admin.php');
						
						 wp_delete_link( $_GET['id'] );
						 $link_count--;
					endif;
					
				endif;
			endif;
		endif; // link was deleted
		
			
		
		/* Before widget (defined by themes). */
		echo $before_widget;
		
		if( $limit > $link_count || !$limit):
		/* Display the widget title if one was input (before and after defined by themes). */
			if ( $title )
				echo $before_title . $title . $after_title;
		
				
		
			if($message)
				echo "<div class='add-link-widget-message'>$message</div>";
			?>
			<form action="<?php $_SERVER['REQUEST_URI']; ?>" method="post">
			
			
			<input type="hidden" value="<?php echo wp_create_nonce($widget_id); ?>" name="<?php echo $widget_id; ?>" />
			<p>
			<label for="link-<?php echo $widget_id; ?>"><?php echo $link_label; ?>:</label><br />
			<input class="input" type="text" name="add-link-widget-link" id="link-<?php echo $widget_id; ?>"  value="<?php echo esc_url(esc_html($_POST['add-link-widget-link'])); ?>"    /><br />
			</p>
			<?php
			if($name_check):
			?> <p>
				<label for="name-<?php echo $widget_id; ?>"><?php echo $name_label; ?>:</label><br />
				<input class="input" type="text" name="add-link-widget-name" id="name-<?php echo $widget_id; ?>" value="<?php  echo esc_html($_POST['add-link-widget-name']); ?>"  />
				</p>
			<?php
			endif;
			
			if($description_check):
			?>	<p>
				<label for="description-<?php echo $widget_id; ?>"><?php echo $description_label; ?>:</label><br />
				<textarea name="add-link-widget-description" id="description-<?php echo $widget_id; ?>" ><?php  echo esc_html($_POST['add-link-widget-description']); ?></textarea>
				</p>
			<?php
			endif;
			
			if($feed_check):
			?>	<p>
				<label for="feed-<?php echo $widget_id; ?>"><?php echo $feed_label; ?>:</label><br />
				<input class="input" type="text" name="add-link-widget-feed" id="feed-<?php echo $widget_id; ?>" value="<?php  echo esc_url(esc_html($_POST['add-link-widget-feed'])); ?>" />
				</p>
			<?php
			endif;
			
			if($notes_check):
			?> <p>
				<label for="notes-<?php echo $widget_id; ?>"><?php echo $notes_label; ?>:</label><br />
				<input class="input" type="text" name="add-link-widget-notes" id="notes-<?php echo $widget_id; ?>" value="<?php echo esc_html($_POST['add-link-widget-notes']); ?>" />
				</p>
			<?php
			endif;
			
			if($password_check):
			?>
				<p>
				<label for="password-<?php echo $widget_id; ?>"><?php echo $password_label; ?></label><br />
				<input class="input" type="password" name="add-link-widget-password" id="password-<?php echo $widget_id; ?>"  />
				</p>
			<?php
			endif;
			
			
			
			
			if($button_label):
				?>
				<input class="button" type="submit" value="<?php echo $button_label; ?>" />
				<?php 
			else:
				?>
				<input class="button"  type="submit" value="<?php _e('Add Link:', 'add-link'); ?>" />
				<?php 
			endif;
			?>
			</form>
			
			<?php 
		endif;
		 if($owner_view_check && $permissions != 'everyone'):
		 	echo "<div class='add-link-widget-message'>$owner_view_label</div>";
			$query = "SELECT * FROM ". $wpdb->links ." WHERE `link_owner` = ".$current_user->ID;
			$links = $wpdb->get_results($query);
			?>
			<ul class="add-links">
			<?php
			foreach ($links as $link):
			?><li><a href="<?php echo $link->link_url; ?>"><?php echo $link->link_name; ?></a>
			<?php $feed_text = __('feed', 'add-link'); 
				
			  echo ($link->link_rss ? "<a href='$link->link_rss' class='link-feed' style=\"padding: 0 0 0 19px; background: url('".plugins_url('/add-link/feed.gif')."') no-repeat 0 50%;\" >  $feed_text </a>": ""); ?>
			<?php if($owner_delete_check): ?>
								<a href="?add-link-action=delete&id=<?php echo $link->link_id ?>&nonce=<?php echo wp_create_nonce("delete_add-link_".$link->link_id); ?>" title="delete link" onclick="return confirm('<?php _e('Are you sure you want to delete?', 'add-link'); ?> \n <?php echo $link->link_url; ?>')" style="padding: 0 0 0 19px; background: url('<?php echo plugins_url('/add-link/trash.gif'); ?>') no-repeat 0 50%;"> <?php _e('delete', 'add-link'); ?></a>
	
			<?php endif; ?>	
			<?php  echo ($link->link_description ? "<p class='link-description'>$link->link_description</p>": ""); ?>
			<?php  echo ($link->link_notes ? "<p class='link-notes'>$link->link_notes</p>": ""); ?>
			
			</li>
			<?php 
			endforeach;
			?>
			</ul>
			<?php
					
		endif;
		
		
		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		global $current_user;
		$instance = $old_instance;
		
		
		$instance[ 'title_label' ] 	= strip_tags($new_instance[ 'title_label' ]);
		
		$instance[ 'message' ] 		= $new_instance[ 'message' ];
		
		$instance[ 'link_label' ] 		= strip_tags($new_instance[ 'link_label' ]);
				
		$instance[ 'name_check' ] 		= $new_instance[ 'name_check' ];
		$instance[ 'name_label' ] 		= strip_tags($new_instance[ 'name_label' ]);
		 
		$instance[ 'description_check' ] = $new_instance[ 'description_check' ];
		$instance[ 'description_label' ] = strip_tags($new_instance[ 'description_label' ]);
		
		$instance[ 'feed_check' ] 		= $new_instance[ 'feed_check' ];
		$instance[ 'feed_label' ] 		= strip_tags($new_instance[ 'feed_label']);
		
		$instance[ 'notes_check' ] 		= $new_instance[ 'notes_check' ];
		$instance[ 'notes_label' ] 		= strip_tags($new_instance[ 'notes_label' ]);
		
		
		$instance[ 'password_check' ] 	= $new_instance[ 'password_check' ];
		$instance[ 'password_label' ]	= strip_tags($new_instance[ 'password_label' ]);
		$instance[ 'password' ] 		= $new_instance[ 'password' ];
		
		$instance[ 'button_label' ] 	= strip_tags($new_instance[ 'button_label' ]);
		
		$instance[ 'link_category' ] 	= $new_instance[ 'link_category' ];
		
		$instance[ 'permissions' ] 		= $new_instance[ 'permissions' ];
		$instance[ 'owner_view_check' ] = $new_instance[ 'owner_view_check' ];
		$instance[ 'owner_view_label' ] = strip_tags($new_instance[ 'owner_view_label' ]);
		$instance[ 'owner_delete_check' ] = $new_instance[ 'owner_delete_check' ];
		
		$instance[ 'limit' ] 			=  (is_numeric($new_instance[ 'limit' ])? $new_instance[ 'limit' ] : false);
		
		$instance[ 'form_creator' ]		= $current_user->ID;
		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {
		global $current_user;
		/* Set up some default widget settings. */
		$defaults = array( 
		
		'title_label' => __('Add Link', 'add-link'),
		'message' => __('', 'add-link'),
		
		'link_label' =>__('Link URL', 'add-link'),
		
		'name_check' => false,
		'name_label' =>__('Link Name', 'add-link'),
		
		'description_check' => false,
		'description_label' =>__('Description Name', 'add-link'),
		
		'feed_check' => false,
		'feed_label' =>__('Feed URL', 'add-link'),
		
		'notes_check' => false,
		'notes_label' =>__('Your Note', 'add-link'),
		
		'password_check' => false,
		'password_label' =>__('Password', 'add-link'),
		'password' =>__('PasswOrd', 'add-link'),
		
		'button_label' =>__('Add Link', 'add-link'),
		
		'link_category' => array(),
		'permissions' => 'everyone',
		
		'owner_view_check' => false,
		'owner_view_label' =>__('Your have submited the following links', 'add-link'),
		'owner_delete_check' => false,
		'limit'=>false,
		'form_creator' => $current_user->ID,
		
		
		
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<div class="add-link">
		<p>
			<label for="<?php echo $this->get_field_id( 'title_label' ); ?>"><?php _e('Title:', 'add-link'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title_label' ); ?>" name="<?php echo $this->get_field_name( 'title_label' ); ?>" value="<?php echo $instance['title_label']; ?>" class="widefat" />
		</p>
		
		<p>
			
			<div class="add-link-help"><p><label for="<?php echo $this->get_field_id( 'message' ); ?>"><?php _e('Message:', 'add-link'); ?></label></p>
			</div>
			<textarea class="widefat" id="<?php echo $this->get_field_id( 'message' ); ?>" name="<?php echo $this->get_field_name( 'message' ); ?>" ><?php echo $instance['message']; ?></textarea>
		</p>
		
		<p>
			<strong><?php _e('Check  to create a field?', 'add-link'); ?></strong><br />
		</p>
		<?php 
		$this->helper_checbox( $instance , 'link' ,false); 
		$this->helper_checbox( $instance , 'name'); 
		$this->helper_checbox( $instance , 'description'); 
		$this->helper_checbox( $instance , 'feed'); 
		$this->helper_checbox( $instance , 'notes'); 
		$this->helper_checbox( $instance , 'password',true,"Add a password to stop unwanted users from signing up"); 
		
		?>
		
		
			<p id="add-link-password"
			<?php if( !$instance['password_check'] ): ?>
				style="display:none;" 
			<?php endif; ?>
			>
			<label class="input-label" for="<?php echo $this->get_field_id( 'password' ); ?>"><?php _e('Password: What the user is asked to enter.', 'add-link'); ?></label>
			<input id="<?php echo $this->get_field_id( 'password' ); ?>" name="<?php echo $this->get_field_name( 'password' ); ?>" value="<?php echo $instance['password']; ?>" />
			</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'button_label' ); ?>"><?php _e('Button Text:', 'add-link'); ?></label>
			<input id="<?php echo $this->get_field_id( 'button_label' ); ?>" name="<?php echo $this->get_field_name( 'button_label' ); ?>" value="<?php echo $instance['button_label']; ?>" class="widefat" />
		</p>
		 
		<!-- 
		<?php 
		
		$categories = get_terms('link_category', 'orderby=count&hide_empty=0');
 		
     	if ( is_array($categories) ) :
     	?>
		<p ><label><?php _e('Select which <strong>link categories</strong> in which the user submited links will in', 'add-link'); ?></lable>
			<ul style="max-height:100px; overflow: auto; display:block; background:#FAFAFA; padding:3px 10px; ">
		<?php 	
     		foreach ( $categories as $category ):
        		$cat_id = $category->term_id;
         		$name = esc_html( apply_filters('the_category', $category->name));
         		$checked = false;
         		if(is_array($instance['link_category']))
         			$checked = in_array( $cat_id, $instance['link_category'] );
         		
         			
         		echo '<li id="link-category-'. $cat_id. '"><label for="in-link-category-'. $cat_id. '" class="selectit"><input value="'. $cat_id. '" type="checkbox" name="link_category[]" id="in-link-category-'. $cat_id. '"'. ($checked ? ' checked="checked"' : "" ). '/> '. $name. "</label></li>";
    		endforeach;
		?>
		</ul>
		<br />
		</p>
		
		<?php endif;
			// end of 
		 ?>
		-->
		<p>
			<label for="<?php echo $this->get_field_id( 'permissions' ); ?>"><?php _e('<strong>Permissions</strong>: Who can see this widget?', 'add-link'); ?></label> 
			<select id="<?php echo $this->get_field_id( 'permissions' ); ?>" name="<?php echo $this->get_field_name( 'permissions' ); ?>" class="widefat add-link-select-input" >
				<option value="everyone" 	<?php if ( 'everyone' == $instance['permissions'] ) echo 'selected="selected"'; ?>><?php _e('Everyone', 'add-link'); ?></option>
				<option value="login" 		<?php if ( 'login' == $instance['permissions'] ) echo 'selected="selected"'; ?>><?php _e('Login users only', 'add-link'); ?></option>
				<option value="registered" 	<?php if ( 'registered' == $instance['permissions'] ) echo 'selected="selected"'; ?>><?php _e('Registered users only', 'add-link'); ?></option>
			</select>
		</p>
		<p class="add-link-user-info" style="<?php if ( 'everyone' == $instance['permissions'] ) { echo 'display:none'; } ?>">
			<input id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" value="<?php echo $instance['limit']; ?>" maxlenght="2" size="3"  /> <label class="label-clean" for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e('Number of links a user can submit', 'add-link'); ?></label>
		</p>
		<p class="add-link-user-info" style="<?php if ( 'everyone' == $instance['permissions'] ) { echo 'display:none'; } ?>">
			<strong><?php _e('Associate the link to the login user', 'add-link'); ?></strong><br />
					
			<input class="checkbox add-link-owner-check" type="checkbox" <?php checked( $instance['owner_view_check'], "on" ); ?> id="<?php echo $this->get_field_id( 'owner_view_check' ); ?>" name="<?php echo $this->get_field_name( 'owner_view_check' ); ?>" class="add-link-owner-check" /> 
			<label class="label-clean" for="<?php echo $this->get_field_id( 'owner_view_check' ); ?>"><?php _e('Display to the user the submited links', 'add-link'); ?></label>
		</p>	
		<p class="add-link-user-info add-link-user-info-indent" style=" <?php if ( 'everyone' == $instance['permissions'] || !$instance['owner_view_check'] ) { echo 'display:none'; } ?>">
		<label for="<?php echo $this->get_field_id( 'owner_view_label' ); ?>"><?php _e('Helpfull heading:', 'add-link'); ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'owner_view_label' ); ?>" name="<?php echo $this->get_field_name( 'owner_view_label' ); ?>" value="<?php echo $instance['owner_view_label']; ?>" class="widefat" />
			
		</p>
		<p class="add-link-user-info add-link-user-info-indent" style=" <?php if ( 'everyone' == $instance['permissions'] || !$instance['owner_view_check']) { echo 'display:none'; } ?> ">
			<input class="checkbox" type="checkbox" <?php checked( $instance['owner_delete_check'], "on" ); ?> id="<?php echo $this->get_field_id( 'owner_delete_check' ); ?>" name="<?php echo $this->get_field_name( 'owner_delete_check' ); ?>" /> 
			<label class="label-clean" for="<?php echo $this->get_field_id( 'owner_delete_check' ); ?>"><?php _e('Allow the user to delete their own link', 'add-link'); ?></label> <br />
			
		
		</p>

		
		</div>

	<?php
	
	
	}
	
	
	function helper_checbox($instance,$type ="name",$checkbox=true,$description=false)
	{
		?>
		<p>	
			<?php if($checkbox) :?>
			<input class="checkbox olt-add-link-chec" type="checkbox" <?php checked( $instance[$type.'_check'], "on"); ?> id="<?php echo $this->get_field_id( $type.'_check' ); ?>" name="<?php echo $this->get_field_name( $type.'_check' ); ?>" />
			<?php else: ?>
			<input class="checkbox" type="checkbox" checked="checked" disabled="disabled" /> 
			<?php endif; ?>
			<span
			<?php if(!$instance[$type.'_check'] && $checkbox): ?>
				style="display:none;" 
			<?php endif; ?>
			>
			<label class="input-label" for="<?php echo $this->get_field_id( $type.'_label' ); ?>"><?php _e( $type.' Label:', 'add-link'); ?></label>
			<input type="text" id="<?php echo $this->get_field_id( $type.'_label' ); ?>" name="<?php echo $this->get_field_name( $type.'_label' ); ?>" value="<?php echo $instance[$type.'_label']; ?>" />
			</span>
			<?php if($checkbox): ?>
			<label 
			<?php if( $instance[$type.'_check'] ): ?>
				style="display:none;" 
			<?php endif; ?>
			for="<?php echo $this->get_field_id( $type.'_check' ); ?>"><?php  _e(($description ? $description : $type), 'add-link'); ?></label>
			<?php endif; ?>
		</p>
		

		<?php
	
	}
}

?>