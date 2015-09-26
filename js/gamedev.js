/*jslint vars: true, plusplus: true, devel: true, nomen: true, maxerr: 50, regexp: true, browser: true, white: true */
/*global $, google, MarkerClusterer */

google.load('visualization', '1', {packages:['corechart']});
google.load('maps', '3', { other_params : 'sensor=false' });

$(document).ready(function () {

	console.info('We\'re currently not hiring. But, are you interested in having fun with the dark side of data visualization? Fork this project: https://github.com/harian-kompas/gamedev-survey');

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

	Array.prototype.distinctSingleDimension = function () {
		var a = [],
			i,
			len = this.length;

		for (i = 0; i < len; i++) {
			if (a.indexOf(this[i]) < 0) {
				a.push(this[i]);
			}
				
		}

		return a;

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
						fcrFrag = $(document.createDocumentFragment()),
						options;

					for (i = 1; i <= 30; i++) {
						options = $('<option></option>');
						options.attr('value', i);
						options.text(i + ' orang');
						fclFrag.append(options);
					}

					clFormControl.append(fclFrag);

					$.each(data, function() {
						var opt = $('<option></option>');
						opt.attr('value', this.key);
						opt.text('lulus ' + this.value);
						fcrFrag.append(opt);
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
					i,
					options;

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
					options = $('<option></option>');
					options.attr('value', i);
					options.text(i);
					cyDocFrag.append(options);
				}

				cyFormControl.append(cyDocFrag);

				cpFormGroup.addClass('form-group');
				cpWrapper.addClass('checkbox');

				$.each(platforms, function() {
					var labels = $('<label></label>'),
						inputs = $('<input></input>');

					labels.addClass('checkbox-inline');
					inputs.attr('type', 'checkbox');
					inputs.attr('value', this);
					inputs.attr('name', 'products[platform][' + c + '][]');

					labels.append(inputs).append(document.createTextNode(this.ucfirst()));

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
				doValidate = true;

			// validate user's inputs
			if (doValidate) {
				if (name !== '') {
					$('#txt-studio-name').parent().removeClass('has-error');
				} else {
					$('#txt-studio-name').parent().addClass('has-error');
					return;
				}

				if (location !== '') {
					$('#txt-studio-location').parent().removeClass('has-error');
				} else {
					$('#txt-studio-location').parent().addClass('has-error');
					return;
				}

				if (product !== '') {
					$('#txt-studio-products').parent().removeClass('has-error');
				} else {
					$('#txt-studio-products').parent().addClass('has-error');
					return;					
				}

				$.each(publications, function() {
					if ($(this).checked) {
						checkPubCounter++;
					}
				});

				if (checkPubCounter > 0) {
					$('input[name^="publications"]').parent().parent().removeClass('has-error');
				} else {
					$('input[name^="publications"]').parent().parent().addClass('has-error');
					return;
				}
			}
				

			// send data if okay
			$('#the-survey').submit();

		});

	}

	$.getJSON('index.php?p=api&t=' + Date.now(), function (data) {
		var drawAcademicChart,
			drawCharts,
			drawDistributionMap,
			drawGamePerYearChart,
			drawStudioEmployeesSize;

		drawAcademicChart = function () {
			var arrDataAcademicDegree = [
					['Pendidikan', 'Pekerja']
				],
				arrPersonnels = {
					total : 0,
					degree : []
				},
				dataAcademicDegree,
				degreeChart = new google.visualization.PieChart(document.getElementById('edu-degree')),
				degreeChartOptions = {
					is3D: true,
					legend: { position: 'bottom' },
					slices : {
						0: { offset: 0.2 },
						3: { offset: 0.4 }
					}
				};
			
			// set container's width and height
			$('#edu-degree').width( $('#edu-degree').width() ).height( Math.floor( (9/16) * $('#edu-degree').width() ) );

			$.each(['SD', 'SMP', 'SMA/SMK', 'D-1', 'D-2', 'D-3', 'D-4', 'S-1', 'S-2', 'S-3'], function (index, value) {
				var personnelDegree = {
					name : value,
					total : 0
				};
				arrPersonnels.degree.push(personnelDegree);
			});

			$.each(data, function () {
				var element = this,
					arrPersonelEdu = element.studio.personnels.education;

				arrPersonnels.total += parseInt(element.studio.personnels.total, 10);

				$.each(arrPersonelEdu, function () {
					var degree = this.degree,
						num = this.num;

					$.each(arrPersonnels.degree, function () {
						
						if (degree === this.name) {
							this.total += num;
						}
					});
				});

			});

			$.each(arrPersonnels.degree, function () {
				arrDataAcademicDegree.push([this.name, this.total]);
			});

			dataAcademicDegree = google.visualization.arrayToDataTable(arrDataAcademicDegree);
			degreeChart.draw(dataAcademicDegree, degreeChartOptions);
		};

		drawDistributionMap = function () {
			var distinctYears,
				rawYears = [],
				populateStudiosData = function (selectedYear, studios) {
					var docFrag = $(document.createDocumentFragment()),
						studiosLen = studios.length,
						studiosChunked = [],
						// tempStudios
						chunk = 4,
						i;

					$('#studios-this-year').empty();
					$('#studios-this-year').parent().find('h3').text('Studio yang Muncul pada Tahun ' + selectedYear);

					// sort by studio name	
					studios.sort(function (a, b) {
						if (a.name < b.name) {
							return -1;
						}

						if (a.name > b.name) {
							return 1;
						}

						return 0;
					});

					for (i = 0; i < studiosLen; i += chunk) {
						studiosChunked.push(studios.slice(i, i + chunk));
					}

					$.each(studiosChunked, function () {
						var values = this,
							row = $('<div></div>'),
							rowFrag = $(document.createDocumentFragment());

						$.each(values, function() {
							var col = $('<div></div>'),
								table = $('<table></table>'),
								thead = $('<thead></thead>'),
								theadTr = $('<tr></tr>'),
								theadTh = $('<th></th>'),
								tbody = $('<tbody></tbody>'),
								trLocation = $('<tr></tr>'),
								tdLocation1 = $('<td></td>'),
								tdLocation2 = $('<td></td>'),
								trProducts = $('<tr></tr>'),
								tdProducts1 = $('<td></td>'),
								tdProducts2 = $('<td></td>'),
								productsStr = '',
								productsRaw = this.products;

							$.each(productsRaw, function () {
								productsStr += this.name + ' (' + this.year + ', ' + this.platform + '); ';
							});
							
							col.addClass('col-md-3');

							table.addClass('table table-stripped');
							theadTh.attr('colspan', 2);
							theadTh.text(this.name);

							theadTr.append(theadTh);
							thead.append(theadTr);

							tdLocation1.text('Lokasi');
							tdLocation2.text(this.location.name);
							trLocation.append(tdLocation1, tdLocation2);

							tdProducts1.text('Produk');
							tdProducts2.text(productsStr.trim().substring(0, productsStr.trim().length - 1));
							trProducts.append(tdProducts1, tdProducts2);

							tbody.append(trLocation, trProducts);

							table.append(thead, tbody);
							col.append(table);
							rowFrag.append(col);
						});

						row.addClass('row');

						row.append(rowFrag);
						docFrag.append(row);
						// console.log(this);
					});

					$('#studios-this-year').append(docFrag);
				},
				populateMap = function (selectedYear) {
					var mapData = [],
						indonesia = {
							lat : -0.789275,
							lng : 113.921327
						},
						map,
						mapOptions,
						marker,
						markers = [],
						mc,
						mcOptions = {
							gridSize: 30
						},
						customMapType = [{"featureType":"administrative","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"administrative.country","elementType":"geometry.stroke","stylers":[{"visibility":"off"}]},{"featureType":"administrative.province","elementType":"geometry.stroke","stylers":[{"visibility":"off"}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"visibility":"on"},{"color":"#e3e3e3"}]},{"featureType":"landscape.natural","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"all","stylers":[{"color":"#cccccc"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"transit.line","elementType":"labels.text","stylers":[{"visibility":"off"}]},{"featureType":"transit.station.airport","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"transit.station.airport","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#FFFFFF"}]},{"featureType":"water","elementType":"labels","stylers":[{"visibility":"off"}]}],
						studiosData = [];

					$.each(data, function () {
						if (this.studio.yearStart <= selectedYear) {
							mapData.push({
								lat : this.studio.location.latitude,
								lng : this.studio.location.longitude,
								locationName : this.studio.location.name
							});
						}

						if (this.studio.yearStart === selectedYear) {
							studiosData.push(this.studio);
						}
					});

					$('#map').empty();

					mapOptions = {
							center : indonesia,
							disableDefaultUI : true,
							styles : customMapType,
							zoom : 5
						};

					map = new google.maps.Map(document.getElementById('map'), mapOptions);

					$.each(mapData, function () {
						marker = new google.maps.Marker({
							position: { lat : this.lat , lng : this.lng },
							map : map
						});
						markers.push(marker);
					});

					mc = new MarkerClusterer(map, markers, mcOptions);

					populateStudiosData(selectedYear, studiosData);
					// console.log(mapData);
				},
				populateNav = function(arrYears) {
					var docFrag = $(document.createDocumentFragment()),
						armNavLinks = function (obj) {
							obj.on('click', function (e) {
								e.preventDefault();
								var btn = $(this);

								$('.map-nav-links').each(function () {
									if (btn.text() === $(this).text()) {
										$(this).parent().addClass('active');
									} else {
										$(this).parent().removeClass('active');
									}
								});


								populateMap(parseInt(btn.text(), 10));
							});
						};
					$.each(arrYears, function (index, element) {
						var items = $('<li></li>'),
							links = $('<a></a>');

						if (index === 0) {
							items.addClass('map-nav-items active');
						} else {
							items.addClass('map-nav-items');
						}
							
						links.addClass('map-nav-links').attr('href', '#').text(element);

						items.append(links);
						docFrag.append(items);
						armNavLinks(links);
					});

					$('#map-nav').append(docFrag);
				};

			$.each(data, function() {
				rawYears.push(this.studio.yearStart);
			});

			distinctYears = rawYears.sort().distinctSingleDimension();
			populateNav(distinctYears);
			populateMap(distinctYears[0]);
		};

		drawGamePerYearChart = function () {
			var gamesData = [
					['Tahun', 'Desktop', 'Mobile']
				],
				numDesktop,
				numMobile,
				rawGamesData = [],
				rawGamesDataLen,
				startYear = 2000,
				today = new Date(),
				currentYear = today.getFullYear(),
				i,
				j,
				gamesChart = new google.visualization.ColumnChart(document.getElementById('game-publications')),
				gamesChartData,
				gamesChartOptions = {
					legend: { position : 'in' },
					isStacked : true
				};

			// set container's width and height
			$('#game-publications').width( $('#game-publications').width() ).height( Math.floor( (9/16) * $('#game-publications').width()) );

			$.each(data, function () {
				var products = this.studio.products;

				$.each(products, function () {
					rawGamesData.push(this);
				});
				
			});

			rawGamesDataLen = rawGamesData.length;

			for (i = startYear; i <= currentYear; i++) {
				numDesktop = 0;
				numMobile = 0;

				for (j = 0; j < rawGamesDataLen; j++) {
					if (i === rawGamesData[j].year) {
						if (rawGamesData[j].platform === 'desktop') {
							numDesktop++;
						} else if (rawGamesData[j].platform === 'mobile') {
							numMobile++;
						}
					}
				}

				if (numDesktop > 0 || numMobile > 0) {
					gamesData.push(
						[i.toString(), numDesktop, numMobile]
					);
				}

					

			}

			gamesChartData = google.visualization.arrayToDataTable(gamesData);
			gamesChart.draw(gamesChartData, gamesChartOptions);
		};

		drawStudioEmployeesSize = function () {
			var arrEmployeesSizeData = [
					['Kategori', 'Jumlah Studio']
				],
				arrSizes = [
					{
						min: 1,
						max: 3
					},
					{
						min: 4,
						max: 7
					},
					{
						min: 8,
						max: 10
					},
					{
						min: 11,
						max: 20
					},
					{
						min: 21,
						max: 50
					},
					{
						min: 51,
						max: 9999999
					}
				],
				arrSizesLen = arrSizes.length,
				dataLen = data.length,
				i,
				j,
				min = 0,
				max = 0,
				numStudio,
				strColumnCat,
				dataNumStudios,
				numStudiosChart = new google.visualization.PieChart(document.getElementById('num-studios')),
				numStudiosChartOptions = {
					is3D: true,
					legend: { position: 'right' }
				};

			for (i = 0; i < arrSizesLen; i++) {
				min = arrSizes[i].min;
				max = arrSizes[i].max;
				numStudio = 0;
				strColumnCat = (i === arrSizesLen - 1) ? '> 50 pekerja' : min + '-' + max + ' pekerja';

				for (j = 0; j < dataLen; j++) {
					if (data[j].studio.personnels.total >= min && data[j].studio.personnels.total <= max) {
						numStudio++;
					}
				}

				if (numStudio > 0) {
					arrEmployeesSizeData.push([strColumnCat, numStudio]);
				}
			}

			$('#num-studios').width( $('#num-studios').width() ).height( Math.floor( (9/16) * $('#num-studios').width() ) );
			dataNumStudios = google.visualization.arrayToDataTable(arrEmployeesSizeData);
			numStudiosChart.draw(dataNumStudios, numStudiosChartOptions);
		};

		drawCharts = function () {
			drawDistributionMap();
			drawStudioEmployeesSize();
			drawAcademicChart();
			drawGamePerYearChart();
		};

		if ($('#map').length) {
			google.setOnLoadCallback(drawCharts);
		}
	});

		

});