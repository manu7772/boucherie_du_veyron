$(document).ready(function() {

	/***************************************/
	/* initialisation DATATABLES           */
	/***************************************/
	var dataTable_language = {
		fr: {
			decimal:				",",
			processing:				"Traitement en cours...",
			search:					"Rechercher&nbsp; ",
			lengthMenu:				"Afficher _MENU_ &eacute;l&eacute;ments",
			info:					"Affichage de l'&eacute;lement _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
			infoEmpty:				"Affichage de l'&eacute;lement 0 &agrave; 0 sur 0 &eacute;l&eacute;ments",
			infoFiltered:			"(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
			infoPostFix:			"",
			loadingRecords: 		"Chargement en cours...",
			zeroRecords:			"Aucun &eacute;l&eacute;ment &agrave; afficher",
			emptyTable:				"Aucune donnée disponible dans le tableau",
			paginate: {
				first:				"Premier",
				previous:			"Pr&eacute;c&eacute;dent",
				next:				"Suivant",
				last:				"Dernier"
			},
			aria: {
				sortAscending:		": activer pour trier la colonne par ordre croissant",
				sortDescending: 	": activer pour trier la colonne par ordre décroissant"
			},
		},
	};

	// alert("Nombre de tableaux : "+$('.dataTable').length);
	$('.dataTable').each(function(index) {
		console.log("• Loading : ", "Datatables");
		// var dtJQObjId = $(this);
		$(this).DataTable({
			// responsive:			true,
			language:			dataTable_language.fr,
			stateSave:			true,
			// order: [[ 1, 'asc' ]],
			// columnDefs: [ {
			// 	targets: [2],
			// 	orderable: false,
			// 	searchable: false,
			// } ],
		});
	});

	/***************************************/
	/* initialisation FOOTABLES            */
	/***************************************/
	$('body .reset_search').hide();
	$('.footable').each(function(index) {
		console.log("• Loading : ", "Footables");
		$(this).footable();
		var parenthis = this;
		$($(this).attr('data-reset-filter')).hide();
		$($(this).attr('data-reset-filter')).on('click', function(e) {
			e.preventDefault();
			$(parenthis).data('footable-filter').clearFilter();
			$($(parenthis).attr('data-filter')).val('');
			$($(parenthis).attr('data-reset-filter')).hide();
		});
		$($(this).attr('data-filter')).on('keyup', function(e) {
			if($($(parenthis).attr('data-filter')).val() != "") $($(parenthis).attr('data-reset-filter')).show();
				else $($(parenthis).attr('data-reset-filter')).hide();
		});
	});

});