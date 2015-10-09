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

			} catch(PDOException $e) {
				echo $e->getMessage();
			}
		}



		public function __destruct() {
			GameDev::$pdo = null;
		}
	}
?>