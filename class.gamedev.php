<?php
	class GameDev {
		static $pdo, $arrNav, $arrAcademics, $arrPublications;

		public function __construct() {
			try {
				GameDev::$pdo = new PDO('mysql:host='.DB_H.';dbname='.DB_D.';charset=utf8', DB_U, DB_P);
				GameDev::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				GameDev::$pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8');
				GameDev::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

				GameDev::$arrNav = array('Formulir', 'Hasil', 'API');
				GameDev::$arrAcademics = array(
					'menengah' => 'SMA/SMK',
					'd1' => 'D I',
					'd2' => 'D II',
					'd3' => 'D III',
					'd4' => 'D IV',
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

			} catch(PDOException $e) {
				echo $e->getMessage();
			}
		}

		public static function get_api($options = array()) {
			if (!empty($options)) {
				$subpage = $options['subpage'];
				$callback = $options['callback'];
				

				switch ($subpage) {
					case 'akademik':
						$str = array();
						foreach (GameDev::$arrAcademics as $key => $value) {
							$str[] = array(
								'key' => $key,
								'value' =>$value
							);
						}
						break;
					
					default:
						$str = GameDev::get_api_survey_result();
						break;
				}

				$result = json_encode($str);
				$output = (!empty($callback)) ? $callback.'('.$result.');' : $result;
				echo $output;
				unset($result, $output);
			}
		}

		public static function get_page($page = 'formulir') {
			// echo 'asuu';
			$str = '<!DOCTYPE html>';
			$str .= '<html>';
			$str .= GameDev::get_html_header();
			$str .= '<body>';

			$str .= GameDev::get_page_nav($page);
			

			if ($page === 'formulir') {
				$str .= GameDev::get_page_intro();
				$str .= GameDev::get_survey_form();
			} else {
				$str .= 'tiada parameter';
			}

			$str .= GameDev::get_page_footer();

			$str .= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>';
			$str .= '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>';
			$str .= '<script src="js/gamedev.js"></script>';
			$str .= '</body>';
			$str .= '</html>';

			echo $str;
			unset($str);
		}

		public static function save_users_inputs() {
			$now = date('Y-m-d H:i:s');
			$studioName = GameDev::sanitize_inputs($_POST['txt-studio-name']);
			$studioUrl = (!empty($_POST['txt-studio-url']) || $_POST['txt-studio-url'] !== 'http://') ? GameDev::sanitize_inputs($_POST['txt-studio-url']) : '';
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

			foreach ($rawPublications as $key => $value) {
				$publications .= $value.';';
			}

			$personnels = substr($personnels, 0, -1);
			$products = substr($products, 0, -1);
			$publications = substr($publications, 0, -1);

			if (empty($studioName) || empty($studioLocation)) {
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

			$query = 'insert into survey_results 
					  (datetime, studio_name, studio_url, studio_location, studio_start, studio_personnels, personnels_educations, products, publications)
					  values 
					  (:now, :studioName, :studioUrl, :studioLocation, :studioStart, :personnelCount, :personnels, :products, :publications)';

			$stat = GameDev::$pdo->prepare($query);
			$stat->bindParam(':now', $now);
			$stat->bindParam(':studioName', $studioName);
			$stat->bindParam(':studioUrl', $studioUrl);
			$stat->bindParam(':studioLocation', $studioLocation);
			$stat->bindParam(':studioStart', $studioStart);
			$stat->bindParam(':personnelCount', $personnelCount);
			$stat->bindParam(':personnels', $personnels);
			$stat->bindParam(':products', $products);
			$stat->bindParam(':publications', $publications);
			$stat->execute();

			header('Location: index.php');
			exit;

			// print_r($studioName."\r\n");
			// print_r($studioUrl."\r\n");
			// print_r($studioLocation."\r\n");
			// print_r($rawPersonnels);
			// print_r($personnels."\r\n");
			// print_r($personnelCount."\r\n");
			// print_r($rawProducts);
			// print_r($products);
			// print_r($rawPublications);
			// print_r($publications);
		}

		private static function get_api_survey_result() {
			$str = array();
			$query = 'select survey_results.id, survey_results.datetime, survey_results.studio_name, survey_results.studio_url, survey_results.studio_location, survey_results.studio_start, survey_results.studio_personnels, survey_results.personnels_educations, survey_results.products, survey_results.publications,
					  location.nid, location.name as location_name, location.latitude, location.longitude
					  from survey_results
					  left join location on survey_results.studio_location = location.nid
					  order by id asc';
			$stat = GameDev::$pdo->prepare($query);
			$stat->execute();
			
			$results = $stat->fetchAll(PDO::FETCH_ASSOC);

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
						$edu[] = array(
							'num' => (int)$values[0],
							'degree' => $values[1]
						);
					}
				}


				foreach ($studioProducts as $rawProducts) {
					$productData = explode('|', $rawProducts);
					$products[] = array(
						'name' => $productData[0],
						'year' => $productData[1],
						'platform' => $productData[2]
					);
				}

				foreach ($productPublications as $productPublication) {
					$publications[] = GameDev::$arrPublications[$productPublication];
				}


				$str[] = array(
					'id' => $id,
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

				unset($edu, $products);
			}

			return $str;
		}

		private static function get_html_header() {
			$str = '<head>';
			$str .= '<meta charset="UTF-8">';
			$str .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
			$str .= '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">';
			$str .= '<meta name="keywords" content="Kompas Print">';
			$str .= '<meta name="description" content="Harian Kompas bermaksud mengulas kondisi terkini industri game nasional sehingga sangat membutuhkan bantuan dari teman-teman pengembang untuk bisa menggambarkan hal tersebut. Beberapa poin yang akan diulas seperti persebaran per wilayah, produk yang dihasilkan, dan gambaran dari angkatan kerja yang diserap.">';
			$str .= '<meta name="author" content="Didit Putra Erlangga dan Yosef Yudha Wijaya">';
			$str .= '<meta name="apple-mobile-web-app-capable" content="yes">';
			$str .= '<meta name="format-detection" content="telephone=no">';
			$str .= '<title>Pemetaan Game Developer Indonesia oleh Harian Kompas</title>';
			$str .= '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">';
			$str .= '<link rel="stylesheet" href="css/style.css">';
			$str .= '</head>';

			return $str;
		}

		private static function get_page_footer() {
			$str = '<footer>';
			$str .= '<div class="container">';
			$str .= 'Sumber data nama daerah: <a href="http://data.go.id/dataset/daftar-nama-daerah" target="_blank">data.go.id</a>';
			$str .= '</div>';
			$str .= '</footer>';

			return $str;
		}

		private static function get_page_intro() {
			$str = '<div class="jumbotron">';
			$str .= '<div class="container">';
			$str .= '<h1>Pemetaan Game Developer Indonesia oleh Harian Kompas</h1>';
			$str .= '<p>Harian Kompas bermaksud mengulas kondisi terkini industri game nasional sehingga sangat membutuhkan bantuan dari teman-teman pengembang untuk bisa menggambarkan hal tersebut. Beberapa poin yang akan diulas seperti persebaran per wilayah, produk yang dihasilkan, dan gambaran dari angkatan kerja yang diserap.</p>';
			$str .= '<p>Besar harapan data ini bisa dinikmati teman-teman kembali menjadi artikel ataupun infografis yang lebih membantu di masa mendatang.</p>';
			$str .= '<p>Terima kasih atas bantuannya.</p>';
			$str .= '<p><a href="https://twitter.com/eldidito">Didit Putra</a></p>';
			$str .= '</div>';
			$str .= '</div>';

			return $str;
		}

		private static function get_page_nav($page) {
			$navItems = '';

			foreach (GameDev::$arrNav as $value) {
				$isActive = ($page === strtolower($value)) ? ' class="active"' : '';
				$target = (strtolower($value) === 'api') ? '_blank' : '_self';

				$navItems .= '<li'.$isActive.'><a href="index.php?p='.strtolower($value).'" target="'.$target.'">'.$value.'</a></li>';
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
			$str .= '<a class="navbar-brand" href="#">Pemetaan Game Developer</a>';
			$str .= '</div>';

			$str .= '<div id="navbar" class="navbar-collapse collapse">';
			$str .= '<ul class="nav navbar-nav navbar-right">'.$navItems.'</ul>';
			$str .= '</div>';
			$str .= '</div>';
			$str .= '</nav>';

			return $str;
		}

		private static function get_survey_form($options = array()) {
			// get provinces, cities, and municipalities;
			$queryProvinces = 'select nid, name from location where parent_nid=0 order by serial asc';
			$statProvinces = GameDev::$pdo->prepare($queryProvinces);
			$statProvinces->execute();
			$resultsProvinces = $statProvinces->fetchAll(PDO::FETCH_ASSOC);
			$optGroup = '';
			// print_r($resultsProvinces);

			foreach ($resultsProvinces as $resultProvinces) {
				$provinceID = $resultProvinces['nid'];
				$query = 'select nid, name from location where parent_nid=:provinceID order by name asc';
				$stat = GameDev::$pdo->prepare($query);
				$stat->bindParam(':provinceID', $provinceID);
				$stat->execute();
				$results = $stat->fetchAll(PDO::FETCH_ASSOC);
				$optItems = '';

				foreach($results as $result) {
					$optItems .= '<option value="'.$result['nid'].'">'.$result['name'].'</option>';
				}

				$optGroup .= '<optgroup label="'.$resultProvinces['name'].'">'.$optItems.'</optgroup>';
			}

			// studio start year
			
			$yearEnd = (int)date('Y');
			$yearStart = $yearEnd - 15;
			$yearItems = '';

			for($i = $yearStart; $i <= $yearEnd; $i++) {
				$yearItems .= '<option value="'.$i.'">'.$i.'</option>';
			}


			$str = '<div class="container">';

			$str .= '<div class="col-md-8">';
			
			$str .= '<form id="the-survey" action="index.php?p=processForm" method="post">';
			
			//studio name
			$str .= '<div class="form-group required">';
			$str .= '<label class="control-label" for="txt-studio-name">Nama Studio</label>';
			$str .= '<input id="txt-studio-name" name="txt-studio-name" class="form-control" type="text" pattern="[a-zA-Z\s]{1,255}" placeholder="Nama studio Anda" maxlength="255">';
			$str .= '</div>';

			//studio url
			$str .= '<div class="form-group">';
			$str .= '<label class="control-label" for="txt-studio-url">Situs Studio</label>';
			$str .= '<input id="txt-studio-url" name="txt-studio-url" class="form-control" type="text" placeholder="Alamat situs studio Anda" maxlength="255" value="http://">';
			$str .= '</div>';

			//studio location
			$str .= '<div class="form-group required">';
			$str .= '<label class="control-label" for="txt-studio-location">Lokasi Studio</label>';
			$str .= '<select class="form-control" id="txt-studio-location" name="txt-studio-location">';
			$str .= '<option value="">Kota/kabupaten domisili</option>';
			$str .= $optGroup;
			$str .= '</optgroup>';
			$str .= '</select>';
			$str .= '</div>';
			
			// studio start year
			$str .= '<div class="form-group required">';
			$str .= '<label class="control-label" for="txt-studio-start">Tahun Beroperasi</label>';
			$str .= '<select class="form-control" id="txt-studio-start" name="txt-studio-start">'.$yearItems.'</select>';
			$str .= '</div>';
			
			// team members
			$str .= '<div class="form-group required">';
			$str .= '<label class="control-label" for="txt-studio-personnels">Anggota Tim</label>';
			$str .= '<div id="team-members" class="row">';

			for ($i=1; $i <= 20; $i++) {
				$numPersonnels .= '<option value="'.$i.'">'.$i.' orang</option>';
			}			
			$str .= '<div class="col-md-6">';
			$str .= '<div class="form-group">';
			$str .= '<select class="form-control" id="txt-studio-personnels" name="personnels[number][]">'.$numPersonnels.'</select>';
			$str .= '</div>';
			$str .= '</div>';

			foreach (GameDev::$arrAcademics as $key => $value) {
				$academicLevels .= '<option value="'.$key.'">lulus '.$value.'</option>';
			}

			$str .= '<div class="col-md-6">';
			$str .= '<div class="form-group">';
			$str .= '<select class="form-control" name="personnels[edu][]">'.$academicLevels.'</select>';
			$str .= '</div>';
			$str .= '</div>';

			$str .= '</div>';

			$str .= '<div class="row"><div class="col-md-12 txt-right"><a id="btn-add-personnels" href="#">Tambah personel</a></div></div>';

			$str .= '</div>';

			// products
			$str .= '<div class="form-group required">';
			
					
			$str .= '<div id="products">';
			
			$str .= '<div class="row">';
			$str .= '<div class="col-md-4">';
			$str .= '<div class="form-group">';
			$str .= '<label class="control-label" for="txt-studio-products">Karya</label>';
			$str .= '<input id="txt-studio-products" class="form-control" type="text" placeholder="Judul karya" name="products[name][]" maxlength="255" value="">';
			$str .= '</div>';
			$str .= '</div>';
			
			$str .= '<div class="col-md-3">';
			$str .= '<div class="form-group">';
			$str .= '<label class="control-label">Tahun terbit</label>';
			$str .= '<select class="form-control" name="products[year][]">'.$yearItems.'</select>';
			$str .= '</div>';
			$str .= '</div>';
			
			$str .= '<div class="col-md-5">';
			$str .= '<div class="form-group">';
			$str .= '<label class="control-label">Platform</label>';
			$str .= '<div class="checkbox">';
			$str .= '<label class="checkbox-inline">';
			$str .= '<input type="checkbox" name="products[platform][0][]" value="desktop">Desktop';
			$str .= '</label>';
			$str .= '<label class="checkbox-inline">';
			$str .= '<input type="checkbox" name="products[platform][0][]" value="mobile">Mobile';
			$str .= '</label>';
			$str .= '</div>';
			$str .= '</div>';
			$str .= '</div>';

			$str .= '</div>'; // .row
			$str .= '</div>'; // #products
			
			$str .= '<div class="row"><div class="col-md-12 txt-right"><a id="btn-add-products" href="#">Tambah karya</a></div></div>';

			$str .= '</div>';

			// publications
			foreach (GameDev::$arrPublications as $key => $value) {
				$pubs .= '<div class="checkbox"><label><input type="checkbox" value="'.$key.'" name="publications[]">'.$value.'</label></div>';
			}

			$str .= '<div class="form-group required">';
			$str .= '<label for="" class="control-label">Cara memperkenalkan karya</label>';
			$str .= $pubs;
			$str .= '</div>';
			
			// submit button
			$str .= '<div class="form-group"><input id="btn-submit" class="btn btn-primary" type="submit" value="Kirim"></div>';

			$str .= '</form>';
			
			$str .= '</div>'; // .col-md-8
			
			$str .= '</div>'; // .container

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