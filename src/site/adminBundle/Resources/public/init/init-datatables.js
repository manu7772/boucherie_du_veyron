$(document).ready(function() {

	/***************************************/
	/* initialisation DATATABLES           */
	/***************************************/
	// chargement dynamique des langues
	var dataTable_language = $.parseJSON($('body #hiddendata #datatables').text());

	// alert("Nombre de tableaux : "+$('.dataTable').length);
	if($('.dataTable').length) {
		var language = $("html").attr('lang');
		$('.dataTable').each(function(index) {
			// var dtJQObjId = $(this);
			$(this).DataTable({
				responsive:			true,
				language:			dataTable_language,
				stateSave:			true,
			});
		});
	}

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