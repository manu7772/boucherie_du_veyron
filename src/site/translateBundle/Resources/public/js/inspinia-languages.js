jQuery(document).ready(function($) {

	// Changement de langue sur le site
	$("body").on('click', '.change-user-language', function(e) {
		//
		e.preventDefault();
		var parenthis = this;
		$.ajax({
			url: $(this).attr('data-url'),
		}).always(function(data) {
			$(location).attr('href', $(parenthis).attr('href'));
		});
	});

	// Actions sur formulaire traductions

	$('body').on('click', '.trad_form_action', function() {
		cible = $(this).attr('data-form-href');
		value = $(this).attr('data-data');
		$(cible).val(value);
	});

	$('body').on('click', '.trad_all_action', function() {
		Mcible = $(this).attr('data-href');
		action = $(this).attr('data-action');
		langue = $(this).attr('data-langue');
		if(action == 'delete_all') {
			$(Mcible+" .trad_form_action").each(function() {
				cible = $(this).attr('data-form-href');
				$(cible).val('');
			});
		} else {
			$(Mcible+" .trad_form_action.mark_"+langue).each(function() {
				cible = $(this).attr('data-form-href');
				value = $(this).attr('data-data');
				anc_value = $(cible).val();
				if(anc_value == '' || action == 'replace') {
					$(cible).val(value);
				}
			});
		}
	});

	// $( "input[type='text']" ).change(function() {
	// 	alert($(this).val());
	// });
	// Auto trad - choix de la langue
	$('body').on('click', '.autotrad', function() {
		lang = $(this).attr('data-auto-choice');
		langue = $(this).text();
		$('body #autotrad-lang').attr('data-auto-lang', lang);
		$('body #autotrad-lang').text(langue);
	});
	// Auto trad - launch
	$('body').on('click', '.autotrad-launch', function() {
		var parenthis = this;
		this.origin = $('body #autotrad-lang').attr('data-auto-lang');
		this.destlg = $('body #autotrad-lang').attr('data-trad-to');
		this.cible = $(this).attr('data-form-href');
		this.texte = $(this.cible).val();
		if(this.texte != '') {
			$(">i.fa", this).first().removeClass('text-danger').addClass('fa-spin text-info');
			this.ATURL = "http://mymemory.translated.net/api/get?de=manu7772@gmail.com&q="+this.texte+"&langpair="+this.origin+"|"+this.destlg+"&key="+"1a18c921a520860b0e2c";
			// alert(this.ATURL);
			$.getJSON(this.ATURL, function(data) {
				if(data['responseStatus'] == 200) {
					// retour OK
					$(parenthis.cible).val(data['responseData']['translatedText']);
					$(parenthis).attr('title', (parseFloat(data['responseData']['match']) * 100) + '%');
					$(">i.fa", parenthis).first().removeClass('fa-spin text-info');
					// alert("Réception JSon :\n"+parenthis.ATURL+'\nAller : '+parenthis.texte+'\nRetour : '+data['responseData']['translatedText']);
				} else {
					// Erreur
					alert('Erreur ('+data['responseStatus']+') en réponse du serveur :\n'+data['responseDetails']);
					$(">i.fa", parenthis).first().removeClass('fa-spin text-info').addClass('text-danger');
				}
			});
		} else {
			// alert('Champ vide : '+this.texte);
		}
	});


});