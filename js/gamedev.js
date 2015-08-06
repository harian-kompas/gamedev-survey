$(document).ready(function () {

	if ($('#btn-add-personnels').length) {
		$('#btn-add-personnels').click(function (e) {
			// prevent default action
			e.preventDefault();

			if ($('#team-members').length) {
				
				// create elements
				var colLeft = $('<div></div>'),
					colRight = $('<div></div>'),
					clFormGroup = $('<div></div>'),
					clFormControl = $('<select></select>'),
					crFormGroup = $('<div></div>'),
					crFormControl = $('<select></select>');

				//set elements' attributes;
				colLeft.addClass('col-md-6');
				colRight.addClass('col-md-6');
				clFormGroup.addClass('form-group');
				clFormControl.addClass('form-control');
				crFormGroup.addClass('form-group');
				crFormControl.addClass('form-control');


				clFormGroup.append(clFormControl);
				crFormGroup.append(crFormControl);

				colLeft.append(clFormGroup);
				colRight.append(crFormGroup);

				$('#team-members').append(colLeft, colRight);

				// get academic level
				$.getJSON('index.php?p=api&sp=akademik', function (data) {
					$.each(data, function(index, element) {
						console.log(index);
					});
				});
			}

		});
	}


});