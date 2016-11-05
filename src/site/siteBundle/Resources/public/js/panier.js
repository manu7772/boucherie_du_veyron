jQuery(document).ready(function($) {

	// $('.commande-cmd').each(function (item) {
		//
	// });

	var getPanierData = function (item) {
		var parent;
		if($(item).hasClass('commande-cmd')) parent = item;
			else parent = $(item).closest('.commande-cmd');
		if($(parent).data('panierData') == undefined) {
			if(parent == undefined) return false;
			var data = $.parseJSON($(parent).attr('data-article'));
			if(data != undefined) {
				data['targets'] = {
					quantity: $('input.command_quantity', parent),
				};
				if(data.minquantity == null) data.minquantity = 1;
				if(data.defaultquantity == null) data.defaultquantity = 1;
				// save unit
				var value = data.targets.quantity.val();
				if(value == undefined) return false;
				var values = value.split(' ');
				data.unit = values[1];
				$(parent).data('panierData', data);
				$(parent).attr('data-article', null);
			} else return false;
		}
		return $(parent).data('panierData');
	};

	var controleValues = function (data) {
		var value = data.targets.quantity.val();
		if(value == undefined) return false;
		if(value == undefined || value == null) return [data.defaultquantity, data.unit];
		var values = value.split(' ');
		if(values.length == 0) return [data.defaultquantity, data.unit];
		if(values[0] == undefined || values[0] == NaN || values[0] == null) values[0] = data.defaultquantity;
		var neg = false;
		if(values[0][0] == '-') neg = true;
		values[0] = Math.floor(parseFloat(values[0].replace(new RegExp("[^(0-9\.,)]", "g"), '')));
		if(neg == true) values[0] = 0 - values[0];
		if(values[0] < 1) values[0] = 1;
		return [values[0], data.unit];
	};

	var panierChange = function (data, value) {
		var values = controleValues(data);
		data.targets.quantity.val((parseInt(values[0]) + value)+' '+data.unit);
		controlePanier(data);
	};

	var controlePanier = function (data) {
		var values = controleValues(data);
		// console.log(values);
		newvalue = values[0];
		if(data.maxquantity != null)
			if(values[0] > data.maxquantity) newvalue = data.maxquantity;
		if(data.minquantity != null)
			if(values[0] < data.minquantity) newvalue = data.minquantity;
		if(values[0] < 1) newvalue = 1;
		data.targets.quantity.val(newvalue+' '+data.unit);
	};

	$('body').on('click', '.commande-cmd span.btn-quantity a.quantity-top', function (item) {
		data = getPanierData(this);
		if(data != false) {
			// console.log('Panier data + : ', data);
			panierChange(data, data.increment);
		} else {
			console.log('Error panier !');
		}
	});

	$('body').on('click', '.commande-cmd span.btn-quantity a.quantity-bottom', function (item) {
		data = getPanierData(this);
		if(data != false) {
			// console.log('Panier data - : ', data);
			newval = 0 - data.increment;
			panierChange(data, newval);
		} else {
			console.log('Error panier !');
		}
	});

	$('body').on('change', '.commande-cmd input.command_quantity', function (item) {
		data = getPanierData(this);
		if(data != false) {
			controlePanier(data);
		} else {
			console.log('Error panier !');
		}
	});

	$('body').on('click', '.commande-cmd .btn-commander', function (item) {
		alert('Pour le moment, vous ne pouvez pas commander.\nLe site sera bientôt prêt pour la commande en ligne.');
	});

	$('.commande-cmd').each(function (item) {
		var data = getPanierData(this);
		// console.log('Prepare panier for '+data.id, data);
	});

});









