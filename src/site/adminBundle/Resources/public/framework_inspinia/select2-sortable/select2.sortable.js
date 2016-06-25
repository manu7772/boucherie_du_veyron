/**
 * jQuery Select2 Sortable
 * - enable select2 to be sortable via normal select element
 * 
 * author      : Vafour
 * inspired by : jQuery Chosen Sortable (https://github.com/mrhenry/jquery-chosen-sortable)
 * License     : GPL
 */

(function($){
	$.fn.extend({
		select2SortableOrder: function(){
			var $this = this.filter('[multiple]');

			$this.each(function(){
				var $select  = $(this);

				// skip elements not select2-ed
				if(typeof($select.data('select2')) !== 'object'){
					return false;
				}
				// console.log($select);

				var $select2 = $select.siblings('.select2-container'),
				    unselected = [],
				    sorted;

				$select.find('option').each(function(){
					!this.selected && unselected.push(this);
				});

				sorted = $($select2.find('ul.select2-selection__rendered li[class!="select2-search--inline"]').map( function() {
					if (!this) return undefined;
					var id = $(this).data('data').id;
					return $select.find('option[value="' + id + '"]')[0];
				}));
				// console.log('sorted : ', sorted);

				sorted.push.apply(sorted, unselected);
				$select.children().remove();
				$select.append(sorted);
			});

			return $this;
		},
		select2Sortable: function(){
			var args         = Array.prototype.slice.call(arguments, 0);
			    $this        = this.filter('[multiple]'),
			    validMethods = ['destroy'];

			if(args.length === 0 || typeof(args[0]) === 'object') {

				var defaultOptions = {
					// bindOrder       : 'formSubmit', // or sortableStop
					bindOrder       : 'sortableStop', // or formSubmit
					sortableOptions : {
						placeholder : 'ui-state-highlight',
						items       : 'li:not(.select2-search--inline)',
						tolerance   : 'pointer'
					}
				};
				var options = $.extend(defaultOptions, args[0]);

				// Init select2 only if not already initialized to prevent select2 configuration loss
				if(typeof($this.data('select2')) !== 'object'){
					$this.select2();
				}
				var data = $this.data('select2');
				if(data != undefined) {
					// console.log('select2 id : ', data.id);
					// console.log('select2 data : ', data);
				}

				$this.each(function(){
					var $select  = $(this),
					    $select2choices = $select.next('.select2-container').first('ul.select2-selection__rendered');

					// Init jQuery UI Sortable
					$select2choices
						.sortable(options.sortableOptions)
						// .on("sortstop.select2sortable", function( event, ui ) {
						// 	var $this = $select.filter('[multiple]');
						// 	var items = $('li:not(.select2-search--inline)', $select2choices);
						// 	var test = '';
						// 	var itemId;
						// 	for (var i = 0; i < items.length; i++) {
						// 		itemId = $(items[i]).data('data').id;
						// 		test += itemId+' > ';
						// 	};
						// 	console.log('Items sort : ', test);
						// 	test = '';
						// 	var optionId;
						// 	var distant = $('select option', $select.closest('div'));
						// 	for (var i = 0; i < distant.length; i++) {
						// 		optionId = $(distant[i]).attr('value');
						// 		test += optionId+' > ';
						// 	};
						// 	console.log('Options sort : ',test);

						// })
					;


					switch(options.bindOrder){
						case 'sortableStop':
							// apply options ordering in sortstop event
							$select2choices.on("sortstop.select2sortable", function( event, ui ) {
								$select.select2SortableOrder();
							});
							$select.on('change', function(e){
								$(this).select2SortableOrder();
							});
							break;
						default:
							// apply options ordering in form submit
							$select.closest('form').unbind('submit.select2sortable').on('submit.select2sortable', function(){
								$select.select2SortableOrder();
							});
					}

				});
			}
			else if(typeof(args[0] === 'string'))
			{
				if($.inArray(args[0], validMethods) == -1)
				{
					throw "Unknown method: " + args[0];
				}
				if(args[0] === 'destroy')
				{
					$this.select2SortableDestroy();
				}
			}
			return $this;
		},
		select2SortableDestroy: function(){
			var $this = this.filter('[multiple]');
			$this.each(function(){
				var $select         = $(this),
				    $select2choices = $select.parent().find('.select2-choices');

				// unbind form submit event
				$select.closest('form').unbind('submit.select2sortable');

				// unbind sortstop event
				$select2choices.unbind("sortstop.select2sortable");

				// destroy select2Sortable
				$select2choices.sortable('destroy');
			});
			return $this;
		}
	});
}(jQuery));

