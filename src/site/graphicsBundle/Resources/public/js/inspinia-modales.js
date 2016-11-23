// $(document).ready(function() {

// 	/* initialisation Modales */
// 	// Récupération, suppression du code HTML
// 	if($("#modale_model").length) {
// 		var $newModale = $("#modale_model").clone();
// 	} else {
// 		if($('body').data('environnementMode') != 'prod') alert('Modèle de modale absent.');
// 	}
// 	// event
// 	$("body").on('click', "a.need-confirmation, button.need-confirmation", function(e) {
// 		e.preventDefault();
// 		if($newModale.length) {
// 			// alert("Confirmation requise…");
// 			$copyModale = $newModale.clone();
// 			title = $(this).attr('data-title');
// 			if(title == undefined) title = $(this).attr('title');
// 			message = $(this).attr('data-message');
// 			if($(this).attr('data-href') != undefined)
// 				href = $(this).attr('data-href');
// 				else href = $(this).attr('href');
// 			$("#modal-title", $copyModale).text(title);
// 			$("#modal-message", $copyModale).text(message);
// 			$("#modal-href", $copyModale).attr('href', href);
// 			// modale
// 			$copyModale.modal();
// 		} else {
// 			// pas de modale présente, on utilise une confirm javascript
// 			title = $(this).attr('data-title');
// 			message = $(this).attr('data-message');
// 			result = confirm(title + '\n' + message);
// 			if(result) {
// 				dontPrevent = true;
// 				// $(this).submit();
// 			}
// 		}
// 	});

// 	var dontPrevent = false;

// 	$('body').on('submit', 'form.need-confirmation', function(e) {
// 		var parentForModal = this;
// 		// method = $(this).find("[name='_method']").first().attr('value');
// 		if(dontPrevent == false /* && method == 'DELETE' */) {
// 			e.preventDefault();
// 			if($newModale.length) {
// 				// modale présente…
// 				$copyModale = $newModale.clone();
// 				title = $(this).attr('data-title');
// 				if(title == undefined) title = $(this).attr('title');
// 				message = $(this).attr('data-message');
// 				// if($(this).attr('data-href') != undefined)
// 					// href = $(this).attr('data-href');
// 					// else href = $(this).attr('href');
// 				$("#modal-title", $copyModale).text(title);
// 				$("#modal-message", $copyModale).text(message);
// 				// modale				
// 				$copyModale.modal();
// 				$copyModale.on('click', "#modal-href", function(e) {
// 					dontPrevent = true;
// 					parentForModal.submit();
// 				});
// 			} else {
// 				// pas de modale présente, on utilise une confirm javascript
// 				title = $(this).attr('data-title');
// 				message = $(this).attr('data-message');
// 				result = confirm(title + '\n' + message);
// 				if(result) {
// 					dontPrevent = true;
// 					parentForModal.submit();
// 				} else {
// 					//
// 				}
// 			}
// 		}
// 	});

// 	// var confirmSubmit = function() {

// 	// }

// });