(function($){
	$(document).ready(function (){

		$(document).on('click', '[data-confirm]', function (event) {
			return confirm($(this).data('confirm'));
		});

		$('[data-date-input]').datepicker();
		$('[data-datetime-input]').datetimepicker();
		$('[data-time-input]').timepicker();

		$('[data-dependent-select-loader]').hide();

		$(document).on('change', '[data-dependent-select]', function () {
			$(this).closest('form').find('[data-dependent-select-loader]').ajaxSubmit(function (payload) {
				jQuery.nette.success(payload);
				$('[data-dependent-select-loader]').hide();
			});
		});

		$(document).on('change', '[data-company-select]', function () {
			$(this).closest('form').submit();
		});
	
	});
})(window.jQuery);