jQuery(document).ready(function($) {


	// Gestion de l'aide en administration
	// 1 - hide/show balises de classe "adminhelp"
	$('body').on('click', '.switch-adminhelp', function() {
		if($(this).hasClass('swh-on')) {
			// adminhelp is on
			// $('body .adminhelp').fadeOut('slow');
			$('body .adminhelp').slideUp('slow');
			$('.switch-text-adminhelp').each(function(item) {
				$(this).removeClass('text-info').addClass('text-muted');
			});
			$('.switch-adminhelp').text('Activer l\'aide');
			$('.switch-adminhelp').removeClass('swh-on').addClass('swh-off');
		} else {
			// adminhelp is off
			// $('body .adminhelp').fadeIn('slow');
			$('body .adminhelp').slideDown('slow');
			$('.switch-text-adminhelp').each(function(item) {
				$(this).removeClass('text-muted').addClass('text-info');
			});
			$('.switch-adminhelp').text('Désactiver l\'aide');
			$('.switch-adminhelp').removeClass('swh-off').addClass('swh-on');
		}
	});


});