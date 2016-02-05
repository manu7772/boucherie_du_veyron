$(document).ready(function(){

	$('form .cropper-block').each(function(e) {

		// Format ratio
		var ratio = 800	/ 600;
		var ratio = null;

		var actualFile = null;

		var feedbackData = Array();
		var cropperId = $(this).attr('data-id');
		// Copy file name un field
		var fieldName = $(this).attr('data-form-name');
		var fieldForCopyName = $(cropperId).attr('filename-copy');
		var regFC = new RegExp("(_"+fieldName+")$", "g");
		var $fieldForCopy = $(cropperId.replace(regFC, '_'+fieldForCopyName));

		// info for persist
		var regFP = new RegExp("(_"+fieldName+")$", "g");
		// var $infoForPersist = $(cropperId.replace(regFP, '_infoForPersist'));
		$(cropperId).data('infoForPersist_field', cropperId.replace(regFP, '_infoForPersist'));

		var $inputImage = $(cropperId+'_fileInput');
		var $image = $(cropperId+'_image');
		var $previews = $('.img-preview', this);
		var $cropButtons = $('.docs-buttons button[data-method]', this);
		// thereIsPicture
		var $contImageNotNull = $('.containerImageNull', this);
		var $suppImage = $('.noImage', this);
		var $contFile = $('.container-actions', this);
		// accept / Type de fichier
		var accept = $(cropperId).attr('cropper-accept');
		if(accept != undefined) $inputImage.attr('accept', accept); else accept = $inputImage.attr('accept');
		var deletable = $(cropperId).attr('deletable');
		if(deletable == undefined) {
			deletable = false;
			$('.noImage', this).remove();
		} else deletable = true;

		console.log("• Loading : ", "file-cropper " + cropperId+' / Accept : '+accept);

		var initBoard = function() {
			var data = $(cropperId).data('infoForPersist');
			if(data == undefined) {
				data = {
					height: null,
					width: null,
					file: {
						type: null,
						name: null,
						size: null,
					},
					dataType: 'cropper',
					fileStatus: 'empty',
				};
			}
			return data;
		}

		var updateBoard = function(e) {
			data = initBoard();
			if(e != undefined) {
				data.height = e.height;
				data.width = e.width;
				data.dataType = 'cropper';
				$(cropperId).data('infoForPersist', data);
				// $($(cropperId).data('infoForPersist_field')).val(JSON.stringify($(cropperId).data('infoForPersist')));
			}
		}

		var updateBoardFile = function(file) {
			if(file == undefined) var file = actualFile; else var file = file;
			data = initBoard();
			data.file = {
				type: file.type,
				name: file.name,
				size: file.size,
			};
			data.fileStatus = 'filled';
			$(cropperId).data('infoForPersist', data);
			// $($(cropperId).data('infoForPersist_field')).val(JSON.stringify($(cropperId).data('infoForPersist')));
		}

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

		/**
		 * Mise à jour des éléments graphiques selon la présence ou non d'une image
		 * @return boolean
		 */
		var updateIfIsPicture = function() {
			var value = $image.attr('src');
			if(value == "#" || value == '') {
				$contImageNotNull.addClass('hidden');
				return true;
			} else {
				$contImageNotNull.removeClass('hidden');
				return false;
			}
		}
		updateIfIsPicture();

		/**
		 * Copie le nom du fichier dans le champ passé en paramètre
		 * @param string name
		 */
		var updateFilenameCopy = function(name) {
			if(name != undefined) var name = name;
				else if(actualFile != null) var name = actualFile.name;
			if($fieldForCopy.length && name != undefined) {
				$fieldForCopy.val(name);
			}
		}

		/**
		 * Bouton(s) suppression de l'image
		 */
		$suppImage.on('click', function(e) {
			e.preventDefault();
			$image.attr('src', '#');
			updateIfIsPicture();
		});

		cropperOptions = {
			aspectRatio: ratio,
			preview: cropperId+"_block .img-preview",
			autoCropArea: 1,
			responsive: true,
			restore: true,
			// viewMode: 3,
			movable: false,
			// rotatable: false,
			// scalable: false,
			// zoomable: false,
			zoomOnTouch: false,
			zoomOnWheel: false,
			wheelZoomRatio: false,
			// cropBoxMovable: false,
			// cropBoxResizable: false,
			toggleDragModeOnDblclick: false,
			build: function (e) {
				initPreviews($(this).clone());
				updateBoard(e);
			},
			crop: function (e) {
				var imageData = $(this).cropper('getImageData');
				var previewAspectRatio = e.width / e.height;
				$previews.each(function () {
					var $preview = $(this);
					var previewWidth = $preview.width();
					var previewHeight = previewWidth / previewAspectRatio;
					var imageScaledRatio = e.width / previewWidth;
					$preview.height(previewHeight).find('img').css({
						width: imageData.naturalWidth / imageScaledRatio,
						height: imageData.naturalHeight / imageScaledRatio,
						marginLeft: -e.x / imageScaledRatio,
						marginTop: -e.y / imageScaledRatio
					});
				});
				updateBoard(e);
				// var result = $image.cropper("getCroppedCanvas", null);
				// $(cropperId).val(result.toDataURL());
			},
			done: function(data) {
				// Output the result data for cropping image.
			},
		}

		$image.cropper(cropperOptions);

		// Import image
		var URL = window.URL || window.webkitURL;
		var blobURL;
		if(URL) {
			$inputImage.change(function () {
				var files = this.files;
				var file;
				if(!$image.data('cropper')) {
					return;
				}
				if(files && files.length) {
					file = files[0];
					// alert("Fichier : " + files.length + "\nType : " + file.type + "\nTaille : " + file.size + "\nNom : " + file.name);
					if(/^image\/\w+$/.test(file.type)) {
						blobURL = URL.createObjectURL(file);
						$image.one('built.cropper', function () {
							// Revoke when load complete
							URL.revokeObjectURL(blobURL);
						}).cropper('replace', blobURL);
						actualFile = file;
						updateBoardFile(actualFile);
						updateFilenameCopy();
						$inputImage.val('');
					} else {
						alert('Vous devez sélectionner un fichier de type image');
					}
					updateIfIsPicture();
				}
			});
		} else {
			$inputImage.prop('disabled', true).parent().addClass('disabled');
		}

		// Methods for crop buttons
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

	});

	var updateImageData = function(cropperId) {
		// var cropperId = cropperId;
		var $image = $(cropperId + '_image');
		// options : size
		var options = $(cropperId + "_sizeRef").attr('data-option');
		if(options != undefined) options = JSON.parse(options); else options = null;
		// contenu imagee
		if($image.attr('src') != "#" ) {
			var result = $image.cropper("getCroppedCanvas", options);
			$(cropperId).val(result.toDataURL());
			// info file/size
			$($(cropperId).data('infoForPersist_field')).val(JSON.stringify($(cropperId).data('infoForPersist')));
		} else {
			$(cropperId).val('');
		}
	};

	var sub = false; // site_adminbundle_media
	// Download new image to crop
	$(document.body).on('submit', "form", function(e) {
		var $cropperBlock = $('.cropper-block', this);
		if($cropperBlock.length) {
			if(sub == false) {
				e.preventDefault();
				sub = true;
				$cropperBlock.each(function(e) {
					updateImageData($(this).attr('data-id'));
				});
				$(this).submit();
			}
		}
	});



});
