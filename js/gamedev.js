$(document).ready(function () {

	String.prototype.ucfirst = function () {
		return this.charAt(0).toUpperCase() + this.slice(1);
	}

	if ($('#btn-add-personnels').length) {
		$('#btn-add-personnels').click(function (e) {
			// prevent default action
			e.preventDefault();

			if ($('#team-members').length) {
				
				// create elements
				var colLeft = $('<div></div>'),
					colRight = $('<div></div>'),
					colAdmin = $('<div></div>'),
					clFormGroup = $('<div></div>'),
					clFormControl = $('<select></select>'),
					crFormGroup = $('<div></div>'),
					crFormControl = $('<select></select>'),
					caDelButton = $('<a></a>');

				//set elements' attributes;
				colLeft.addClass('col-md-6');
				colRight.addClass('col-md-5');
				colAdmin.addClass('col-md-1');
				clFormGroup.addClass('form-group');
				clFormControl.addClass('form-control');
				clFormControl.attr('name', 'personnels[number][]');
				crFormGroup.addClass('form-group');
				crFormControl.addClass('form-control');
				crFormControl.attr('name', 'personnels[edu][]');
				caDelButton.addClass('btn btn-danger');
				caDelButton.text('X');

				clFormGroup.append(clFormControl);
				crFormGroup.append(crFormControl);
				

				colLeft.append(clFormGroup);
				colRight.append(crFormGroup);
				colAdmin.append(caDelButton);

				$('#team-members').append(colLeft, colRight, colAdmin);

				// get academic level
				$.getJSON('index.php?p=api&sp=akademik', function (data) {
					var i,
						fclFrag = $(document.createDocumentFragment()),
						fcrFrag = $(document.createDocumentFragment());

					for (i = 1; i <= 20; i++) {
						var options = $('<option></option>');
						options.attr('value', i);
						options.text(i + ' orang');
						fclFrag.append(options);
					}

					clFormControl.append(fclFrag);

					$.each(data, function(index, element) {
						var options = $('<option></option>');
						options.attr('value', element.key);
						options.text('lulus ' + element.value);
						fcrFrag.append(options);
					});

					crFormControl.append(fcrFrag);
				});

				caDelButton.one('click', function (e) {
					e.preventDefault();
					colLeft.remove();
					colRight.remove();
					colAdmin.remove();
				});
			}

		});
	}

	if ($('#btn-add-products').length) {
		$('#btn-add-products').click(function (e) {
			// prevent default action
			e.preventDefault();

			if ($('#products').length) {

				// create elements
				var today = new Date(),
					currentYear = today.getFullYear(),
					startYear = currentYear - 15,
					row = $('<div></div>'),
					colName = $('<div></div>'),
					colYear = $('<div></div>'),
					colPlatform = $('<div></div>'),
					colAdmin = $('<div></div>'),
					cnFormGroup = $('<div></div>'),
					cnFormControl = $('<input></input>'),
					cyFormGroup = $('<div></div>'),
					cyFormControl = $('<select></select>'),
					cyDocFrag = $(document.createDocumentFragment()),
					cpFormGroup = $('<div></div>'),
					cpDocFrag = $(document.createDocumentFragment()),
					cpWrapper = $('<div></div>'),
					caDelButton = $('<a></a>'),
					platforms = ['desktop', 'mobile'],
					i;

				//set elements' attributes;
				row.addClass('row');
				colName.addClass('col-md-4');
				colYear.addClass('col-md-3');
				colPlatform.addClass('col-md-4');
				colAdmin.addClass('col-md-1');

				cnFormGroup.addClass('form-group');
				cnFormControl.addClass('form-control');
				cnFormControl.attr('type', 'text');
				cnFormControl.attr('placeholder', 'Judul karya');
				cnFormControl.attr('maxlength', 255);

				cyFormGroup.addClass('form-group');
				cyFormControl.addClass('form-control');
				// console.log(startYear);
				for (i = startYear; i <= currentYear; i++) {
					var options = $('<option></option>');
					options.attr('value', i);
					options.text(i);
					cyDocFrag.append(options);
				}

				cyFormControl.append(cyDocFrag);

				cpFormGroup.addClass('form-group');
				cpWrapper.addClass('checkbox');

				$.each(platforms, function(index, element) {
					var labels = $('<label></label>'),
						inputs = $('<input></input>');

					labels.addClass('checkbox-inline');
					inputs.attr('type', 'checkbox');
					inputs.attr('value', element);

					labels.append(inputs).append(document.createTextNode(element.ucfirst()));

					cpDocFrag.append(labels);
				});

				cpWrapper.append(cpDocFrag);

				caDelButton.addClass('btn btn-danger');
				caDelButton.text('X');

				cnFormGroup.append(cnFormControl);
				cyFormGroup.append(cyFormControl);
				cpFormGroup.append(cpWrapper);

				colName.append(cnFormGroup);
				colYear.append(cyFormGroup);
				colPlatform.append(cpFormGroup);
				colAdmin.append(caDelButton);

				row.append(colName, colYear, colPlatform, colAdmin);
				$('#products').append(row);

				caDelButton.one('click', function (e) {
					e.preventDefault();
					colName.remove();
					colYear.remove();
					colPlatform.remove();
					colAdmin.remove();
					row.remove();
				});
			}

		});
	}

	if ($('#btn-submit').length) {

		$('#btn-submit').click(function (e) {
			e.preventDefault();
			// var inputs = $('#the-survey').serializeArray();
			// console.log(inputs);

			var name = $('#txt-studio-name').val().trim(),
				location = $('#txt-studio-location').val().trim(),
				product = $('#txt-studio-products').val().trim(),
				publications = $('input[name^="publications"]'),
				checkPubCounter = 0,
				doValidate = false;

			// validate user's inputs
			if (doValidate) {
				if (name === '') {
					$('#txt-studio-name').parent().addClass('has-error');
					return;
				} else {
					$('#txt-studio-name').parent().removeClass('has-error');
				}

				if (location === '') {
					$('#txt-studio-location').parent().addClass('has-error');
					return;
				} else {
					$('#txt-studio-location').parent().removeClass('has-error');
				}

				if (product === '') {
					$('#txt-studio-products').parent().addClass('has-error');
					return;
				} else {
					$('#txt-studio-products').parent().removeClass('has-error');
				}

				
				$.each(publications, function(index, element) {
					if (element.checked) {
						checkPubCounter++;
					}
				});

				if (checkPubCounter === 0) {
					$('input[name^="publications"]').parent().parent().addClass('has-error');
					return;
				} else {
					$('input[name^="publications"]').parent().parent().removeClass('has-error');
				}
			}
				

			// send data if okay
			$('#the-survey').submit();

		});

	}

});