jQuery(function($) {
	$('#logichop_goal_ga_cb').change(function () {
		$('#logichop_ga_event_fields, #logichop_ga_page_fields, .logichop_goal_ga_meta').slideUp();
		if ( $(this).val() == 'event' ) {
			$('#logichop_ga_event_fields, .logichop_goal_ga_meta').slideDown();
		} else if ( $(this).val() == 'page' ) {
			$('#logichop_ga_page_fields, .logichop_goal_ga_meta').slideDown();
		}
	})

	$('.logichop_google_analytics_clear').click(function (e) {
		$('.logichop_ga_event').val('');
		e.preventDefault();
	});
});
