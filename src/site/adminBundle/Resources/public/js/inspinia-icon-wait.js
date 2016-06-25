$(document).ready(function() {

	// CLICKS //
	// BALISE <a>
	// @LINK https://docs.google.com/document/d/1o8-w0ccGAgqXR63BmU5dodvn7pIJk44umtgo25erZgY/edit#heading=h.rhr4yv3oh36p

	// console.log("• Loading : ", "icon-wait-on-click");

	var faturning = 'fa-refresh';

	var disableAllIconWait = function() {
		$('body .fa-spin').each(function() {
			$(this).removeClass('fa-spin');
			if($(this).data('icon_wait') != undefined) {
				$(this)
					.removeClass($(this).data('icon_wait').turningIcon)
					.addClass($(this).data('icon_wait').oldIcon);
			}
		});
	}

	var searchIconInClasses = function(classes) {
		var reg = new RegExp("^(fa-)","g");
		for (var i = classes.length - 1; i >= 0; i--) {
			if(reg.test(classes[i])) return classes[i];
		};
		return 'fa-question';
	}

	$('body').on('click', "a, button, [type='submit']", function(event) {
		// icon wait on click
		if($(this).attr('disabled') == undefined) {
			$(this).find('.icon-wait-on-click').each(function() {
				if($(this).data('icon_wait') == undefined) {
					var turningIcon = $(this).attr('data-icon-wait');
					if(turningIcon == undefined) turningIcon = faturning;
					if(turningIcon == '_self') turningIcon = oldIcon;
					// annule les autres actions si existantes
					disableAllIconWait();
					var classes = $(this).attr('class').split(' ');
					var oldIcon = searchIconInClasses(classes);
					console.log("Icon wait : ", oldIcon);
					// mémorise ancien icone
					var icon_wait = {};
					icon_wait.oldIcon = searchIconInClasses(classes);
					icon_wait.turningIcon = turningIcon;
					$(this).data('icon_wait', icon_wait);
				}
				$(this)
					.removeClass($(this).data('icon_wait').oldIcon)
					.addClass($(this).data('icon_wait').turningIcon)
					.addClass('fa-spin');
			});
		} else {
			event.preventDefault()
			disableAllIconWait();
			return false;
		}
	});

	$('body').on('click', '.cancel-all-icon-wait-on-click', function(event) {
		disableAllIconWait();
	})

	// $('body').on('click', '[disabled]', function(e) {
	// 	e.preventDefault();
	// 	// alert('STOP !');
	// 	disableAllIconWait();
	// 	return false;
	// });


});