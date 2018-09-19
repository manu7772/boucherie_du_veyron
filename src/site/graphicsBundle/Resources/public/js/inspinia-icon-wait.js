// $(document).ready(function() {

// 	// CLICKS //
// 	// BALISE <a>
// 	// @LINK https://docs.google.com/document/d/1o8-w0ccGAgqXR63BmU5dodvn7pIJk44umtgo25erZgY/edit#heading=h.rhr4yv3oh36p

// 	var environment = $('body').data('environnementMode');
// 	if(environment != 'prod') console.log('• Loading : ', 'icon-wait-on-click ('+environment+')');

// 	var disableAllIconWait = function() {
// 		$('body .fa-spin').each(function() {
// 			$(this).removeClass('fa-spin');
// 			// if($(this).data('oldIcon') != undefined) {
// 				$(this).removeClass().addClass($(this).data('oldIcon'));
// 			// }
// 		});
// 	}

// 	var tempErrorIcon = function() {
// 		$('body .fa-spin').each(function() {
// 			var oldIcon = $(this).attr('class').toString();
// 			if($(this).data('oldIcon') != undefined) oldIcon = $(this).data('oldIcon');
// 			// $(this).removeClass('fa-spin');
// 			$(this).removeClass().addClass('fa fa-warning').delay(3000).removeClass().addClass($(this).data('oldIcon'));
// 		});
// 	}

// 	$('body').on('click', "a, button, [type='submit']", function(event) {
// 		// icon wait on click
// 		$(this).find('.icon-wait-on-click').each(function() {
// 			$turningIcon = $(this).attr('data-icon-wait');
// 			if($turningIcon == undefined) $turningIcon = 'fa-refresh';
// 			// annule les autres actions si existantes
// 			disableAllIconWait();
// 			// mémorise ancien icone
// 			$(this).data('oldIcon', $(this).attr('class').toString());
// 			if($turningIcon != '_self') {
// 				$(this).removeClass().addClass('fa '+$turningIcon+' fa-spin');
// 			} else {
// 				$(this).addClass('fa-spin');
// 			}
// 		});
// 	});

// 	$('body').on('click', '.cancel-all-icon-wait-on-click', function(event) {
// 		disableAllIconWait();
// 	})
// 	// $(document).ajaxStop(function() { disableAllIconWait(); });
// 	// $(document).ajaxComplete(function() { disableAllIconWait(); });
// 	$(document).ajaxSuccess(function() { disableAllIconWait(); });
// 	$(document).ajaxError(function() { tempErrorIcon(); });

// });