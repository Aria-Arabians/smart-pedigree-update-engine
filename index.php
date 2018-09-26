<?php

/*
Plugin Name: AHW Smart Pedigree Update Engine
*/

function pedigree_update_engine($array) {

	// Prepare a WP Query so we can check if $array['horse name'] already exists
	$args = array(
    	'post_type'   => 'horse',
    	'numberposts' => -1,
    	'post_status' => array('publish', 'pending', 'draft')
    	);

    $post_array = get_posts($args);

    // Run through the provided array, tracking the current node and associated horse name
    foreach ($array as $node => $horse) {

    	// By default, we assume $horse is new
    	$horse_post_exists = false;

    	foreach ($post_array as $curr_post) {

    		// If $horse exists...
    		if ( addslashes(strtoupper($curr_post->post_title)) == strtoupper($horse) ) {

    			// Update our variable...
    			$horse_post_exists = true;

    			// Then cycle through the horse array again and update $horse's pedigree tree
	            foreach ($array as $inner_node => $inner_horse) {

	                // Make sure $inner_node contains $node but is not equal to $node (IE is a child on the binary tree)
	                if ( substr($inner_node, 0, strlen($node)) == $node && $inner_node !== $node ) {

	                    // Then $slice $node from $inner_node
	                    // (makes $node the root)...
	                    $slice = substr($inner_node, strlen($node));

	                    // and assign $inner_horse to $post_meta['$slice']
	                    // (drop $inner_horse into it's rightful place on the tree)
	                    //update_post_meta($curr_post->ID, strtolower($slice), $inner_horse);  
	                }
	            }

    		}
    	}
            
        // If the current horse doesn't exist (and $horse has a meaningful value)
        if ( $horse_post_exists == false && !empty($horse) ) {

            // Pack the post basics into an array...
            $new_post = array(

                'post_title'  => wp_strip_all_tags($horse),

                'post_type'   => 'horse',

                'post_status' =>'draft'

            );

            // ...then create the post, saving its generated id
            remove_action( 'save_post', 'save_horse_image');
            $new_horse_id = wp_insert_post( $new_post );
            add_action( 'save_post', 'save_horse_image');

            if ( !empty($new_horse_id) ) {

	            // Cycle through the horse array again
	            foreach ($array as $inner_node => $inner_horse) {

	                // Make sure $inner_node contains $node but is not equal to $node (IE is a child on the binary tree)
	                if ( substr($inner_node, 0, strlen($node)) == $node && $inner_node !== $node ) {

	                    // Then $slice $node from $inner_node...
	                    $slice = substr($inner_node, strlen($node));

	                    // and assign $inner_node to $post_meta['$slice']
	                    //update_post_meta($new_horse_id, strtolower($slice), $inner_horse);  
	                }
	            }
	        }

        }

    }

}

// THE BUG: Pedigree updates work when other posts to be updated exists deeper in the node tree. If NOT, the function fails (since it hinges on slicing node depths). This is all still fuzzy, but I need a way to determine where a horse exists in the node tree in both directions (deeper/shallower) so I can update in both directions. That'll take some cleverness, but it'll be awesome when it works.

// Tree only walks right (deeper) into the tree. It needs to be able to walk left (shallower). Basically, do the opposite of the current slicing: Instead of removing node levels, add them? I think? Something along those lines. Test the bounds of the feature, figure out exactly where it fails. Cover those cases.
?>