<?php
	class GameDev {
		static $pdo, $arrNav, $arrAcademics, $arrPublications, $baseUrl;

		public function __construct() {
			try {
				GameDev::$pdo = new PDO('mysql:host='.DB_H.';dbname='.DB_D.';charset=utf8', DB_U, DB_P);
				GameDev::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				GameDev::$pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8');
				GameDev::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

				GameDev::$arrNav = array('Hasil', 'Direktori', 'Formulir');
				GameDev::$arrAcademics = array(
					'dasar' => 'SD',
					'menengahpertama' => 'SMP',
					'menengahatas' => 'SMA/SMK',
					'd1' => 'D-1',
					'd2' => 'D-2',
					'd3' => 'D-3',
					'd4' => 'D-4',
					's1' => 'S-1',
					's2' => 'S-2',
					's3' => 'S-3'
				);

				GameDev::$arrPublications = array(
					'offair' => 'Off air',
					'socialmedia' => 'Media sosial (Facebook, Twitter, dll)',
					'email' => 'E-mail',
					'publisher' => 'Penerbit (publisher)',
					'gameportal' => 'Portal game'
				);

				GameDev::$baseUrl = ($_SERVER['HTTP_HOST'] === 'localhost') ? 'http://localhost/gamedev/public_html' : 'http://id.infografik.print.kompas.com/gamedev';

			} catch(PDOException $e) {
				echo $e->getMessage();
			}
		}

		public static function get_api_others($options = array()) {
			$academicDegrees = array();
			$publicationMethods = array();
			$provinces = array();

			foreach (GameDev::$arrAcademics as $key => $value) {
				$academicDegrees[] = array(
					'key' => $key,
					'value' => $value
				);
			}

			foreach (GameDev::$arrPublications as $key => $value) {
				$publicationMethods[] = array(
					'key' => $key,
					'value' => $value
				);
			}

			$parentNid = 0;
			$queryProvinces = 'select nid, name from gamedev__location where parent_nid=:parent_nid order by serial asc';
			$statProvinces = GameDev::$pdo->prepare($queryProvinces);
			$statProvinces->bindParam(':parent_nid', $parentNid);
			$statProvinces->execute();
			$resultProvinces = $statProvinces->fetchAll(PDO::FETCH_ASSOC);

			foreach ($resultProvinces as $resultProvince) {
				$cities = array();
				$provinceNid = $resultProvince['nid'];
				$queryCities = 'select nid, name from gamedev__location where parent_nid=:province_nid order by serial asc';
				$statCities = GameDev::$pdo->prepare($queryCities);
				$statCities->bindParam(':province_nid', $provinceNid);
				$statCities->execute();
				$resultCities = $statCities->fetchAll(PDO::FETCH_ASSOC);

				foreach ($resultCities as $resultCity) {
					$cities[] = array(
						'nid' => (int)$resultCity['nid'],
						'name' => $resultCity['name']
					);
				}


				$provinces[] = array(
					'nid' => (int)$provinceNid,
					'name' => $resultProvince['name'],
					'cities' => $cities
				);
			}

			$strDetails = array(
				'academicDegrees' => $academicDegrees,
				'publicationMethods' => $publicationMethods,
				'provinces' => $provinces
			);

			$strJSON = json_encode($strDetails);

			$str = GameDev::get_page_header('json');
			$str .= (isset($options['callback']) && !empty($options['callback'])) ? $options['callback'].'('.$strJSON.');' : $strJSON; 

			echo $str;
			unset($str);
		}

		public static function get_api_results($options = array()) {
			$strDetails = array();
			$query = 'select gamedev__survey_results.id, gamedev__survey_results.datetime, gamedev__survey_results.studio_name, gamedev__survey_results.studio_url, gamedev__survey_results.studio_location, gamedev__survey_results.studio_start, gamedev__survey_results.studio_personnels, gamedev__survey_results.personnels_educations, gamedev__survey_results.products, gamedev__survey_results.publications,
					  gamedev__location.nid, gamedev__location.name as location_name, gamedev__location.latitude, gamedev__location.longitude
					  from gamedev__survey_results
					  left join gamedev__location on gamedev__survey_results.studio_location = gamedev__location.nid
					  order by id asc';
			$stat = GameDev::$pdo->prepare($query);
			$stat->execute();
			
			$results = $stat->fetchAll(PDO::FETCH_ASSOC);

			$eduSD = 0;
			$eduSMP = 0;
			$eduSMA = 0;
			$eduD1 = 0;
			$eduD2 = 0;
			$eduD3 = 0;
			$eduD4 = 0;
			$eduS1 = 0;
			$eduS2 = 0;
			$eduS3 = 0;

			foreach ($results as $result) {
				$id = $result['id'];
				$datetime = new DateTime($result['datetime']);
				$studioName = $result['studio_name'];
				$studioUrl = (empty($result['studio_url']) || $result['studio_url'] === 'http://') ? null : $result['studio_url'];
				$studioStart = (int)$result['studio_start'];
				$studioPersonnels = (int)$result['studio_personnels'];
				$personnelsEdu = explode(';', $result['personnels_educations']);
				$studioProducts = explode(';', $result['products']);
				$productPublications = explode(';', $result['publications']);
				
				$locationNid = (int)$result['nid'];
				$locationName = $result['location_name'];
				$locationLatitude = $result['latitude'];
				$locationLongitude = $result['longitude'];

				$edu = array();
				$products = array();
				$publications = array();

				foreach ($personnelsEdu as $key => $rawValue) {
					$values = explode('|', $rawValue);
					
					if (!empty($values)) {
						$num = (int)$values[0];
						$edu[] = array(
							'num' => $num,
							'degree' => GameDev::$arrAcademics[$values[1]]
						);

						if ($values[1] === 'dasar') {
							$eduSD += $num;
						} else if ($values[1] === 'menengahpertama') {
							$eduSMP += $num;
						} else if ($values[1] === 'menengahatas') {
							$eduSMA += $num;
						} else if ($values[1] === 'd1') {
							$eduD1 += $num;
						} else if ($values[1] === 'd2') {
							$eduD2 += $num;
						} else if ($values[1] === 'd3') {
							$eduD3 += $num;
						} else if ($values[1] === 'd4') {
							$eduD4 += $num;
						} else if ($values[1] === 's1') {
							$eduS1 += $num;
						} else if ($values[1] === 's2') {
							$eduS2 += $num;
						} else if ($values[1] === 's3') {
							$eduS3 += $num;
						}
					}
				}

				foreach ($studioProducts as $rawProducts) {
					$productData = explode('|', $rawProducts);
					$products[] = array(
						'name' => $productData[0],
						'year' => (int)$productData[1],
						'platform' => $productData[2]
					);
				}

				foreach ($productPublications as $productPublication) {
					$publications[] = GameDev::$arrPublications[$productPublication];
				}

				$strDetails[] = array(
					// 'id' => $id,
					'datetime' => array (
						'iso8601' => $datetime->format('c')
					),
					'studio' => array(
						'name' => $studioName,
						'url' => $studioUrl,
						'location' => array(
							'nid' => $locationNid,
							'name' => $locationName,
							'latitude' => (float)$locationLatitude,
							'longitude' => (float)$locationLongitude
						),
						'yearStart' => $studioStart,
						'personnels' => array(
							'total' => $studioPersonnels,
							'education' => $edu
						),
						'products' => $products,
						'productsPublication' => $publications
					)
				);
			}

			$strJSON = json_encode($strDetails);

			$str = GameDev::get_page_header('json');
			$str .= (isset($options['callback']) && !empty($options['callback'])) ? $options['callback'].'('.$strJSON.');' : $strJSON; 

			echo $str;
			unset($str);
		}

		public static function get_data_visualization_page() {
			$str = GameDev::get_page_header('html');
			$str .= GameDev::get_page_nav('hasil');

			// intro
			$str .= '<div class="container-fluid full-width txt-center">';
			$str .= '<h1>Industri Permainan Elektronik di Indonesia</h1>';
			$str .= '</div>';

			// map
			$str .= '<div class="container-fluid full-width txt-center">';
			$str .= '<h3>Pertumbuhan Industri dari Tahun ke Tahun</h3>';
			$str .= '<div id="map" class="map-canvas"></div>';
			$str .= '<ul id="map-nav" class="map-nav"></ul>';
			$str .= '</div>';

			$str .= '<div class="container-fluid">';
			$str .= '<h3 class="txt-center"></h3>';
			$str .= '<div id="studios-this-year"></div>';
			$str .= '</div>';


			// data visualizations using charts
			$str .= '<div class="container-fluid">';
			$str .= '<div class="row">';

			// numbers of workers
			$str .= '<div class="col-lg-4 col-md-4">';
			$str .= '<h3 class="txt-center">Persentase Ukuran Studio Berdasarkan Jumlah Pekerja</h3>';
			$str .= '<div id="num-studios"></div>';
			$str .= '</div>';

			// education degree pie chart
			$str .= '<div class="col-lg-4 col-md-4">';
			$str .= '<h3 class="txt-center">Tingkat Pendidikan Pekerja Industri Permainan Elektronik</h3>';
			$str .= '<div id="edu-degree"></div>';
			$str .= '</div>';

			// published games per year
			$str .= '<div class="col-lg-4 col-md-4">';
			$str .= '<h3 class="txt-center">Permainan Elektronik Terbit Per Tahun</h3>';
			$str .= '<div id="game-publications"></div>';
			$str .= '</div>';
			
			$str .= '</div>'; // .row
			$str .= '</div>'; // .container-fluid

			$str .= GameDev::get_page_footer('html');

			echo $str;
			unset($str);
		}

		public static function get_entry_data_form($key = null) {
			$str = GameDev::get_page_header('html');
			$str .= GameDev::get_page_nav('formulir');

			if (empty($key)) {
				$str .= '<div class="container"><br>';
				$str .= '<p>Sayangnya parameter kuncinya kosong. Anda bisa minta kunci untuk membuka formulir dengan salah satu metode berikut:</p>';

				$str .= '<ol>';

				$str .= '<li>';
				$str .= '<form class="form-inline" action="'.GameDev::$baseUrl.'/formulir/kunci" method="post">';
				$str .= '<div class="form-group">';
				$str .= '<label class="sr-only">Email</label>';
				$str .= '<p class="form-control-static">Isikan alamat e-mail Anda&nbsp;</p>';
				$str .= '</div>';
				$str .= '<div class="form-group">';
				$str .= '<label for="txt-email" class="sr-only">E-mail</label>';
				$str .= '<input type="text" class="form-control" name="txt-key-request-email" placeholder="g.freeman@halflife3.tld">';
				$str .= '</div>&nbsp;';
				$str .= '<button type="submit" class="btn btn-primary">Minta kunci</button>';
				$str .= '</form>';
				$str .= '</li>';

				$str .= '<li><p>Ajari kami bikin game :D</p></li>';
				$str .= '</ol>';

				$str .= '<p>Jika Anda berada di halaman ini karena salah masuk, kami sangat menyarankan Anda untuk <a href="'.GameDev::$baseUrl.'/">menengok visualisasi data yang keren</a>.</p>';
				$str .= '</div>';
			} else {
				$strLocations = '';

				$queryProvinces = 'select nid, name from gamedev__location where parent_nid=0 order by serial asc';
				$statProvinces = GameDev::$pdo->prepare($queryProvinces);
				$statProvinces->execute();
				$resultsProvinces = $statProvinces->fetchAll(PDO::FETCH_ASSOC);

				foreach ($resultsProvinces as $value) {
					$parentNid = $value['nid'];
					$strCities = '';
					


					$queryCities = 'select nid, name from gamedev__location where parent_nid=:parent_nid order by name asc';
					$statCities = GameDev::$pdo->prepare($queryCities);
					$statCities->bindParam(':parent_nid', $parentNid);
					$statCities->execute();
					$resultsCities = $statCities->fetchAll(PDO::FETCH_ASSOC);


					foreach ($resultsCities as $city) {
						$strCities .= '<option value="'.$city['nid'].'">'.$city['name'].'</option>';
					}

					$yearEnd = (int)date('Y');
					$yearStart = $yearEnd - 15;
					$strYears = '';

					for ($i = $yearStart; $i <= $yearEnd; $i++) {
						$strYears .= '<option value="'.$i.'">'.$i.'</option>';
					}

					$strLocations .= '<optgroup label="'.$value['name'].'">'.$strCities.'</optgroup>';
				}

				$strNumPersonnels = '';
				for ($i = 1; $i <= 30; $i++) {
					$strNumPersonnels .= '<option value="'.$i.'">'.$i.'</option>';
				}

				$academicLevels = '';
				foreach (GameDev::$arrAcademics as $key => $value) {
					$academicLevels .= '<option value="'.$key.'">lulus '.$value.'</option>';
				}

				$pubs = '';
				foreach (GameDev::$arrPublications as $key => $value) {
					$pubs .= '<div class="checkbox"><label><input type="checkbox" value="'.$key.'" name="publications[]">'.$value.'</label></div>';
				}


				// jumbrotron
				$str .= '<div class="jumbotron"><div class="container"><h1>Pemetaan Pengembang Permainan Elektronik Indonesia oleh Harian Kompas</h1><p>Harian Kompas bermaksud mengulas kondisi terkini industri game nasional sehingga sangat membutuhkan bantuan dari teman-teman pengembang untuk bisa menggambarkan hal tersebut. Beberapa poin yang akan diulas seperti persebaran per wilayah, produk yang dihasilkan, dan gambaran dari angkatan kerja yang diserap.</p><p>Besar harapan data ini bisa dinikmati teman-teman kembali menjadi artikel ataupun infografis yang lebih membantu di masa mendatang.</p><p>Terima kasih atas bantuannya.</p><p><a href="https://twitter.com/eldidito">Didit Putra</a></p></div></div>';

				$str .= '<div class="container">';
				$str .= '<div class="row">';
				$str .= '<div class="col-md-8">';

				// the form
				$str .= '<form id="the-survey" action="'.GameDev::$baseUrl.'/formulir/post" method="post">';

				// studio name
				$str .= '<div class="form-group required"><label class="control-label" for="txt-studio-name">Nama Studio</label><input id="txt-studio-name" name="txt-studio-name" class="form-control" type="text" pattern="[a-zA-Z\s]{1,255}" placeholder="Nama studio Anda" maxlength="255"></div>';
				// studio url
				$str .= '<div class="form-group"><label class="control-label" for="txt-studio-url">Situs Studio</label><input id="txt-studio-url" name="txt-studio-url" class="form-control" type="text" placeholder="Alamat situs studio Anda" maxlength="255" value="http://"></div>';
				//studio location
				$str .= '<div class="form-group required">';
				$str .= '<label class="control-label" for="txt-studio-location">Lokasi Studio</label>';
				$str .= '<select class="form-control" id="txt-studio-location" name="txt-studio-location">';
				$str .= '<option value="">Kota/kabupaten domisili</option>';
				$str .= $strLocations;
				$str .= '</select>';
				$str .= '</div>';
				// studio start year
				$str .= '<div class="form-group required"><label class="control-label" for="txt-studio-start">Tahun Beroperasi</label><select class="form-control" id="txt-studio-start" name="txt-studio-start">'.$strYears.'</select></div>';
				// studio workers
				$str .= '<div class="form-group required"><label class="control-label" for="txt-studio-personnels">Anggota Tetap Tim</label><div id="team-members" class="row"><div class="col-xs-5"><div class="form-group"><select class="form-control" id="txt-studio-personnels" name="personnels[number][]">'.$strNumPersonnels.'</select></div></div><div class="col-xs-6"><div class="form-group"><select class="form-control" name="personnels[edu][]">'.$academicLevels.'</select></div></div></div><div class="row"><div class="col-md-12 txt-right"><a id="btn-add-personnels" href="#">Tambah personel</a></div></div></div>';
				// studio products
				$str .= '<div class="form-group required"><div id="products"><div class="row"><div class="col-md-4 col-xs-6"><div class="form-group"><label class="control-label" for="txt-studio-products">Karya</label><input id="txt-studio-products" class="form-control" type="text" placeholder="Judul karya" name="products[name][]" maxlength="255" value=""></div></div><div class="col-md-3 col-xs-6"><div class="form-group"><label class="control-label">Tahun terbit</label><select class="form-control" name="products[year][]">'.$strYears.'</select></div></div><div class="col-md-4 col-xs-11"><div class="form-group"><label class="control-label">Platform</label><div class="checkbox"><label class="checkbox-inline"><input type="checkbox" name="products[platform][0][]" value="desktop">Desktop</label><label class="checkbox-inline"><input type="checkbox" name="products[platform][0][]" value="mobile">Mobile</label></div></div></div></div></div><div class="row"><div class="col-md-12 txt-right"><a id="btn-add-products" href="#">Tambah karya</a></div></div></div>';
				// products publication methods
				$str .= '<div class="form-group required"><label for="" class="control-label">Cara memperkenalkan karya</label>'.$pubs.'</div>';

				// finally, le button
				$str .= '<div class="form-group"><input id="btn-submit" class="btn btn-primary" type="submit" value="Kirim"></div>';

				$str .= '</form>';

				$str .= '</div>'; // .col-md-8
				$str .= '</div>'; // row
				$str .= '</div>'; // .container
			}

			

			$str .= GameDev::get_page_footer('html');

			echo $str;
			unset($str);
		}

		public static function get_studios_directory_page($alphabet = 'a') {
			$alphas = range('a', 'z');
			$navItems = '';

			if (!ctype_alpha($alphabet)) {
				$alphabet = 'a';
			}
			
			foreach ($alphas as $key => $value) {
				$navItems .= ($value === $alphabet) ? '<li class="map-nav-items active">' : '<li class="map-nav-items">';
				$navItems .= '<a class="map-nav-links" href="'.GameDev::$baseUrl.'/direktori/'.$value.'">'.strtoupper($value).'</a>';
				$navItems .= '</li>';
			};

			$strAlpha = $alphabet.'%';

			$query = 'select gamedev__survey_results.id, gamedev__survey_results.studio_name, gamedev__survey_results.studio_url, gamedev__survey_results.products,
					  gamedev__location.name as location_name
					  from gamedev__survey_results
					  left join gamedev__location on gamedev__location.nid = gamedev__survey_results.studio_location
					  where gamedev__survey_results.studio_name like :strAlpha 
					  order by gamedev__survey_results.studio_name asc';
			$stat = GameDev::$pdo->prepare($query);
			$stat->bindParam(':strAlpha', $strAlpha);
			$stat->execute();
			$results = $stat->fetchAll(PDO::FETCH_ASSOC);

			if (!empty($results)) {
				$resultsChunked = array_chunk($results, 4);

				$strDir = '';

				foreach ($resultsChunked as $resultChunked) {
					$strStudio = '';

					foreach ($resultChunked as $value) {
						$products = explode(';', $value['products']);
						$strProducts = '';

						foreach ($products as $productRaw) {
							$product = explode('|', $productRaw);
							$strProducts .= $product[0].' ('.$product[1].')<br>';
						}
						

						$strStudio .= '<div class="col-lg-3 col-md-3 col-sm-6 directory-content-wrapper">';
						$strStudio .= '<div class="col-sm-8">';
						$strStudio .= '<span class="directory-label">Nama</span>';
						$strStudio .= '<p class="directory-txt purple">'.$value['studio_name'].'</p>';
						$strStudio .= !empty($value['studio_url']) ? '<span class="directory-label">URL</span>' : '';
						$strStudio .= !empty($value['studio_url']) ? '<p class="directory-txt"><a href="'.$value['studio_url'].'" target="_blank">'.$value['studio_url'].'</a></p>' : '';
						$strStudio .= '<span class="directory-label">Produk</span>';
						$strStudio .= '<p class="directory-txt">'.substr($strProducts, 0, -4).'</p>';
						$strStudio .= '</div>'; // .col-sm-8
						$strStudio .= '</div>'; // .col-lg-3.col-md-3.col-sm-6.directory-content-wrapper
					}

					$strDir .='<div class="row">'.$strStudio.'</div>'; //.row
				}
			} else {
				$strDir ='<div class="row"><div class="col-lg-12 col-md-12 col-sm-12"><p>Ciluuukk baaa</p></div></div>';
			}

				

			$str = GameDev::get_page_header('html');
			$str .= GameDev::get_page_nav('direktori');


			$str .= '<div class="directory-nav">';
			
			$str .= '<div class="container-fluid">';
			$str .= '<div class="row">';
			$str .= '<div class="col-lg-12 col-md-12 col-sm-12"><h1 class="txt-white">Direktori Pengembang Game Indonesia</h1></div>';
			$str .= '</div>';
			$str .= '</div>'; // .container-fluid

			$str .= '<div class="directory-nav-alphabet">';
			$str .= '<ul id="directory-nav" class="map-nav-alphabet">'.$navItems.'</ul>';
			$str .= '</div>';

			$str .= '</div>'; // .directory-nav

			$str .= '<div class="container-fluid directory-contents">'.$strDir.'</div>';

			// $str .= '</div>';


			$str .= GameDev::get_page_footer('html');

			echo $str;
			unset($str);
		}

		public static function save_users_inputs() {
			$now = date('Y-m-d H:i:s');
			$studioName = GameDev::sanitize_inputs($_POST['txt-studio-name']);
			$studioUrl = (!empty($_POST['txt-studio-url']) && $_POST['txt-studio-url'] !== 'http://') ? GameDev::sanitize_inputs($_POST['txt-studio-url']) : '';
			$studioLocation = GameDev::sanitize_inputs($_POST['txt-studio-location']);
			$studioStart = (int)($_POST['txt-studio-start']);
			$rawPersonnels = $_POST['personnels'];
			$rawProducts = $_POST['products'];
			$rawPublications = $_POST['publications'];

			$personnels = '';
			$personnelCount = 0;
			$products = '';
			$publications = '';

			foreach ($rawPersonnels['number'] as $key => $value) {
				$personnels .= $value.'|'.$rawPersonnels['edu'][$key].';';
				$personnelCount += (int)$value;
			}

			foreach ($rawProducts['name'] as $key => $value) {
				$productName = isset($value) ? GameDev::sanitize_inputs($value) : '';
				$productYear = isset($rawProducts['year'][$key]) ? (int)$rawProducts['year'][$key] : '';
				$productPlatforms = isset($rawProducts['platform'][$key]) ? $rawProducts['platform'][$key] : array();
				$platforms = '';

				if (!empty($productName) && !empty($productYear) && !empty($productPlatforms)) {

					foreach ($productPlatforms as $keyPlatform => $valuePlatform) {
						$platforms .= $valuePlatform.',';
					}

					$products .= $productName.'|'.$productYear.'|'.substr($platforms, 0, -1).';';
				}
			}

			if (!empty($rawPublications)) {
				foreach ($rawPublications as $key => $value) {
					$publications .= $value.';';
				}
			}
				

			$personnels = substr($personnels, 0, -1);
			$products = substr($products, 0, -1);
			$publications = substr($publications, 0, -1);

			if (empty($studioName)) {
				exit('Tiada nama studio');
			}

			if (empty($studioLocation)) {
				exit('Tiada lokasi studio');
			}

			if (!is_numeric($studioStart) || $studioStart <= 0) {
				exit('Tahun studio berdiri tak valid');
			}

			if (empty($personnels)) {
				exit('Tiada anggota tim studio');
			}

			if (empty($products)) {
				exit('Tiada produk. Aneh kan?');
			}

			if (empty($publications)) {
				exit('Tiada publikasi produk');
			}

			//check if studio is already in database
			$queryCheck = 'select count(id) as cc from gamedev__survey_results where studio_name=:studioName';
			$statCheck = GameDev::$pdo->prepare($queryCheck);
			$statCheck->bindParam(':studioName', $studioName, PDO::PARAM_STR);
			$statCheck->execute();
			$resultCheck = $statCheck->fetch(PDO::FETCH_ASSOC);
			// print_r($resultCheck);
			if ($resultCheck['cc'] > 0) {
				exit('Studio '.$studioName.' sudah ada');
			}


			// $query = 'insert into survey_results 
			// 		  (datetime, studio_name, studio_url, studio_location, studio_start, studio_personnels, personnels_educations, products, publications)
			// 		  values 
			// 		  (:now, :studioName, :studioUrl, :studioLocation, :studioStart, :personnelCount, :personnels, :products, :publications)';

			// $stat = GameDev::$pdo->prepare($query);
			// $stat->bindParam(':now', $now);
			// $stat->bindParam(':studioName', $studioName);
			// $stat->bindParam(':studioUrl', $studioUrl);
			// $stat->bindParam(':studioLocation', $studioLocation);
			// $stat->bindParam(':studioStart', $studioStart);
			// $stat->bindParam(':personnelCount', $personnelCount);
			// $stat->bindParam(':personnels', $personnels);
			// $stat->bindParam(':products', $products);
			// $stat->bindParam(':publications', $publications);
			// $stat->execute();

			header('Location: '.GameDev::$baseUrl);
			exit;

			print_r($studioName."\r\n");
			print_r($studioUrl."\r\n");
			print_r($studioLocation."\r\n");
			print_r($rawPersonnels);
			print_r($personnels."\r\n");
			print_r($personnelCount."\r\n");
			print_r($rawProducts);
			print_r($products);
			print_r($rawPublications);
			print_r($publications);
		}

		public static function save_users_key_request() {
			$emailRaw = trim($_POST['txt-key-request-email']);
			$email = filter_var($emailRaw, FILTER_SANITIZE_EMAIL);

			if (empty($email)) {
				$strResponse = '<div class="bg-warning message-box">Alamat e-mail kosong. Kami memerlukan alamat e-mail Anda untuk memvalidasi data. <a href="'.GameDev::$baseUrl.'/formulir">Kembali</a></div>';
			} else {
				if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
					
					// check if e-mail is already registered
					$queryCheck = 'select count(id) as num from gamedev__keys where email=:email';
					$statCheck = GameDev::$pdo->prepare($queryCheck);
					$statCheck->bindParam(':email', $email);
					$statCheck->execute();
					$resultCheck = $statCheck->fetch(PDO::FETCH_ASSOC);

					if ($resultCheck['num'] === 0) {
						// e-mail is clear, save now
						$dtNow = date('Y-m-d H:i:s');
						$uniqueKey = md5($email.time());
						$isActive = 1;
						$querySave = 'insert into gamedev__keys (email, form_key, dt_request, is_active) values (:email, :form_key, :dt_request, :is_active)';
						$statSave = GameDev::$pdo->prepare($querySave);
						$statSave->bindParam(':email', $email);
						$statSave->bindParam(':form_key', $uniqueKey);
						$statSave->bindParam(':dt_request', $dtNow);
						$statSave->bindParam(':is_active', $isActive);
						$statSave->execute();

						$strResponse = '<div class="bg-success message-box">Alamat e-mail Anda telah terdaftar. <a href="'.GameDev::$baseUrl.'/formulir/'.$uniqueKey.'">Lanjutkan ke formulir</a>.</div>';
					} else {
						$strResponse = '<div class="bg-warning message-box">Alamat e-mail Anda, '.$email.', sudah didaftarkan. Kami tak bisa melanjutkan pemrosesan permintaan Anda. <a href="'.GameDev::$baseUrl.'/formulir">Kembali</a>.</div>';
					}

					
				} else {
					$strResponse = '<div class="bg-warning message-box">Alamat e-mail Anda, '.$email.', tak valid. <a href="'.GameDev::$baseUrl.'/formulir">Mohon cek kembali</a>. Kami memerlukan alamat e-mail Anda untuk memvalidasi data.</div>';
				}
					
			}

			$str = GameDev::get_page_header('html');
			$str .= GameDev::get_page_nav('formulir');
			
			$str .= '<div class="container">';
			$str .= '<div class="row">';
			$str .= '<div class="col-md-6 col-md-offset-3">';
			$str .= $strResponse;
			$str .= '</div>'; // .col-md-6.col-md-offset-3
			$str .= '</div>'; // .row
			$str .= '</div>'; // .container

			$str .= GameDev::get_page_footer('html');

			echo $str;
			unset($str);
		}

		private static function get_page_header($type = 'html') {
			if ($type === 'html') {
				$str = '<!DOCTYPE html>';
				$str .= '<html>';
				$str .= '<head>';
				$str .= '<meta charset="UTF-8">';
				$str .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
				$str .= '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">';
				$str .= '<meta name="keywords" content="Kompas Print">';
				$str .= '<meta name="description" content="Pemetaan pengembang permainan elektronik Indonesia merupakan inisiatif dari harian Kompas untuk mendata populasi studio pengembang permainan elektronik interaktif di Tanah Air dengan masukan dari para pengembang.">';
				$str .= '<meta name="author" content="Didit Putra Erlangga dan Yosef Yudha Wijaya">';
				$str .= '<meta name="apple-mobile-web-app-capable" content="yes">';
				$str .= '<meta name="format-detection" content="telephone=no">';
				$str .= '<title>Pemetaan Game Developer Indonesia oleh Harian Kompas</title>';
				$str .= '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">';
				$str .= '<link rel="stylesheet" href="'.GameDev::$baseUrl.'/css/style.css">';
				$str .= '</head>';
				$str .= '<body>';
			} else if ($type === 'json') {
				header('content-type: application/json; charset: utf-8');
			}
			
			return $str;
		}

		private static function get_page_footer($type = 'html') {
			if ($type === 'html') {
				$str = '<footer>';
				$str .= '<div class="container-fluid footer">';
				$str .= '<div class="col-md-12 txt-right">';
				$str .= '<span class="footer-span">'.date('Y').' PT Kompas Media Nusantara | <a href="http://id.infografik.print.kompas.com/gamedev/api" target="_blank">API</a> </span>';
				$str .= '<a class="link-ico-32" href="https://github.com/harian-kompas/gamedev-survey" target="_blank" title="Hayuk berkontribusi untuk repositori ini :D"><img src="'.GameDev::$baseUrl.'/img/GitHub-Mark-32px.png"></a>';
				$str .= '</div>';
				// $str .= 'Sumber data nama daerah: <a href="http://data.go.id/dataset/daftar-nama-daerah" target="_blank">data.go.id</a>';
				$str .= '</div>';
				$str .= '</footer>';
				$str .= '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>';
				$str .= '<script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>';
				$str .= '<script type="text/javascript" src="https://www.google.com/jsapi"></script>';
				$str .= '<script type="text/javascript" src="'.GameDev::$baseUrl.'/js/markerclusterer.js"></script>';
				$str .= '<script type="text/javascript" src="'.GameDev::$baseUrl.'/js/gamedev.js"></script>';
				$str .= '</body>';
				$str .= '</html>';
			}
				

			return $str;
		}

		private static function get_page_nav($page = '') {
			$navItems = '';
			// print_r();
			foreach (GameDev::$arrNav as $value) {
				$isActive = ($page === strtolower($value)) ? ' class="active"' : '';

				$navItems .= '<li'.$isActive.'><a href="'.GameDev::$baseUrl.'/'.strtolower($value).'" target="'.$target.'">'.$value.'</a></li>';
			}

			$str = '<nav class="navbar navbar-inverse navbar-fixed-top">';
			$str .= '<div class="container-fluid">';
			$str .= '<div class="navbar-header">';
			$str .= '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">';
			$str .= '<span class="sr-only">Toggle navigation</span>';
			$str .= '<span class="icon-bar"></span>';
			$str .= '<span class="icon-bar"></span>';
			$str .= '<span class="icon-bar"></span>';
			$str .= '</button>';
			$str .= '<a class="navbar-brand" href="'.GameDev::$baseUrl.'/">Pemetaan Game Developer</a>';
			$str .= '</div>';

			$str .= '<div id="navbar" class="navbar-collapse collapse">';
			$str .= '<ul class="nav navbar-nav navbar-right">'.$navItems.'</ul>';
			$str .= '</div>';
			$str .= '</div>';
			$str .= '</nav>';

			return $str;
		}

		private static function sanitize_inputs($str) {
			$str = trim($str);
			$str = strip_tags($str);
			if(get_magic_quotes_gpc()) {
				$str = stripslashes($str);
			}

			return $str;
		}

		public function __destruct() {
			GameDev::$pdo = null;
		}
	}
?>