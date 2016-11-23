// $(document).ready(function() {

// 	var errorMessages = new Array();
// 	var hiddenBalise = 'hiddendata';

// 	// environnement (dev/prod/test)environnementMode
// 	if($('#'+hiddenBalise+' #environnementMode').length) {
// 		var environnementMode = $('#'+hiddenBalise+' #environnementMode').text();
// 		$('body').data('environnementMode', environnementMode);
// 	} else {
// 		alert("Erreur majeure : environnement non défini.");
// 		errorMessages.push("Erreur : environnement non défini.");
// 	}

// 	var hiddendata = [
// 		"typeMessages",			// types de messages/alertes
// 		"defaultsMessages"		// paramètres par défaut de messages/alertes
// 	];
// 	for(var data in hiddendata) {
// 		var balise = "#" + hiddenBalise + " #" + data;
// 		if($(balise).length) {
// 			hdata = $(balise).text();
// 			// alert(hdata + "\n" + data + " : " + $(balise).length);
// 			$('body').data(data, $.parseJSON(hdata));
// 			if(environnementMode == "prod") $('body').remove(balise);
// 		} else {
// 			errorMessages.push("Erreur : paramètres par défaut de " + data + " non trouvés.");
// 		}
// 	};


// 	// var stripslashes = function(str) {
// 	// 	return (str + '').replace(/\\(.?)/g, function (s, n1) {
// 	// 		switch (n1) {
// 	// 			case '\\':
// 	// 				return '\\';
// 	// 			case '0':
// 	// 				return '\u0000';
// 	// 			case '':
// 	// 				return '';
// 	// 			default:
// 	// 				return n1;
// 	// 			}
// 	// 	});
// 	// }

// 	// effacement de la balise complète en mode "prod"
// 	// if(environnementMode == "dev") alert("Environnement : " + environnementMode);
// 	// if(environnementMode == "prod") $('body').remove('#'+hiddenBalise);

// 	if($("#" + hiddenBalise + ' .flashMessage').length) {
// 		var defaultsMessages 	= $('body').data("defaultsMessages");
// 		var typeMessages 		= $('body').data("typeMessages");
// 		$("#" + hiddenBalise + ' .flashMessage').each(function() {
// 			this.messagedata = $.parseJSON($(this).text());
// 			var parent = this;
// 			if(parent.messagedata != undefined) setTimeout(function() {
// 				// console.log('toastr : ', '?????');
// 				mess = parent.messagedata;
// 				mess.type == undefined ? type = typeMessages[0] : type = mess.type;
// 				mess.title == undefined ? title = null : title = mess.title;
// 				mess.text == undefined ? text = "…" : text = mess.text;
// 				mess.showMethod == undefined ? showMethod = defaultsMessages['showMethod'] : showMethod = mess.showMethod;
// 				mess.hideMethod == undefined ? hideMethod = defaultsMessages['hideMethod'] : hideMethod = mess.hideMethod;
// 				mess.showEasing == undefined ? showEasing = defaultsMessages['showEasing'] : showEasing = mess.showEasing;
// 				mess.hideEasing == undefined ? hideEasing = defaultsMessages['hideEasing'] : hideEasing = mess.hideEasing;
// 				mess.showDuration == undefined ? showDuration = defaultsMessages['showDuration'] : showDuration = mess.showDuration;
// 				mess.hideDuration == undefined ? hideDuration = defaultsMessages['hideDuration'] : hideDuration = mess.hideDuration;
// 				mess.positionClass == undefined ? positionClass = defaultsMessages['positionClass'] : positionClass = mess.positionClass;
// 				mess.progressBar == undefined ? progressBar = defaultsMessages['progressBar'] : progressBar = mess.progressBar;
// 				mess.debug == undefined ? debug = defaultsMessages['debug'] : debug = mess.debug;
// 				mess.timeOut == undefined ? timeOut = defaultsMessages['timeOut'] : timeOut = mess.timeOut;
// 				mess.extendedTimeOut == undefined ? extendedTimeOut = defaultsMessages['extendedTimeOut'] : extendedTimeOut = mess.extendedTimeOut;
// 				(mess.closeButton == undefined) || (timeOut > 9999) ? closeButton = true : closeButton = mess.closeButton;
// 				mess.closeHtml == undefined ? closeHtml = defaultsMessages['closeHtml'] : closeHtml = mess.closeHtml;
// 				mess.newestOnTop == undefined ? newestOnTop = defaultsMessages['newestOnTop'] : newestOnTop = mess.newestOnTop;
// 				toastr.options = {
// 					showMethod: 		showMethod,
// 					hideMethod: 		hideMethod,
// 					showEasing: 		showEasing,
// 					hideEasing: 		hideEasing,
// 					showDuration: 		showDuration,
// 					hideDuration: 		hideDuration,
// 					positionClass: 		positionClass,
// 					closeButton: 		closeButton,
// 					progressBar: 		progressBar,
// 					debug: 				debug,
// 					timeOut: 			timeOut,
// 					extendedTimeOut: 	extendedTimeOut,
// 					closeButton: 		closeButton,
// 					closeHtml: 			closeHtml,
// 					newestOnTop:		newestOnTop,
// 					// onClick: 			function() { alert('Click !!!'); }
// 					// onHidden: 			function() { alert('fermé !!!'); },
// 				};
// 				if(debug == false || (debug == true && environnementMode == 'dev'))
// 					toastr[type](unescape(text), unescape(title));
// 			}, 500);
// 		});
// 	}

// 	// errorMessages.push("Chargement [" + environnementMode + "] complet de data-et-messages.js !");
// 	// Affichage des erreurs en mode DEV
// 	var points = "• ";
// 	if(errorMessages.length > 0 && environnementMode != 'prod') {
// 		alert(points + errorMessages.join("\n" + points));
// 	}

// });