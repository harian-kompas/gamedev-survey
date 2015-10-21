<?php
	class GameDev {
		static $pdo, $arrNav, $arrAcademics, $arrPublications, $baseUrl;

		public function __construct() {
			try {
				GameDev::$pdo = new PDO('mysql:host='.DB_H.';dbname='.DB_D.';charset=utf8', DB_U, DB_P);
				GameDev::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				GameDev::$pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8');
				GameDev::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

				GameDev::$arrNav = array('Hasil', 'Direktori');
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
			$acaDemicDegrees = array();
			$publicationMethods = array();
			$provinces = array();

			foreach (GameDev::$arrAcademics as $key => $value) {
				$acaDemicDegrees[] = array(
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
			$queryProvinces = 'select nid, name from location where parent_nid=:parent_nid order by serial asc';
			$statProvinces = GameDev::$pdo->prepare($queryProvinces);
			$statProvinces->bindParam(':parent_nid', $parentNid);
			$statProvinces->execute();
			$resultProvinces = $statProvinces->fetchAll(PDO::FETCH_ASSOC);

			foreach ($resultProvinces as $resultProvince) {
				$cities = array();
				$provinceNid = $resultProvince['nid'];
				$queryCities = 'select nid, name from location where parent_nid=:province_nid order by serial asc';
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
				'acaDemicDegrees' => $acaDemicDegrees,
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
			$query = 'select survey_results.id, survey_results.datetime, survey_results.studio_name, survey_results.studio_url, survey_results.studio_location, survey_results.studio_start, survey_results.studio_personnels, survey_results.personnels_educations, survey_results.products, survey_results.publications,
					  location.nid, location.name as location_name, location.latitude, location.longitude
					  from survey_results
					  left join location on survey_results.studio_location = location.nid
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
			$str .= '<h3>Persebaran Industri dari Tahun ke Tahun</h3>';
			$str .= '<ul id="map-nav" class="map-nav"></ul>';
			$str .= '<div id="map" class="map-canvas"></div>';
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

		public static function get_studios_directory_page($alphabet = 'a') {
			$alphas = range('a', 'z');
			$navItems = '';
			
			foreach ($alphas as $key => $value) {
				$navItems .= ($value === $alphabet) ? '<li class="map-nav-items btn btn-default active">' : '<li class="map-nav-items btn btn-default">';
				$navItems .= '<a class="map-nav-links" href="'.GameDev::$baseUrl.'/direktori/'.$value.'">'.strtoupper($value).'</a>';
				$navItems .= '</li>';
			};

			$str = GameDev::get_page_header('html');
			$str .= GameDev::get_page_nav('direktori');

			$str .= '<div class="container-fluid">';

			$str .= '<div class="row">';
			$str .= '<div class="col-lg-12 col-md-12 col-sm-12">';
			$str .= '<h1>Direktori Pengembang Game Indonesia</h1>';
			$str .= '</div>';
			$str .= '</div>';

			$str .= '<div class="row">';
			$str .= '<div class="col-lg-12 col-md-12 col-sm-12">';
			$str .= '<ul id="directory-nav" class="map-nav">'.$navItems.'</ul>';
			$str .= '</div>';
			$str .= '</div>';

			$str .= '<div class="row">';

			$str .= '</div>';

			$str .= '</div>';


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
				$str .= '<meta name="description" content="Harian Kompas bermaksud mengulas kondisi terkini industri game nasional sehingga sangat membutuhkan bantuan dari teman-teman pengembang untuk bisa menggambarkan hal tersebut. Beberapa poin yang akan diulas seperti persebaran per wilayah, produk yang dihasilkan, dan gambaran dari angkatan kerja yang diserap.">';
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
				$str .= '<span class="footer-span">'.date('Y').' PT Kompas Media Nusantara</span>';
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

		public function __destruct() {
			GameDev::$pdo = null;
		}
	}
?>