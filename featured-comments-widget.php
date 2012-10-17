<?php

/*
Plugin Name: Featured Comments
Description: This plugin provides a widget that let you select the comments you want to display as featured or important.
Version: 1.0
Author: Andreu Llos
Author URI: http://andreullos.com
Copyright: 2012, Andreu Llos
Text Domain: featured-comments-widget

GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>
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
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


function featured_comments_textdomain() {
	if ( function_exists('load_plugin_textdomain') ) {
		load_plugin_textdomain('featured-comments-widget', false, basename( dirname( __FILE__ ) ) . '/languages' );

	}
}
add_action( 'init', 'featured_comments_textdomain' );


class featured_comments_widget extends WP_Widget {
    //process our new widget
    function featured_comments_widget() {
        $widget_ops = array('classname' => 'widget_featured_comments', 'description' => __('Display the comments you want to feature','featured-comments-widget')); 
        $control_ops = array( 'width' => 550 );
        $this->WP_Widget('widget_featured_comments', __('Featured Comments','featured-comments-widget'), $widget_ops, $control_ops);
        
	    function get_featured_comments_short_text_words($text, $limit=50) {
	    	$excerpt = explode(' ', $text, $limit);
	    	if (count($excerpt)>=$limit) {
	    	array_pop($excerpt);
	    	$excerpt = implode(" ",$excerpt)."…";
	    	} else {
	    	$excerpt = implode(" ",$excerpt);
	    	}	
	    	$excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);
	    	return $excerpt;
	    }

    }
       
    //build our widget settings form
    function form($instance) {
        $defaults = array('title' => __('Featured Comments','featured-comments-widget'), 'count' => 5,'words' => 20); 
        $i=1; while($i<20){ $defaults['comment_'.$i] = '';  $i++; }
        $instance = wp_parse_args( (array) $instance, $defaults );
        ?>
        	<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title', 'featured-comments-widget'); ?>:</label>
        	<input type="text" name="<?php echo $this->get_field_name('title'); ?>" class="widefat" value="<?php echo esc_attr($instance['title']); ?>" /></p>

            <p><label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e('Number Of Comments To Show', 'featured-comments-widget'); ?>:</label>
        	<select class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name('count'); ?>"><? $i=0; while($i<20){ $i++; ?>
        	<option value="<? print $i; ?>"<? if($instance['count']==$i){ ?>selected="selected"<? } ?>><? print $i.' '.__('comments','featured-comments-widget'); ?></option><? } ?>
   			</select></p>
   			
   			<p><label for="<?php echo $this->get_field_id( 'words' ); ?>"><?php _e('Number Of Words Per Comment', 'featured-comments-widget'); ?>:</label>
        	<select class="widefat" id="<?php echo $this->get_field_id( 'words' ); ?>" name="<?php echo $this->get_field_name('words'); ?>"><? $i=0; while($i<200){ $i++; ?>
        	<option value="<? print $i; ?>"<? if($instance['words']==$i){ ?>selected="selected"<? } ?>><? print $i.' '.__('words','featured-comments-widget'); ?></option><? } ?>
   			</select></p>
   				    
		    <h4><?php _e('Select Featured Comments', 'featured-comments-widget'); ?></h4>
		    <ul>
		    <? $i=1; while($i<=$instance['count']){ ?>
		    <li><select style="width:390px" id="<?php echo $this->get_field_id( 'comment_'.$i ); ?>" name="<?php echo $this->get_field_name('comment_'.$i); ?>">
		    	<? $comments = get_comments('status=approve'); foreach($comments as $comment){ ?>
		    	<option value="<? print $comment->comment_ID; ?>"<? if($instance['comment_'.$i]==$comment->comment_ID){ ?>selected="selected"<? } ?>><? print get_featured_comments_short_text_words($comment->comment_content,20)." (#".$comment->comment_ID.")"; ?></option>
		    <? } ?></select></li>
		    <? $i++; } ?>
		    </ul>
		    </p>
		    
		     
        <? 
    }
 
    //save our widget settings
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['count'] = $new_instance['count'];
        $instance['words'] = $new_instance['words'];
        $instance['title'] = strip_tags($new_instance['title']);
        $i=1; while($i<20){ 
        	$instance['comment_'.$i] =  $new_instance['comment_'.$i]; 
        	$i++;
        }
        return $instance;
    }
 
    //display our widget
    function widget($args, $instance) {
        extract($args);
        $title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
        $count = empty($instance['count']) ? '' : apply_filters('widget_count', $instance['count']);
        $words = empty($instance['words']) ? '' : apply_filters('widget_words', $instance['words']);
        $i=1; while($i<20){ 
	        ${'comment_'.$i} = empty($instance['comment_'.$i]) ? '' : apply_filters('widget_comment_'.$i, $instance['comment_'.$i]);
	        $i++;
        }
        
        echo $before_widget;
		
		if ( $title )
			echo $before_title . $title . $after_title;
			
		echo '<ul>';
		
		$i=1; while($i<$count){ 
			if(${'comment_'.$i}!=''){
				$comment = get_comment(${'comment_'.$i});
				echo '<li><span class="comment-meta"><span class="author">'.$comment->comment_author.'</span> '.__('on','featured-comments-widget').' <span class="comment-title"><a href="'.get_permalink($comment->comment_post_ID).'">'.get_the_title($comment->comment_post_ID).'</a></span>:</span><blockquote>'.get_featured_comments_short_text_words($comment->comment_content,$words).'</blockquote></li>';	
			}
			$i++;
		}
		echo '</ul>';				
		echo $after_widget;
    }
}

function featured_comments_widget_init(){
	register_widget( 'featured_comments_widget' );
}

add_action('widgets_init', 'featured_comments_widget_init');


?>