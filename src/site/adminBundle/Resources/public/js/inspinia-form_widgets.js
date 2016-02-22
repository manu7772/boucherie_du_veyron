jQuery(document).ready(function($) {


	/***************************************/
	/* initialisation ICHECK/IRADIO        */
	/***************************************/
	$('.icheckbox, .iradio').each(function() {
		$(this).iCheck({
			checkboxClass: 'icheckbox_square-green',
			radioClass: 'iradio_square-green',
			// increaseArea: '20%' // optional
		});
	});

	/***************************************/
	/* initialisation DATEPICKER           */
	/***************************************/
	$('.input-group.date').datepicker({
		todayBtn: "linked",
		keyboardNavigation: false,
		forceParse: false,
		calendarWeeks: true,
		autoclose: true,
		format: $('#formFormatDate').text(),
	});

	/***************************************/
	/* initialisation COLORPICKER          */
	/***************************************/
	$('.colorpickers').colorpicker({
		// options…
	});

	/***************************************/
	/* initialisation RICHTEXT             */
	/***************************************/
	/* initialisation RICHTEXT */
	$('.richtexts').summernote({
		// options…
		lang: 'fr',
		// airMode: true,
		toolbar: [
				//[groupname, [button list]]
				["style", ["style"]],
				["font", ["bold", "italic", "underline", "clear"]],
				// ["fontname", ["fontname"]],
				["color", ["color"]],
				["para", ["ul", "ol", "paragraph"]],
				// ["height", ["height"]],
				// ["table", ["table"]],
				["insert", ["link", "picture"]],
				// ["view", ["fullscreen", "codeview"]],
				["help", ["help"]]
			]
	});

	/***************************************/
	/* initialisation POPOVER              */
	/***************************************/
	$('body [data-toggle="popover"]').popover({
		// options…
	});

	/***************************************/
	/* initialisation TOOLTIP              */
	/***************************************/
	$('body [data-toggle="tooltip"]').tooltip({
		container: 'body',
		delay: { "show": 500, "hide": 100 },
	});

	/***************************************/
	/* fonctionnalités data-mask           */
	/***************************************/
	$('body .input-group.mask >input[class^=mask-]').each(function (e) {
		var parenthis = this;
		var parent = $(this).parent();
		var index = e;
		var addon = $(parent).find('> .input-group-addon').first();

		if($(this).hasClass('mask-money')) {
			$(parent).data('format', "# ##0,00");
			$(parent).data('params', {'placeholder': "0,00", 'reverse': "true", 'data-mask-clearifnotmatch': "true"});
		} else if($(this).hasClass('mask-tel')) {
			$(parent).data('format', "00 00 00 00 00");
			$(parent).data('params', {'placeholder': "Téléphone", 'data-mask-clearifnotmatch': "true"});
		} else if($(this).hasClass('mask-cp')) {
			$(parent).data('format', "00000");
			$(parent).data('params', {'placeholder': "Code postal", 'data-mask-clearifnotmatch': "true"});
		} else if($(this).hasClass('mask-dept')) {
			$(parent).data('format', "00");
			$(parent).data('params', {'placeholder': "Département", 'data-mask-clearifnotmatch': "true"});
		} else if($(this).hasClass('mask-siret')) {
			$(parent).data('format', "000000000-00000");
			$(parent).data('params', {'placeholder': "Siret (9+5 chiffres)", 'data-mask-clearifnotmatch': "true"});
		} else if($(this).hasClass('mask-siren')) {
			$(parent).data('format', "000000000");
			$(parent).data('params', {'placeholder': "Siren (9 chiffres)", 'data-mask-clearifnotmatch': "true"});
		} else return false;
		// $(addon).data('data-mask-index', index);
		// alert(index);
		// $(this).attr('title', 'Index : '+index);
		$(this).mask($(parent).data('format'), $(parent).data('params'));
		$(parent).data('data-mask-switch', true);
		// évènements
		$(addon).on('click', function (e) {
			// alert($(e).attr('class')+"/"+$(e).attr('id'));
			if($(parent).data('data-mask-switch') == false) {
				$(parent).data('data-mask-switch', true);
				$(this, '>i.fa').removeClass('text-danger');
				$(parenthis).mask($(parent).data('format'), $(parent).data('params'));
			} else {
				$(parent).data('data-mask-switch', false);
				$(this, '>i.fa').addClass('text-danger');
				$(parenthis).unmask().attr('placeholder', null);
			}
		});
	});


	/***************************************/
	/* Sortable / Nested                   */
	/***************************************/

	if($('.master-nestable-menu').length) {
		// https://github.com/dbushell/Nestable/issues/77
		// https://github.com/dbushell/Nestable
		// http://dbushell.github.io/Nestable/
		$('.master-nestable-menu').each(function (e) {
			var idnest = $(this).attr('id');
			console.log("New nestable : ", idnest);

			var infoRoles = $.parseJSON($('[data-info-roles]', this).first().attr('data-info-roles'));
			var pwebs = $.parseJSON($('[data-info-pagewebs]', this).first().attr('data-info-pagewebs'));
			var messages = $.parseJSON($('[data-info-messages]', this).first().attr('data-info-messages'));
			// console.log("Roles : ", window.JSON.stringify(infoRoles));
			// console.log("Pagewebs : ", window.JSON.stringify(pwebs));
			console.log("Messages : ", window.JSON.stringify(messages));
			var $parent = $(this);
			var bundle = $(this).attr('data-bundle');
			var name = $(this).attr('data-name');
			var maxDepth = parseInt($(this).attr('data-maxDepth'));
			var url = $(this).attr('data-url');

			$('div.dd3-content', this).first().css('background-color', "#ddd;");

			$(this).nestable({
				group: idnest,
				maxDepth: maxDepth,
			}).on('change', function() {
				$.ajax({
					url: url,
					method: "POST",
					data: {tree: $parent.nestable('serialize')},
				});
			});

			// get data of item
			var getDataOfItem = function(idnest, id) {
				var $target = $('div#' + idnest + ' li#li-' + idnest + "-" + id);
				data = $target.attr('data-item');
				return $.parseJSON(data);
			}

			// set data of item
			var setDataOfItem = function(idnest, id, champ, data) {
				var $target = $('div#' + idnest + ' li#li-' + idnest + "-" + id);
				// change id
				switch(champ) {
					case 'name':
						$('.dd3-content > span.text', $target).first().text(data.name);
						if(messages.hasOwnProperty(data.name) == true) data.name = messages[data.name];
						break;
					case 'pageweb':
						$('.dd3-content .pageweb', $target).first().text(pwebs[data.path.params.pageweb]);
						break;
					case 'role':
						$('.dd3-content > span > small', $target).first().text(infoRoles[data.role]);
						break;
					case 'icon':
						$elem = $('.dd3-content > i.fa', $target).first();
						icon = $elem.attr('data-fa');
						$elem.removeClass(icon);
						$elem.attr('data-fa', data.icon);
						$elem.addClass(data.icon);
						break;
				}
				$target.attr('data-item', window.JSON.stringify(data));
			}

			// change name
			$('body').on('change', 'input.name', function (e) {
				var idnest = $(this).attr('data-idnest');
				var id = $(this).attr('data-id');
				var data = getDataOfItem(idnest, id);
				data.name = $(this).val();
				setDataOfItem(idnest, id, 'name', data);
				$.ajax({
					url: url,
					method: "POST",
					data: {tree: $parent.nestable('serialize')},
				}).complete(function() {
					// console.log('Back change name : \n', window.JSON.stringify(data));
				});
			});

			// change pageweb
			// $('body').on('change', 'select.pageweb', function() { alert($(this).val() + " = " + $(this).text()); });
			$('body').on('change', 'select.pageweb', function (e) {
				$(this).trigger("chosen:updated");
				var idnest = $(this).attr('data-idnest');
				var id = $(this).attr('data-id');
				var data = getDataOfItem(idnest, id);
				data.path.params.pageweb = $(this).val();
				// var newpageweb = $("select.pageweb").first().children("option").filter(":selected").text();
				setDataOfItem(idnest, id, 'pageweb', data);
				$.ajax({
					url: url,
					method: "POST",
					data: {tree: $parent.nestable('serialize')},
				}).complete(function() {
					// console.log('Back change pageweb : ' + newpageweb);
				});
			});

			// change role
			// $('body').on('change', 'select.role', function() { alert($(this).val() + " = " + $(this).text()); });
			$('body').on('change', 'select.role', function (e) {
				$(this).trigger("chosen:updated");
				var idnest = $(this).attr('data-idnest');
				var id = $(this).attr('data-id');
				var data = getDataOfItem(idnest, id);
				data.role = $(this).val();
				// var newrole = $("select.role").first().children("option").filter(":selected").text();
				setDataOfItem(idnest, id, 'role', data);
				$.ajax({
					url: url,
					method: "POST",
					data: {tree: $parent.nestable('serialize')},
				}).complete(function() {
					// console.log('Back change role : ' + newrole);
				});
			});

			// change icon
			$('body').on('click', 'button.icon', function (e) {
				$('button.icon', $(this).parent()).removeClass('btn-primary').addClass('btn-white');
				$(this).toggleClass('btn-white').toggleClass('btn-primary');
				var icon = $('>i.fa', this).first().attr('data-fa');
				var $big = $('div.well >i.fa', $(this).parent().parent()).first();
				var oldicon = $big.attr('data-fa');
				$big.removeClass(oldicon).addClass(icon).attr('data-fa', icon);
				var idnest = $(this).attr('data-idnest');
				var id = $(this).attr('data-id');
				var data = getDataOfItem(idnest, id);
				data.icon = icon;
				setDataOfItem(idnest, id, 'icon', data);
				$.ajax({
					url: url,
					method: "POST",
					data: {tree: $parent.nestable('serialize')},
				}).complete(function() {
					// console.log('Back change role : ' + newrole);
				});
			});


		});


		/**
		 * Actions sur nestables
		 */
		$('body').on('click', '.nest-menu button', function (e) {
			switch($(this).attr('data-action')) {
				case 'add':
					idnest = $(this).attr('data-idnest');
					alert('Ajouter un nouvel item ' + idnest);
					$newil = $($('<ol class="dd-list bis">')).prepend($('<li class="dd-item bis">').prepend($('<div class="dd-handle bis">')));
					$(idnest).prepend($newil);
					break;
				default:
					$($(this).attr('data-idnest')).nestable($(this).attr('data-action'));
			}
		});

		// icons
		$('.menu-icons-item').hide();
		$('.menu-icons-item').first().show();
		// params
		$('.menu-params-item').hide();
		$('.menu-params-item').first().show();
		$('body').on('mouseenter', 'div.dd div.dd3-content', function (e) {
			var target = $(this).first().attr('data-toggle');
			var master = "#"+$(this).first().attr('data-id');
			$(master + ' div.dd3-content').css('background-color', "#fff;");
			$(this).css('background-color', "#ddd;");
			$('.menu-params-item').hide();
			$('.menu-icons-item').hide();
			$(target+'-param').show();
			$(target+'-icon').show();
		});

		// http://refreshless.com/nouislider/
		$(".depthSlider").each(function() {
			var $parent = $(this);
			var majtarget = $($(this).attr('data-maj-target'));
			var idnest = $(this).attr('data-idnest');
			noUiSlider.create($(this).get(0), {
				start: parseInt($(this).attr('data-steps')),
				behaviour: 'tap',
				connect: 'upper',
				animate: true,
				// tooltips: true,
				step: 1,
				range: {
					'min': 1,
					'max': 6,
				},
				direction: 'ltr',
				pips: { // Show a scale with the slider
					mode: 'steps',
					density: 20,
				},
			}).on('change', function() {
				var parent = this;
				var value = parseInt(this.get());
				parent.set(value);
				majtarget.text(value);
				var url = $parent.attr('data-url').replace('%23%23%23value%23%23%23', value);
				$.ajax({
					url: url,
					method: "GET",
				}).complete(function(data) {
					// data = $.parseJSON(data);
					// data = window.JSON.stringify(data);
					// alert(data.responseJSON.value);
					newvalue = parseInt(data.responseJSON.value);
					parent.set(newvalue);
					majtarget.text(newvalue);
					// actualisation…
					$("#"+idnest).nestable({
						group: idnest,
						maxDepth: newvalue,
					}).trigger('change');
				});
			});
		});

	}



	/***************************************/
	/* initialisation CHOSEN               */
	/***************************************/
	var config = {
		'.chosen-select'           	: {},
		'.chosen-select-deselect'  	: {allow_single_deselect:true},
		'.chosen-select-no-single' 	: {disable_search_threshold:5},
		'.chosen-select-no-results'	: {no_results_text:'Aucun résultat…'},
		'.chosen-select-width'     	: {width:"95%"},
		'.chosen-select-max1'      	: {max_selected_options:1},
		'.chosen-required'			: {allow_single_deselect:false},
	}
	for (var selector in config) {
		$(selector).chosen(config[selector]);
	}

	$('.chosen-multi-max1').chosen({
		allow_single_deselect:		true,
		disable_search_threshold:	5,
		no_results_text:			'Aucun résultat…',
		width:						"95%",
		max_selected_options:		1
	});

	$('body .chosen-container').css({width: '100%'});
	// $('select').chosen({width: '100%'});
	// $('body').on('click', 'a[data-toggle="tab"]', function(elem) {
	// 	// cible = $(this).attr('href');
	// 	// alert(cible);
	// 	$('body ' + $(this).attr('href') + ' .chosen-container').css({width: '100%'});
	// });

	/***************************************/
	/* initialisation MULTIFORM            */
	/***************************************/
	$("[id^=multi-form_].multi-form").each(function(elem) {
		var elem = elem;
		var colorActive = "success";
		var basename = $(this).attr('id').replace(/^multi-form_/gi, '');
		var name = basename+"_"+elem;
		var tablename = "table_"+name;
		var basedelete = "delete_"+basename+"_";
		var deletename = basedelete+elem;
		// prototype du tableau
		var $tab_prototype = $($(this).attr('data-table-prototype')).attr('id', tablename);
		// conteneur ul du formulaire imbriqué
		var $ul_list_form = $('#'+$(this).attr('id')+' > ul.multi-form-ul').first();
		// colonnes à afficher dans le tableau
		// var cols = [0, 1, 2, 3];
		var exp = new RegExp("[ ,;:|]+", "g");
		var cols = $ul_list_form.attr('data-columns').split(exp);
		// index débutant après la dernière ligne présente (pour edit)
		var formIndex = $ul_list_form.find(' > li').length;
		// prototype du formulaire imbriqué
		var multi_form_prototype = $ul_list_form.attr("data-prototype");
		// bouton(s) d'ajout d'une ligne de formulaire
		var $addMultiFormLink = $('.add_multiform_link[data-target=#'+$(this).attr('id')+']');

		/**
		 * Renvoie le texte label d'une colonne du prototype (commence par 0)
		 * @return string
		 */
		var getLabel_InPrototype = function(col) {
			if(col == undefined) { alert("Erreur programme : col doit être défini !"); return false; }
			var col = col;
			return $(multi_form_prototype).find('.form-group:eq('+col+') > label').first().text().replace('*', '');
		}

		/**
		 * Initialise et affiche l'en-tête du tableau
		 */
		var initiateHeader = function() {
			var deleteColumn = "<th></th>";
			$tab_prototype.find('> thead > tr').append(deleteColumn);
			for (var i = 0; i <= cols.length - 1; i++) {
				$tab_prototype.find('> thead > tr').append("<th class='ellipsis'>"+getLabel_InPrototype(cols[i])+"</th>");
			}
		}

		/**
		 * Vide les lignes du tableau
		 */
		var emptyArrayBody = function() {
			$tab_prototype.find(' > tbody').first().empty();
		}

		/**
		 * Renvoie la valeur de la colonne d'un index d'un sous-formulaire (index et col commencent par 0). 
		 * Renvoie "" si rien n'est trouvé
		 * @param integer index
		 * @param integer col
		 * @param boolean onlyOneIfSelect
		 * @return sting
		 */
		var getTextValue = function(index, col, onlyOneIfSelect) {
			if(index == undefined) { alert("Erreur programme : index doit être défini !"); return false; }
			var index = index;
			if(col == undefined) { alert("Erreur programme : col doit être défini !"); return false; }
			var col = col;
			if(onlyOneIfSelect == undefined) onlyOneIfSelect = false;
			var onlyOneIfSelect = onlyOneIfSelect;
			var reponse = undefined;
			var balise = $ul_list_form.find(" > li:eq("+index+") > div .form-group:eq("+col+") input").first();
			if(balise.length) {
				// INPUT
				if($(balise).attr('type') == 'text') {
					// text
					reponse = $(balise).val();
				}
			}
			if(reponse == undefined) {
				// pas d'input => on recherche un SELECT
				balise = $ul_list_form.find(" > li:eq("+index+") > div .form-group:eq("+col+") select").first();
				if(balise.length) {
					// si onlyOneIfSelect == true
					if(onlyOneIfSelect == true) reponse = $(balise).find("option:selected").first().text();
						else {
							var selects = $(balise).find("option:selected");
							if(selects.length > 0) {
								var texts = new Array();
								for (var i = 0; i <= selects.length - 1; i++) {
									texts.push($(selects[i]).text());
								};
								reponse = texts.join('<br>');
							}
						}
				}
			}
			if(reponse == undefined) reponse = "";
			return reponse;
		}

		/**
		 * Renvoie une ligne html pour le tableau (avec "<td>") (index commence par 0). 
		 * @param integer index
		 * @return sting
		 */
		var getTdLineForArray = function(index) {
			if(index == undefined) { alert("Erreur programme : index doit être défini !"); return false; }
			var index = index;
			var buffer = "";
			for (var i = 0; i <= cols.length - 1; i++) {
				buffer += "<td class='change_item in_cols ellipsis' style='cursor:pointer;'>"+getTextValue(index, cols[i])+"</td>";
			};
			return buffer;
		}

		var fillArray = function() {
			var deleteColumn = "";
			var $tbody = $tab_prototype.find(' > tbody').first();
			// lignes du formulaire
			var items = $ul_list_form.find(' > li');
			// vide le tableau… au cas où…
			emptyArrayBody();
			if(items.length > 0) {
				// puis le remplit…
				var highlight = "";
				for (var i = 0; i <= items.length - 1; i++) {
					deleteColumn = "<td class='text-center delete_column' data-confirm-title='Confirmation' data-confirm-message='Souhaitez-vous supprimer cette ligne ?' title='Supprimer cette ligne' style='width: 36px;'><button id='"+deletename+"_"+i+"' class='btn btn-xs btn-danger btn-outline' data-item='"+i+"'><i class='fa fa-times'></i></button></td>";
					if($(items[i]).css('display') != 'none') highlight = " "+colorActive;
						else highlight = "";
					$tbody.append("<tr class='ellipsis"+highlight+"'>"+deleteColumn+getTdLineForArray(i)+"</tr>")
				}
			}
		}

		/**
		* Renvoie le nombre de sous-formulaires
		* @return integer
		*/
		var getNumerOfSubforms = function() {
			 return $ul_list_form.find(' > li').length;
		 }

		 var getIndexOfActiveItem = function() {
			return $("#"+tablename+" > tbody > tr."+colorActive).index("#"+tablename+" > tbody > tr");
		 }

		var showSubformItem = function(item) {
			$ul_list_form.find(' > li').hide();
			if(item == undefined || item >= (getNumerOfSubforms()) || item < 0 || item == 'first') {
				// Affiche le premier élément…
				$ul_list_form.find(' > li').first().show();
			} else if(item == 'last') {
				// Affiche le nouvel (dernier, donc) élément…
				$ul_list_form.find(' > li').last().show();
			} else {
				// item précis, on l'affiche…
				$ul_list_form.find(' > li:eq('+item+')').show();
			}
			fillArray();
		}

		var addTagForm = function() {
			$newLi = $('<li></li>').append(multi_form_prototype.replace(/__name__/gi, formIndex++));
			$ul_list_form.append($newLi);
			showSubformItem('last');
			// fillArray();
		}

		var deleteItem = function(item) {
			var active = false;
			if(item == getIndexOfActiveItem()) active = true;
			// efface le formulaire
			$ul_list_form.find(' > li:eq('+item+')').remove();
			// puis rafraîchit le tableau
			if(active) showSubformItem(item - 1);
				else fillArray();
		}

		/**
		 * Renvoie l'index et la colonne de l'élément (dans le formulaire)
		 * @param object elem
		 * @return Array
		 */
		var getIndexAndCol_inForm = function(elem) {
			if(elem == undefined) { alert("Erreur programme : elem doit être défini !"); return false; }
			var elem = elem;
			// index
			var li = $(elem).closest('li');
			var liparent = $(li).parent();
			var index = $(li).index('#'+$(liparent).attr('id')+' > li');
			// col
			var div = $(elem).closest('.form-group');
			var divparent = $(div).parent();
			var col = $(div).index('#'+$(divparent).attr('id')+' > .form-group');
			return {'index': index, 'col': col};
		}

		/**
		 * Renvoie l'index et la colonne de l'élément (dans le tableau)
		 * Attention : ne tient compte que des colonnes renseignées dans cols (par exemple, la colonne delete n'est pas prise en compte)
		 * @param object elem
		 * @return Array
		 */
		var getIndex_inTab = function(elem) {
			if(elem == undefined) { alert("Erreur programme : elem doit être défini !"); return false; }
			var elem = elem;
			// index
			var tr = $(elem).closest('tr');
			var index = $(tr).index('#'+tablename+' > tbody > tr');
			// col
			var td = $(elem).closest('td');
			var col = $(td).index('#'+tablename+' > tbody > tr > td.in_cols');
			// var col = $(div).index('#'+$(divparent).attr('id')+' > .form-group');
			return {'index': index, 'col': col};
		}


		// mise en place du table header
		$(this).prepend($tab_prototype);
		// colonnes du tableau
		initiateHeader();
		showSubformItem();
		// fillArray();

		// changement de sous-formulaire en cliquant sur une ligne du tableau
		$tab_prototype.on('click', 'tbody > tr > td.change_item', function (e) {
			// alert($(this).index());
			showSubformItem($(this).parent().index());
		});

		// modification dans l'un des sous-formulaires
		// INPUT
		$ul_list_form.on('keyup', 'input', function (e) {
			data = getIndexAndCol_inForm(this);
			if(cols.indexOf(data.col+"") != -1) fillArray();
		});
		// SELECT
		$ul_list_form.on('change', 'select', function (e) {
			data = getIndexAndCol_inForm(this);
			if(cols.indexOf(data.col+"") != -1) fillArray();
		});

		// Ajout d'une ligne
		$addMultiFormLink.on('click', function (e) {
			e.preventDefault();
			addTagForm();
		});

		// Suppression d'une ligne
		$('body').on('click', '[id^='+deletename+']', function (e) {
			e.preventDefault();
			var data = getIndex_inTab(this);
			// modale de confirmation
			var title = $(this).attr('data-confirm-title');
			if(title == undefined) title = 'Confirmation';
			var message = $(this).attr('data-confirm-message');
			if(message == undefined) message = 'Confirmez cette action, s.v.p.';
			var reponse = confirm(title+"\n"+message);
			if(reponse) deleteItem(data.index);
		});
	});



});