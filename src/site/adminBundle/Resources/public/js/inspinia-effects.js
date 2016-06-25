$(document).ready(function() {

	// masquage automatique d'éléments (balises HTML)
	// donner la classe "hideauto" à la balise à masquer
	// et préciser dans data-hide les valeurs séparées par - _ , ; ou espace
	// param 1 : délai en ms
	// param 2 : vitesse en ms
	// param 3 : nom de l'effet : fade, blind, bounce, clip, drop, explode, fold, highlight, puff, pulsate, scale, shake, size, slide
	var hideauto = function(elem) {
		var effets = new Array("fade", "blind", "bounce", "clip", "drop", "explode", "fold", "highlight", "puff", "pulsate", "scale", "shake", "size", "slide");
		var parametres = $(elem).attr("data-hide");
		if(parametres === undefined) parametres = "3000 300 test";
		// alert(parametres);
		var reg = new RegExp("[ ,;_-]+", "g");
		param = parametres.split(reg);
		var timeHideAuto = parseInt(param[0]);	// délai
		var speedHideAuto = parseInt(param[1]);	// vitesse
		var effetHideAuto = param[2];			// effet
		// valeurs par défaut si non valides
		if(isNaN(timeHideAuto) || timeHideAuto < 1 || timeHideAuto === undefined) timeHideAuto = 3000;
		if(isNaN(speedHideAuto) || speedHideAuto < 1 || speedHideAuto === undefined) speedHideAuto = 300;
		var test = false;
		for(var count = 0; count < effets.length; count++) {
			if(effets[count] == effetHideAuto) test = true;
		}
		if(test == false) effetHideAuto = effets[0];
		var options = {};
		if ( effetHideAuto === "scale" ) {
			options = { percent: 0 };
		} else if ( effetHideAuto === "size") {
			options = { to: { width: 200, height: 60 } };
		}
		// alert("Délai : " + timeHideAuto + "\nVitesse : " + speedHideAuto + "\nEffet : " + effetHideAuto);
		if(effetHideAuto === "fade") {
			$(elem).delay(timeHideAuto).fadeOut(speedHideAuto);
		} else {
			$(elem).delay(timeHideAuto).hide(effetHideAuto, options, speedHideAuto);
		}
	}
	$(".hideauto").each(function() { hideauto(this); });


	var parseArrayOfItems = function(items) {
	    for (var i = 0; i < items.length; i++) {
	    	items[i] = items[i].split('_');
	    };
	    return items;
	}

	// sort list (JQuery UI)
	$('.sortlist').each(function(item) {
		var element = this;
		var select = "> *:not(.sortable-disabled)";
		var reg = new RegExp("^label-");
		var classe = null;
		var classes = $(select, $(element)).first().attr('class').split(" ");
		for(var i = classes.length - 1; i >= 0; i--) if(reg.test(classes[i])) classe = classes[i];
		$(this).sortable({
			items: select,
			stop: function(event, ui) {
				var $widget = $(element).sortable('widget');
				var URL = $widget.attr('data-url');
				var data = {};
				data.entity = $widget.attr('data-parent').split('_');
				data.group = $widget.attr('data-group');
				data.children = parseArrayOfItems($widget.sortable('toArray'));
				console.log('Sorting url : ', URL);
				console.log('Sorting data : ', data);
				$.ajax({
					method: "POST",
					dataType: "json",
					url: URL,
					data: data,
					context: document.body,
					success: function(returndata) {
						console.log('Return data : ', returndata);
						if(classe != null) {
							$(select, $(element)).removeClass(classe);
							$(select, $(element)).first().addClass(classe);
						}
					},
					error: function(error) {
					    alert("Error "+error.status+" : "+error.responseText);
					},
				}).always(function() {
					$(element).sortable('refresh');
					$(element).sortable('refreshPositions');
				});
			},
		});
	});



});