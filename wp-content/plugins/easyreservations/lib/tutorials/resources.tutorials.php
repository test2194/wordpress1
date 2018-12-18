<?php
	function easyreservations_resources_tutorial() {
		$handler = array('#wpbody-content > h2','#add-new-h2', '#post-view', 'img[name="copylink"]:first', 'a.resource-link:first');
		$content = array(
				'<h3>Resources</h3><p>Resources represent the goods your guest can reserve. They can be rooms, bungalows, scooters, tours, events or even humans.  Multiple interchangeable goods should be consolidated in one resource with the respective quantity set. Each reservation must be attached to a resource, but cannot have multiple.</p>',
				'<h3>Add resource</h3><p>Click here to add a new resource.<p>',
				'<h3>Post view</h3><p>Technically resources are custom post types. All post related option can be found here.</p>',
				'<h3>Copy resource</h3><p>You can also copy the resources settings and filters to minimize your work.</p>',
				'<h3>Edit resource</h3><p>Click here to edit the resource.</p>',
		);
		$at = array('', 'top', 'top', 'right', 'top');
		$execute = array('', '', '', '', 'window.location = jQuery(\'a.resource-link:first\').attr(\'href\');');
		echo easyreservations_execute_pointer(5, $handler, $content, $at, $execute);
	}