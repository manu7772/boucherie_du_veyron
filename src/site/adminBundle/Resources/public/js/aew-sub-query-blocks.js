compteur = 0;
cptdata = 0;

$(document).ready(function() {

	// Toutes les balises de classe 'sqblock'
	// data-sqblock-url			= url à appeler
	// data-sqblock-target		= balises à mettre à jour
	// data-sqblock-data		= data à afficher (ex. : data.html, ou data.number…)
	// data-sqblock-delay		= délai avant la première mise à jour (ms)
	// data-sqblock-event		= type d'évènement déclencheur
	// data-sqblock-setTimeout	= intervale de répétition (ms)
	// data-hide-if				= masque la balise si la condition est remplie
	// data-hide-parent-if		= masque la balise parent si la condition est remplie

	console.log("• Loading : ", "Sub-query");
	var default_sqblock_setTimeout		= 5000;
	var default_sqblock_delay			= 10;

	$('body .sqblock-wrapper > span').each(function(elem1) {
		var elem1 = elem1;
		var minTimeout = 500;
		var minDelay = 10;
		var parenthis = this;
		var data_sqblock_url 			= $(this).attr('data-sqblock-url');
		var data_sqblock_target 		= $(this).attr('data-sqblock-target');

		var data_sqblock_delay 			= $(this).attr('data-sqblock-delay');
		var data_sqblock_setTimeout		= $(this).attr('data-sqblock-setTimeout');

		var data_sqblock_data 			= $(this).attr('data-sqblock-data');
		var data_hide_if				= $(this).attr('data-hide-if');
		var data_hide_parent_if			= $(this).attr('data-hide-parent-if');
		var data_hide_target			= $(this).attr('data-hide-target');

		var data_sqblock_event			= $(this).attr('data-sqblock-event');
		var data_sqblock_event_type		= null;
		var data_sqblock_event_target	= null;

		// tests et vérifications :

		// event instead of timeout
		if(data_sqblock_event != undefined) {
			var sqb_event = data_sqblock_event.split('|');
			if(sqb_event.length == 2) {
				data_sqblock_event_type = sqb_event[0];
				data_sqblock_event_target = sqb_event[1];
			} else if (sqb_event == 1) {
				data_sqblock_event_type = sqb_event[0];
				data_sqblock_event_target = data_sqblock_target;
			} else {
				// données insuffisantes
				data_sqblock_event = undefined;
			}
		}
		// timeout
		if(data_sqblock_setTimeout != undefined) {
			if(parseInt(data_sqblock_setTimeout) == NaN) data_sqblock_setTimeout = default_sqblock_setTimeout;
			data_sqblock_setTimeout = parseInt(data_sqblock_setTimeout);
			if(data_sqblock_setTimeout < minTimeout && data_sqblock_event == undefined) data_sqblock_setTimeout = minTimeout;
		}
		// delay
		if(data_sqblock_delay != undefined) {
			if(parseInt(data_sqblock_delay) == NaN) data_sqblock_delay = default_sqblock_delay;
			data_sqblock_delay = parseInt(data_sqblock_delay);
			if(data_sqblock_delay < minDelay) data_sqblock_delay = minDelay;
		}
		// autres
		if(data_sqblock_url == undefined) alert('Erreur : le paramètre "data_sqblock_url" doit être précisé !');
		if(data_sqblock_target == undefined) alert('Erreur : le paramètre "data_sqblock_target" doit être précisé !');
		if(data_sqblock_data == undefined) data_sqblock_data = 'data.html';

		/**
		 * Récupère les données nécessaire au groupe de blocks
		 */
		var data = null;
		var blocktimeout = null;
		var getData = function() {
			$.ajax({
				dataType: 'json',
				url: data_sqblock_url,
				statusCode: {
					// 500: function() { alert('Erreur programme !'); },
					// 404: function() { alert('Page introuvable !'); },
				}
			})
			.fail(function() {
				console.log("getData "+(cptdata++)+" : ", "Error");
			})
			.done(function(backdata) {
				if(backdata != null) data = backdata;
				console.log("getData "+(cptdata++)+" : ", data);
			});
			if(data_sqblock_setTimeout != undefined) {
				if(blocktimeout != null) clearTimeout(blocktimeout);
				blocktimeout = setTimeout(function() { getData(); }, data_sqblock_setTimeout);
			}
		}


		// getData() (first)
		if(data_sqblock_delay != undefined) {
			blocktimeout = setTimeout(function() { getData(); }, data_sqblock_delay);
		} else if(data_sqblock_setTimeout != undefined) {
			getData();
		}
		// events
		if(data_sqblock_event != undefined) {
			$('body').on(data_sqblock_event_type, data_sqblock_event_target, function() {
				console.log('Event : ', data_sqblock_event_type+' on '+data_sqblock_event_target);
				getData();
			});
		}

		/**
		 * Récupération des données des cibles
		 */
		$(data_sqblock_target).each(function(elem2) {
			var itemtimeout = null;
			var elem2 = elem2;
			var parenthis2 = this;
			var target_data_sqblock_delay 		= $(this).attr('data-sqblock-delay');
			var target_data_sqblock_setTimeout	= $(this).attr('data-sqblock-setTimeout');
			var target_data_sqblock_data 		= $(this).attr('data-sqblock-data');
			var target_data_sqblock_type 		= $(this).attr('data-sqblock-type');
			var target_data_hide_if				= $(this).attr('data-hide-if');
			var target_data_hide_parent_if		= $(this).attr('data-hide-parent-if');
			var target_data_hide_target			= $(this).attr('data-hide-target');
			// tests et vérifications
			if(target_data_sqblock_delay == undefined) target_data_sqblock_delay = data_sqblock_delay;
			if(target_data_sqblock_setTimeout == undefined) target_data_sqblock_setTimeout = data_sqblock_setTimeout;
				else target_data_sqblock_setTimeout = parseInt(target_data_sqblock_setTimeout);
			if(target_data_sqblock_setTimeout == NaN) target_data_sqblock_setTimeout = data_sqblock_setTimeout;
			if(target_data_sqblock_data == undefined) target_data_sqblock_data = data_sqblock_data;

			// console.log('*****************************');
			// console.log("SQBlock : ", elem1+" / "+elem2+'');
			// console.log('-----------------------------');
			// console.log('data_sqblock_url : ', data_sqblock_url);
			// console.log('data_sqblock_target : ', data_sqblock_target);
			// console.log('data_sqblock_delay : ', data_sqblock_delay);
			// console.log('data_sqblock_setTimeout : ', data_sqblock_setTimeout);
			// console.log('data_sqblock_data : ', data_sqblock_data);
			// console.log('data_hide_if : ', data_hide_if);
			// console.log('data_hide_parent_if : ', data_hide_parent_if);
			// console.log('data_hide_target : ', data_hide_target);
			// console.log('data_sqblock_event : ', data_sqblock_event);
			// console.log('data_sqblock_event_type : ', data_sqblock_event_type);
			// console.log('data_sqblock_event_target : ', data_sqblock_event_target);
			// console.log('-----------------------------');
			// console.log('target_data_sqblock_delay : ', target_data_sqblock_delay);
			// console.log('target_data_sqblock_setTimeout : ', target_data_sqblock_setTimeout);
			// console.log('target_data_sqblock_data : ', target_data_sqblock_data);
			// console.log('target_data_sqblock_type : ', target_data_sqblock_type);
			// console.log('target_data_hide_if : ', target_data_hide_if);
			// console.log('target_data_hide_parent_if : ', target_data_hide_parent_if);
			// console.log('target_data_hide_target : ', target_data_hide_target);

			var update = function() {
				if(data != null) {
					console.log("SQBlock : ", elem1+" / "+elem2+' ('+(compteur++)+') = '+target_data_sqblock_data);
					switch(target_data_sqblock_type) {
						case 'text':
							$(parenthis2).text(eval(target_data_sqblock_data));
							break;
						default:
							$(parenthis2).empty().append($(eval(target_data_sqblock_data)));
							break;
					}
					// tests hide…
					if(target_data_hide_if != undefined) {
						if(target_data_hide_target != undefined) {
							if(eval(target_data_hide_if)) $(target_data_hide_target).hide();
								else $(target_data_hide_target).show();
						} else {
							if(eval(target_data_hide_if)) $(parenthis2).hide();
								else $(parenthis2).show();
						}
					}
					if(target_data_hide_parent_if != undefined) {
						if(target_data_hide_target != undefined) {
							if(eval(target_data_hide_parent_if)) $(target_data_hide_target).hide();
								else $(target_data_hide_target).show();
						} else {
							if(eval(target_data_hide_parent_if)) $(parenthis2).parent().hide();
								else $(parenthis2).parent().show();
						}
					}
				} else {
					// console.log("SQBlock : ", "data not ready (null)"+' ('+(compteur++)+')');
				}
				// si setTimeout…
				if(target_data_sqblock_setTimeout != false) {
					itemtimeout = setTimeout(function() { update(); }, parseInt(target_data_sqblock_setTimeout));
				}
			}
			// update() (first)
			if(target_data_sqblock_delay != undefined) itemtimeout = setTimeout(function() { update(); }, parseInt(target_data_sqblock_delay));
				else update();

		});

	});

});