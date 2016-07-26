jQuery(document).ready(function($) {

	/*
	
	BONTONS de CONTROLE :
	- classe "switch-adminhelp"

	UTILISATION :
	- ajouter la classe "adminhelp" à toutes les balises à cliquer
	- ajouter la classe "switch-text-adminhelp" à toutes les balises à inverser (on/off)

	PARAMÈTRES :
	data-txt-swh-seton = texte pour ON
	data-txt-swh-setoff = texte pour OFF

	*/
	var hiddenBalise = 'hiddendata';
	var environnementMode = $('#'+hiddenBalise+' #environnementMode').text();
	$('body').data('environnementMode', environnementMode);

	var adminhelp = function() {

		if($('.switch-adminhelp').length) {

			var swOn = 'swh-on';
			var swOff = 'swh-off';
			var defaultSw = swOn; // default state for adminhelp
			var globalState = null;
			var firstFix = true;
			var ajaxOn = '';
			var ajaxOff = '';
			// if($('body').data('environnementMode') != 'prod') console.log('Adminhelp '+$('body').data('environnementMode'), 'on / '+$('.switch-adminhelp').length+" control(s).");

			var show_helps = function () {
				// show helps
				$('body .adminhelp').slideDown('slow');
				// $('body .adminhelp').show('slow');
				$('.switch-text-adminhelp').each(function() {
					$(this).removeClass('text-muted').addClass('text-info');
				});
				$('.switch-adminhelp').each(function() {
					textOff = $(this).attr('data-txt-swh-setoff');
					if(textOff == undefined) textOff = "Off";
					target = $(this).attr('data-txt-target');
					if(target == undefined) $(this).text(textOff);
						else $(target).text(textOff);
					$(this).removeClass(swOff).addClass(swOn);
				});
			}

			var hide_helps = function () {
				// hide helps
				$('body .adminhelp').slideUp('slow');
				// $('body .adminhelp').hide('slow');
				$('.switch-text-adminhelp').each(function() {
					$(this).removeClass('text-info').addClass('text-muted');
				});
				$('.switch-adminhelp').each(function() {
					textOn = $(this).attr('data-txt-swh-seton');
					if(textOn == undefined) textOn = "On";
					target = $(this).attr('data-txt-target');
					if(target == undefined) $(this).text(textOn);
						else $(target).text(textOn);
					$(this).removeClass(swOn).addClass(swOff);
				});
			}

			var adminhelp_Switch = function (state) {
				if(state == undefined) { if(defaultSw == swOn) globalState = true; else globalState = false; }
					else globalState = state && true;
				if(globalState == true) {
					// set adminhelp ON
					if(!firstFix) $.ajax({
						url: ajaxOn,
					})
					.fail(function(backdata) {
						alert(backdata.message);
					})
					.always(function(backdata) {
						backdata = $.parseJSON(backdata);
						// console.log("Data ajax return", backdata);
						if(backdata.result == true) show_helps();
					});
					else show_helps();
				} else {
					if(!firstFix) $.ajax({
						url: ajaxOff,
					})
					.fail(function(backdata) {
						alert(backdata.message);
					})
					.always(function(backdata) {
						backdata = $.parseJSON(backdata);
						// console.log("Data ajax return", backdata);
						if(backdata.result == true) hide_helps();
					});
					else hide_helps();
				}
				firstFix = false;
				return globalState;
			}

			// Initialization
			$('.switch-adminhelp').each(function (item) {
				// URLs
				ajaxOn = $(this).attr('data-url-on');
				ajaxOff = $(this).attr('data-url-off');
			});
			$('.switch-adminhelp:first').each(function (item) {
				if(!$(this).hasClass(swOn) && !$(this).hasClass(swOff)) $(this).addClass(defaultSw);
				console.log('• Loading : Adminhelp ('+swOn+'/'+swOff+') ', $(this).attr('class'));
				adminhelp_Switch($(this).hasClass(swOn));
			});

			// Click
			$('body').on('click', '.switch-adminhelp', function() {
				if(globalState != null) {
					adminhelp_Switch($(this).hasClass(swOff));
				}
			});

		} else {
			// if($('body').data('environnementMode') != 'prod') console.log('Adminhelp '+$('body').data('environnementMode'), 'off / no control found.');
		}
	}

	adminhelp();

});