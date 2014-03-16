(function($){
	$(document).ready(function (){

		$(document).on('click', '[data-confirm]', function (event) {
			return confirm($(this).data('confirm'));
		});

		bindForm();

		$(document).on('click', 'a.ajax', function (event) {
			event.preventDefault();
			$.get(this.href);
		});

		$(document).on('submit', 'form.ajax', function () {
			$(this).ajaxSubmit(function (payload) {
				jQuery.nette.success(payload);
				bindForm();
				$('[data-dependent-select-loader]').hide();
			});
			return false;
		});

		$(document).on('click', 'form.ajax :submit', function () {
			$(this).ajaxSubmit(function (payload) {
				jQuery.nette.success(payload);
				bindForm();
				$('[data-dependent-select-loader]').hide();
			});
			return false;
		});

		$('[data-dependent-select-loader]').hide();

		$(document).on('change', '[data-dependent-select]', function () {
			$(this).closest('form').find('[data-dependent-select-loader]').ajaxSubmit(function (payload) {
				jQuery.nette.success(payload);
				bindForm();
				$('[data-dependent-select-loader]').hide();
			});
		});

		$(document).on('change', '[data-company-select]', function () {
			$(this).closest('form').submit();
		});
	
	});

	function bindForm() {
		$('[data-date-input]').datepicker();
		$('[data-datetime-input]').datetimepicker();
		$('[data-time-input]').timepicker();
	}
})(window.jQuery);

