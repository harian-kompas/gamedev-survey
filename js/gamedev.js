/*jslint vars: true, plusplus: true, devel: true, nomen: true, maxerr: 50, regexp: true, browser: true, white: true */
/*global $, google */

$(document).ready(function () {

	console.info('We meet again, at last. The circle is now complete. Interested in having fun with the dark side of data visualization? Stalk me at https://id.linkedin.com/in/yudhawijaya');

	Array.prototype.contains = function (a) {
		var len = this.length,
			i;

		for (i = 0; i < len; i++) {
			if (this[i] === a) {
				return true;
			}
		}

		return false;
	};

	String.prototype.ucfirst = function () {
		return this.charAt(0).toUpperCase() + this.slice(1);
	};

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

					for (i = 1; i <= 30; i++) {
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

		var c = 1;

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
				cnFormControl.attr('name', 'products[name][]');

				cyFormGroup.addClass('form-group');
				cyFormControl.addClass('form-control');
				cyFormControl.attr('name', 'products[year][]');
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
					inputs.attr('name', 'products[platform][' + c + '][]');

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

				c++;
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


if ($('#map').length) {

	google.load('visualization', '1', {packages:['corechart', 'map']});
	google.setOnLoadCallback(drawCharts);

	function drawCharts() {
		drawDistributionMap();
		drawAcademicChart();
		drawGamePerYearChart();
	}

	function drawAcademicChart() {
		$.getJSON('index.php?p=api&sp=curang&t=' + Date.now(), function (data) {
			// console.log(data);
			// create academic degree pie chart
			var personnelsDegrees = data.summaries.personnels.degree,
				arrDataAcademicDegree = [
					['Pendidikan', 'Pekerja']
				],
				dataAcademicDegree,
				degreeChart = new google.visualization.PieChart(document.getElementById('edu-degree')),
				degreeChartOptions = {
					chartArea : {
						height: '100%',
						width: '100%'
					},
					is3D: true
				};

				$.each(personnelsDegrees, function(index, element) {
					arrDataAcademicDegree.push([element.name, element.total]);
				});

			

			dataAcademicDegree = google.visualization.arrayToDataTable(arrDataAcademicDegree);

			degreeChart.draw(dataAcademicDegree, degreeChartOptions);

			// console.log(arrDataAcademicDegree);

		});
	}

	function drawDistributionMap() {
		$.getJSON('index.php?p=api&sp=curang&t=' + Date.now(), function (data) {

			var distinctYears = data.summaries.distinctStudioStartYears;
			
			function insertYearNavigation() {
				var navFrag = $(document.createDocumentFragment()),
					displayContent = function(year) {
						var allContents = data.summaries.studioDistributionsPerYear,
							selectedContents,
							map = new google.visualization.Map(document.getElementById('map')),
							mapData = [
								['Lat', 'Long']
							],
							mapOptions = {
								mapType : 'normal',
								zoomLevel: 5
							},
							googleMapData;

						$.each(allContents, function(index, element) {

							if (element.year === year) {
								selectedContents = element.location
							}
						});

						$.each(selectedContents, function(index, element) {
							// console.log(element);
							mapData.push([
								parseFloat(element.lat),
								parseFloat(element.lng)
							]);
						});

						googleMapData = google.visualization.arrayToDataTable(mapData);
						map.draw(googleMapData, mapOptions);

						// console.log(mapData);
					},
					selectYear = function(e) {
						e.preventDefault();
						var btn = $(this),
							items = $('.map-nav-links');
						
						$.each(items, function(index, element) {
							if (btn.text() === $(element).text()) {
								$(element).parent().addClass('active');
								displayContent(parseInt(btn.text(), 10));
							} else {
								$(element).parent().removeClass('active');
							}
						});
					};

				$.each(distinctYears, function(index, element) {
					var items = $('<li></li>'),
						links = $('<a></a>'),
						isActive = (index === 0) ? ' active' : '';

					items.addClass('map-nav-items' + isActive);
					links.text(element);
					links.addClass('map-nav-links');
					links.attr('href', '#');

					items.append(links);
					navFrag.append(items);
					links.on('click', selectYear); 
				});

				$('#map-nav').append(navFrag);

				displayContent(parseInt(distinctYears[0], 10));
			}


			insertYearNavigation();

		});
	}

	function drawGamePerYearChart() {
		$.getJSON('index.php?p=api&sp=curang&t=' + Date.now(), function (data) {
			
			var contents = data.surveyResultDetails,
				gamesRaw = [],
				gamesRawLen,
				today = new Date,
				currentYear = today.getFullYear(),
				startYear = 2000,
				gamesData = [
					['Tahun', 'Desktop', 'Mobile']
				],
				i,
				j,
				numDesktop,
				numMobile;

			$.each(contents, function(index, element) {
				var products = element.studio.products;
				$.each(products, function(id, el) {
					gamesRaw.push(el);
				})
			});

			gamesRawLen = gamesRaw.length;

			for (i = startYear; i <= currentYear; i++) {
				numDesktop = 0;
				numMobile = 0;

				for (j = 0; j < gamesRawLen; j++) {
					if (gamesRaw[j].year === i) {
						if (gamesRaw[j].platform === 'desktop') {
							numDesktop++;
						} else if (gamesRaw[j].platform === 'mobile') {
							numMobile++;
						}
					}
				}

				if (numDesktop > 0 || numMobile > 0) {
					gamesData.push(
						[ i.toString(), numDesktop, numMobile ]
					);
				}

				
			}

			var gamesChart = new google.visualization.ColumnChart(document.getElementById('game-publications')),
				gamesChartOptions = {
					chartArea : {
						// height: '95%',
						// width: '95%',
						isStacked : false,
						// bar : { groupWidth : '75%' },
						legend: { position : 'right' }
					}
				},
				chartData = google.visualization.arrayToDataTable(gamesData);

			gamesChart.draw(chartData, gamesChartOptions);

			// console.log(gamesRaw);
			// console.log(gamesData);
			// console.log(currentYear);
		});
	}

	
}
