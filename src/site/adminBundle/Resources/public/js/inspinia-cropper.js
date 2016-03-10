$(document).ready(function(){

	var dev = true;
	// var dev = false;
	// loading
	var loading = false;
	var croppername = '_cropper';

	$('form .cropper-block').each(function(e) {

		/*******************************/
		/* VARIABLES / INITIALISATION  */
		/*******************************/

		if(dev) console.log('• Loading :', 'Cropper-image');
		// load icon
		var loadicon = $('.load-icon > i', this).first();
		// fichier en cours
		var actualFile = null;

		// ID
		var cropperId = "#"+$(this).attr('id').replace(new RegExp("("+croppername+")$", "g"), '');
		// form name
		var fieldName = $(this).attr('data-form-name');
		// URL for ajax send
		var URLrawfile = $('span.cropper-send-rawfile', this).first().attr('data-send');
		// all other cropper data
		var cropperData = $.parseJSON($('span.cropper-info', this).first().attr('data-cropper'));

		/**
		 * Initialise les données de base
		 */
		var data = {
			height: 0,
			width: 0,
			ratioIndex: 0,
			file: {
				type: null,
				name: null,
				size: null,
			},
			delete: false,
			dataType: 'cropper',
			rawfiles: {
				actual: null,
				list: new Array(),
			},
		};

		/**
		 * Met à jour les données data
		 */
		var updateBoard = function(e) {
			$(cropperId).data('infoForPersist', data);
			if(dev) console.log('infoForPersist', $(cropperId).data('infoForPersist'));
		}

		// ratio
		if(cropperData.format.length > 0) {
			var ratio = new Array();
			if(cropperData.format.x != undefined && cropperData.format.y != undefined) {
				// un seul format
				ratio.push(cropperData.format[0] / cropperData.format[1]);
			} else {
				// tableau de formats
				for (var i = 0; i < cropperData.format.length; i++) {
					if(parseInt(cropperData.ratioIndex) == i) {
						data.ratioIndex = parseInt(cropperData.ratioIndex);
						updateBoard();
					}
					if(cropperData.format[i] == null) {
						ratio.push(null);
					} else if(cropperData.format[i].length > 1) {
						ratio.push(cropperData.format[i][0] / cropperData.format[i][1]);
					}
				};
			}
		}
		if(ratio == undefined) var ratio = new Array(null);

		// fields for copy name
		var fieldForCopy = new Array();
		for(var i = 0; i < cropperData.filenameCopy.length; i++) {
			copyField = cropperId.replace(new RegExp("(_"+fieldName+")$", "g"), '_'+cropperData.filenameCopy[i]);
			if($(copyField).length) fieldForCopy.push($(copyField));
		}
		// Info for persist
		var infoForPersist = cropperId.replace(new RegExp("(_"+fieldName+")$", "g"), '_infoForPersist');
		$(cropperId).data('infoForPersist_field', infoForPersist);

		var $inputImage = $(cropperId+'_fileInput');
		var $image = $(cropperId+'_image');
		var $previews = $('.img-preview', this);
		var $cropButtons = $('.docs-buttons button[data-method]', this);
		var $contImageNotNull = $('.containerImageNull', this);
		var $suppImage = $('.noImage', this);
		var $contFile = $('.container-actions', this);


		/*******************************/
		/* DEV LOGS                    */
		/*******************************/

		if(dev) {
			console.log('cropperId :', cropperId);
			console.log('fieldName :', fieldName);
			console.log('URLrawfile :', URLrawfile);
			console.log('infoForPersist_field :', infoForPersist);
			console.log('fieldForCopy :', fieldForCopy.length);
			console.log('format :', ratio);
			console.log('Accept :', cropperData.accept);
			console.log('Init :', cropperData.init);
		}


		/*******************************/
		/* AFFICHAGES                  */
		/*******************************/

		// Deletable
		if(cropperData.deletable == undefined) {
			cropperData.deletable = false;
			$('.noImage', this).remove();
		}

		/**
		 * Copie le nom du fichier les champs désignés (cropperData.filenameCopy)
		 * @param string name
		 */
		var updateFilenameCopy = function(fields) {
			if(fields == undefined) fields = fieldForCopy;
			if(actualFile != null) var name = actualFile.name; else var name = '';
			for (var i = 0; i < fields.length; i++) fields[i].val(name);
		}

		/**
		 * Mise à jour des éléments graphiques selon la présence ou non d'une image
		 * @return boolean
		 */
		var deletevalue = null;
		var updateIfIsPicture = function() {
			var value = $image.attr('src');
			if(value == "#" || value == '') { $contImageNotNull.addClass('hidden'); if(deletevalue == null) deletevalue = false; return true; }
				else { $contImageNotNull.removeClass('hidden'); if(deletevalue == null) deletevalue = true; return false; }
		}
		// initialize…
		// updateIfIsPicture();

		/**
		 * Affichge on/off du mode "en cours de chargement"
		 * @param boolean statut
		 */
		var setLoading = function(statut) {
			if(statut == true) {
				$(loadicon).addClass('fa-spin').removeClass('fa-upload').addClass('fa-refresh');
			} else {
				$(loadicon).removeClass('fa-spin').removeClass('fa-refresh').addClass('fa-upload');
			}
			loading = statut;
		}

		/**
		 * Initialise les vues preview
		 * @param object elem
		 */
		var initPreviews = function(elem) {
			var elem = elem;
			elem.css({
				display: 'block',
				width: '100%',
				minWidth: 0,
				minHeight: 0,
				maxWidth: 'none',
				maxHeight: 'none',
			}).addClass('img-responsive');
			$previews.css({
				width: '100%',
				overflow: 'hidden',
			}).addClass('img-rounded').html(elem);
		}



		/*******************************/
		/* CROPPER                     */
		/*******************************/

		var cropperOptions = {
			aspectRatio: ratio[data.ratioIndex],
			preview: cropperId+croppername+" .img-preview",
			autoCropArea: 0.95,
			responsive: true,
			restore: true,
			viewMode: 3,
			movable: false,
			// rotatable: false,
			// scalable: false,
			// zoomable: false,
			zoomOnTouch: false,
			zoomOnWheel: false,
			wheelZoomRatio: false,
			cropBoxMovable: true,
			// cropBoxResizable: false,
			toggleDragModeOnDblclick: false,
			build: function (e) {
				initPreviews($(this).clone());
			},
			crop: function (e) {
				var imageData = $(this).cropper('imageData');
				var previewAspectRatio = e.width / e.height;
				console.log('imageData : ', imageData);
				data.width = imageData.context.naturalWidth;
				data.height = imageData.context.naturalHeight;
				updateBoard();
				$previews.each(function () {
					var $preview = $(this);
					var previewWidth = $preview.width();
					var previewHeight = previewWidth / previewAspectRatio;
					var imageScaledRatio = e.width / previewWidth;
					$preview.height(previewHeight).find('img').css({
						width: imageData.context.naturalWidth / imageScaledRatio,
						height: imageData.context.naturalHeight / imageScaledRatio,
						marginLeft: -e.x / imageScaledRatio,
						marginTop: -e.y / imageScaledRatio
					});
				});
			},
		}

		$contImageNotNull.removeClass('hidden');
		$image.cropper(cropperOptions).one('built.cropper', function () {
			if(cropperData.init != undefined && cropperData.init != null) {
				// injecte les dimensions de la cropbox si réédition d'une image déjà enregistrée
				$image.cropper('setData', cropperData.init);
			}
			updateIfIsPicture();
		});

		// Import image
		var URL = window.URL || window.webkitURL;
		var blobURL;
		if(URL) {
			$inputImage.on('change', function () {
				
				var files = this.files;
				var file;
				if(!$image.data('cropper')) {
					return;
				}
				if(files && files.length) {
					file = files[0];
					if(/^image\/\w+$/.test(file.type)) {
						// alert("Fichier : " + files.length + "\nType : " + file.type + "\nTaille : " + file.size + "\nNom : " + file.name);
						if(file.size < (cropperData.maxfilesize * 1000000)) {
							var olddata = $.extend({}, data);
							data.file = {
								type: file.type,
								name: file.name,
								size: file.size,
							};
							setLoading(true);
							var charge = new FileReader();
							charge.readAsDataURL(file);
							charge.onloadend = function(e){
								actualFile = file;
								var send = $.extend({}, data);
								send.raw = e.target.result;
								$.ajax({
									method: "POST",
									url: URLrawfile,
									data: {'data': send},
								}).done(function(returnData) {
									returnData = $.parseJSON(returnData);
									if(returnData.result != true) {
										alert('Une erreur est survenue lors de l\'enregistrement. Veuillez recommencer, SVP.\nAttention : un fichier corrompu, non conforme ou trop lourd (Max. '+cropperData.maxfilesize+'Mo) peut faire échouer l\'opération.');
										data = olddata;
										updateBoard();
									} else {
										$image.attr('src', returnData.data.image);
										$contImageNotNull.removeClass('hidden');
										if(data.rawfiles.actual != null) data.rawfiles.list.push(data.rawfiles.actual);
										data.rawfiles.actual = returnData.data.id;
										data.delete = false;
										updateBoard();
										updateFilenameCopy();
										$image.cropper('destroy').cropper(cropperOptions);
										updateIfIsPicture();
									}
								}).fail(function(jqXHR, textStatus) {
									if(dev) alert('Une erreur est survenue lors de l\'enregistrement. Veuillez recommencer, SVP.\nAttention : un fichier corrompu, non conforme ou trop lourd (Max. '+cropperData.maxfilesize+'Mo) peut faire échouer l\'opération.\nRequest failed: '+textStatus);
										else alert('Une erreur est survenue lors de l\'enregistrement. Veuillez recommencer, SVP.\nAttention : un fichier corrompu, non conforme ou trop lourd (Max. '+cropperData.maxfilesize+'Mo) peut faire échouer l\'opération.');
									data = olddata;
									updateBoard();
								}).always(function() {
									setLoading(false);
									$inputImage.val('');
									updateIfIsPicture();
								});
							}
						} else {
							alert('Fichier trop lourd… vous devez choisir un fichier de moins de '+cropperData.maxfilesize+'Mo.');
						}
					} else {
						alert('Vous devez sélectionner un fichier de type image');
					}
				}
			});
		} else {
			$inputImage.prop('disabled', true).parent().addClass('disabled');
		}



		/*******************************/
		/* EVENTS                      */
		/*******************************/

		/**
		 * EVENTS : bouton(s) suppression de l'image
		 */
		$suppImage.on('click', function(e) {
			e.preventDefault();
			$image.attr('src', '#');
			data.delete = deletevalue;
			updateBoard();
			updateIfIsPicture();
		});

		/**
		 * EVENTS : methods for crop buttons
		 */
		$cropButtons.on('click', function () {
			var $this = $(this);
			var data = $this.data();
			var $target;
			var result;
			if ($this.prop('disabled') || $this.hasClass('disabled')) {
				return;
			}
			if ($image.data('cropper') && data.method) {
				data = $.extend({}, data); // Clone a new one
				if (typeof data.target !== 'undefined') {
					$target = $(data.target);
					if (typeof data.option === 'undefined') {
						try {
							data.option = JSON.parse($target.val());
						} catch (e) {
							console.log(e.message);
						}
					}
				}
				result = $image.cropper(data.method, data.option, data.secondOption);
				switch (data.method) {
					case 'scaleX':
					case 'scaleY':
						$(this).data('option', -data.option);
						break;
				}
				if ($.isPlainObject(result) && $target) {
					try {
						$target.val(JSON.stringify(result));
					} catch (e) {
						console.log(e.message);
					}
				}
			}
		});

		/**
		 * EVENTS : methods for crop toggles
		 */
		$('.docs-toggles', this).on('change', 'input', function (e) {
			// e.preventDefault();
			// if (!$image.data('cropper')) return;
			var idx = parseInt($(this).attr('data-ratio-index'));
			if(idx != undefined) {
				data.ratioIndex = idx;
				updateBoard();
			}
			// cropperOptions.aspectRatio = ratio[parseInt($(this).val())];
			cropperOptions.aspectRatio = ratio[idx];
			if(cropperOptions.aspectRatio == undefined) cropperOptions.aspectRatio = null;
			$image.cropper('destroy').cropper(cropperOptions);
			updateIfIsPicture();
		});


	});



	/*******************************/
	/* ON SUBMIT                   */
	/*******************************/

	var updateImageData = function(cropperId) {
		// var cropperId = cropperId;
		var $image = $(cropperId + '_image');
		// contenu imagee
		var data = $(cropperId).data('infoForPersist');
		data.getData = $image.cropper("getData");
		// Math.round
		data.getData.x = Math.round(data.getData.x);
		data.getData.y = Math.round(data.getData.y);
		data.getData.width = Math.round(data.getData.width);
		data.getData.height = Math.round(data.getData.height);
		// info file/size
		$($(cropperId).data('infoForPersist_field')).val(JSON.stringify(data));
	}

	var sub = false; // site_adminbundle_media
	// Download new image to crop
	$(document.body).on('submit', "form", function(e) {
		var $cropperBlock = $('.cropper-block', this);
		if($cropperBlock.length) {
			if(sub == false) {
				e.preventDefault();
				if(loading == false) {
					sub = true;
					$cropperBlock.each(function(e) {
						updateImageData("#"+$(this).attr('id').replace(new RegExp("("+croppername+")$", "g"), ''));
					});
					$(this).submit();
				} else {
					alert('Le chargement est toujours en cours, veuillez patienter, svp.');
				}
			}
		}
	});

});


