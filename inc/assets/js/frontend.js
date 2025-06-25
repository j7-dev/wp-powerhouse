(function ($) {
	$('body').on('click', '.pc-btn-disabled', function (e) {
		e.preventDefault();
		e.stopPropagation();
	});
})(jQuery);