jQuery(document).ready(function($) {

	/***************************************/
	/* initialisation DATEPICKER           */
	/***************************************/
	var DATEPICKER = $('.input-group.date');
	var locale = $('#hiddendata #locale');
	if(locale !== undefined) locale = locale.text();
		else locale = 'fr';

	if(DATEPICKER.length) DATEPICKER.each(function (e) {

		// init toastr
		toastr.options = {
			"closeButton": false,
			"debug": false,
			"newestOnTop": false,
			"progressBar": false,
			"positionClass": "toast-top-full-width",
			"preventDuplicates": false,
			"onclick": null,
			"showDuration": "300",
			"hideDuration": "1000",
			"timeOut": "5000",
			"extendedTimeOut": "1000",
			"showEasing": "swing",
			"hideEasing": "linear",
			"showMethod": "slideDown",
			"hideMethod": "slideUp",
		};
		// init moment
		moment.locale(locale);

		var FORM = $(this).closest('form');
		if(FORM !== undefined) {
			var $validdate = $(FORM).find('#form_validdate');
			var $commandeready = $(FORM).find('#form_commandeready');
			var $INPUT = $('input', $(this)).first();
			var URL = $(FORM).attr('checkdate-url');
			var last_result = {data: JSON.parse($(FORM).attr('checkdate-initdata'))};
			// console.log('Datepicker init data:', last_result);
			var btnsAmPm = {
				matin: $('input#form_demijournee_0', $(FORM)),
				aprem: $('input#form_demijournee_1', $(FORM)),
			};
			var $commandeready_display = $('#commandeready_display', $(FORM));
			// commande ready display
			var checkCdeReadyDisplay = function () {
				var verboseDate = new moment($commandeready.val());
				if(verboseDate.isValid()) {
					$commandeready_display.parent().slideDown();
					$commandeready_display.parent().attr('title', verboseDate.fromNow());
					$commandeready_display.text(verboseDate.format('LLLL'));
				} else {
					$commandeready_display.parent().hide();
				}
			}
			// check buttons
			var checkButtonsAmPm = function () {
				// console.log('Datepicker check buttons am/pm:', last_result);
				// radio buttons am/pm
				if(last_result.data.am_and_pm.aprem.length > 0) {
					btnsAmPm.aprem.removeAttr('disabled');
					$commandeready.val(last_result.data.commandeready.aprem);
				} else {
					btnsAmPm.aprem.attr('disabled', 'disabled');
					btnsAmPm.matin.prop('checked', true);
				}
				if(last_result.data.am_and_pm.matin.length > 0) {
					btnsAmPm.matin.removeAttr('disabled');
					btnsAmPm.matin.prop('checked', true);
					$commandeready.val(last_result.data.commandeready.matin);
				} else {
					btnsAmPm.matin.attr('disabled', 'disabled');
					btnsAmPm.aprem.prop('checked', true);
				}
				checkCdeReadyDisplay();
			}
			checkButtonsAmPm();
			// buttonsAmPm triggers
			btnsAmPm.matin.on('change', function (e) {
				$commandeready.val(last_result.data.commandeready.matin);
				checkCdeReadyDisplay();
			});
			btnsAmPm.aprem.on('change', function (e) {
				$commandeready.val(last_result.data.commandeready.aprem);
				checkCdeReadyDisplay();
			});
			// ajax check
			var AjaxCheckData = function (date) {
				if(URL !== undefined) {
					$.when(
						$.ajax({
							method: 'post',
							url: URL,
							data: {date: date}, // string : 2016/12/31 ou 2016-12-31…
							}).fail(function (err) { console.log('Error:', err); })
					).done(function (result) {
						console.log('##################### Return data:', {result: $.extend(true, {}, result)});
						if(result.result === false) {
							// invalid date : remove it !
							if(result.message+'' !== '') toastr["error"](result.message);
							result = $.extend(true, {}, last_result);
							$INPUT.datepicker("setDate", new Date($validdate.val()));
						} else {
							// valid date : fix it !
							last_result = $.extend(true, {}, result);
							console.log('Result message:', {type: $.type(last_result.message), brut: last_result.message, trim: last_result.message.trim(), trim_length: last_result.message.trim().length});
							if(last_result.message.trim().length > 0) toastr["success"](last_result.message);							
						}
						$validdate.val($INPUT.datepicker('getDate').toString());
						checkButtonsAmPm();
					});
				}
			}
			// init datepicker
			$INPUT.datepicker({
				calendarWeeks: true,
				autoclose: true,
				showAnim: "slideDown",
				// dateFormat: 'dd/mm/yy',
				minDate: new Date($validdate.val()),
				onSelect: function (date, item) { AjaxCheckData(date); }
			});				
			$INPUT.datepicker("setDate", new Date($validdate.val()));
			$INPUT.datepicker("option", $.datepicker.regional[locale]);

			$('.input-group-addon', $(this)).on('click', function (e) {
				$INPUT.datepicker("show");
			});
		}
	});

});