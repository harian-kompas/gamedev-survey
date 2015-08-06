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
						# code...
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
			$yearStart = 2000;
			$yearEnd = (int)date('Y');
			$yearItems = '';

			for($i = $yearStart; $i <= $yearEnd; $i++) {
				$yearItems .= ($i === $yearEnd) ? '<option value="'.$i.'" selected="selected">'.$i.'</option>' : '<option value="'.$i.'">'.$i.'</option>';
			}


			$str = '<div class="container">';

			$str .= '<div class="col-md-8">';
			
			$str .= '<form action="" method="post">';
			
			//studio name
			$str .= '<div class="form-group required">';
			$str .= '<label class="control-label" for="txt-studio-name">Nama Studio</label>';
			$str .= '<input id="txt-studio-name" class="form-control" type="text" placeholder="Nama studio Anda" maxlength="255">';
			$str .= '</div>';

			//studio url
			$str .= '<div class="form-group">';
			$str .= '<label class="control-label" for="txt-studio-url">Situs Studio</label>';
			$str .= '<input id="txt-studio-url" class="form-control" type="text" placeholder="Alamat situs studio Anda" maxlength="255" value="http://">';
			$str .= '</div>';

			//studio location
			$str .= '<div class="form-group required">';
			$str .= '<label class="control-label" for="txt-studio-location">Lokasi Studio</label>';
			$str .= '<select class="form-control" id="txt-studio-location">';
			$str .= '<option value="">Kota/kabupaten domisili</option>';
			$str .= $optGroup;
			$str .= '</optgroup>';
			$str .= '</select>';
			$str .= '</div>';
			
			// studio start year
			$str .= '<div class="form-group required">';
			$str .= '<label class="control-label" for="txt-studio-start">Tahun Beroperasi</label>';
			$str .= '<select class="form-control" id="txt-studio-start">'.$yearItems.'</select>';
			$str .= '</div>';
			
			// team members
			$str .= '<div class="form-group required">';
			$str .= '<label class="control-label">Anggota Tim</label>';
			$str .= '<div id="team-members" class="row">';

			for ($i=1; $i <= 20; $i++) {
				$numPersonnels .= '<option value="'.$i.'">'.$i.' orang</option>';
			}			
			$str .= '<div class="col-md-6">';
			$str .= '<div class="form-group">';
			$str .= '<select class="form-control" id="txt-studio-personnels">'.$numPersonnels.'</select>';
			$str .= '</div>';
			$str .= '</div>';

			foreach (GameDev::$arrAcademics as $key => $value) {
				$academicLevels .= '<option value="'.$key.'">lulus '.$value.'</option>';
			}

			$str .= '<div class="col-md-6">';
			$str .= '<div class="form-group">';
			$str .= '<select class="form-control">'.$academicLevels.'</select>';
			$str .= '</div>';
			$str .= '</div>';

			$str .= '</div>';

			$str .= '<div class="row"><div class="col-md-12 txt-right"><a id="btn-add-personnels" href="#">Tambah personel</a></div></div>';

			$str .= '</div>';

			// products
			$str .= '<div class="form-group required">';
			$str .= '<label class="control-label" for="txt-studio-products">Karya</label>';
					
			$str .= '<div class="row">';
			
			$str .= '<div class="col-md-4">';
			$str .= '<div class="form-group">';
			$str .= '<input id="txt-studio-products" class="form-control" type="text" placeholder="Judul karya" maxlength="255" value="">';
			$str .= '</div>';
			$str .= '</div>';
			
			$str .= '<div class="col-md-3">';
			$str .= '<div class="form-group">';
			$str .= '<select class="form-control" id="txt-studio-personnels">';
			$str .= '<option value="">Tahun terbit</option>';
			$str .= $yearItems;
			$str .= '</select>';
			$str .= '</div>';
			$str .= '</div>';
			
			$str .= '<div class="col-md-5">';
			$str .= '<label class="checkbox-inline">';
			$str .= '<input type="checkbox" id="inlineCheckbox1" value="option1">Desktop';
			$str .= '</label>';
			$str .= '<label class="checkbox-inline">';
			$str .= '<input type="checkbox" id="inlineCheckbox2" value="option2">Mobile';
			$str .= '</label>';
			$str .= '</div>';

			$str .= '</div>';

			
			$str .= '<div class="row"><div class="col-md-8"></div><div class="col-md-4 txt-right"><a href="#">Tambah karya</a></div></div>';

			$str .= '</div>';

			// publications
			foreach (GameDev::$arrPublications as $key => $value) {
				$pubs .= '<div class="checkbox"><label><input type="checkbox" value="'.$key.'">'.$value.'</label></div>';
			}

			$str .= '<div class="form-group required">';
			$str .= '<label for="" class="control-label">Cara memperkenalkan karya</label>';
			$str .= $pubs;
			$str .= '</div>';
			
			// submit button
			$str .= '<div class="form-group"><input class="btn btn-primary" type="submit" value="Kirim"></div>';

			$str .= '</form>';
			
			$str .= '</div>'; // .col-md-8
			
			$str .= '</div>'; // .container

			return $str;
		}

		public function __destruct() {
			GameDev::$pdo = null;
		}
	}
?>