<?php
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<meta name="keywords" content="Kompas Print">
	<meta name="description" content="Harian Kompas bermaksud mengulas kondisi terkini industri game nasional sehingga sangat membutuhkan bantuan dari teman-teman pengembang untuk bisa menggambarkan hal tersebut. Beberapa poin yang akan diulas seperti persebaran per wilayah, produk yang dihasilkan, dan gambaran dari angkatan kerja yang diserap.">
	<meta name="author" content="Didit Putra Erlanga dan Yosef Yudha Wijaya">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="format-detection" content="telephone=no">
	<title>Pemetaan Game Developer Indonesia oleh Harian Kompas</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<link rel="stylesheet" href="css/style.css">
</head>
<body>
	<nav class="navbar navbar-inverse navbar-fixed-top">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">Pemetaan Game Developer</a>
			</div>

			<div id="navbar" class="navbar-collapse collapse">
				<ul class="nav navbar-nav navbar-right">
					<li><a href="#">Formulir</a></li>
					<li><a href="#">Hasil</a></li>
					<li><a href="#">API</a></li>
				</ul>
			</div>
		</div>
	</nav>

	<div class="jumbotron">
		<div class="container">
			<h1>Pemetaan Game Developer Indonesia oleh Harian Kompas</h1>
			<p>Harian Kompas bermaksud mengulas kondisi terkini industri game nasional sehingga sangat membutuhkan bantuan dari teman-teman pengembang untuk bisa menggambarkan hal tersebut. Beberapa poin yang akan diulas seperti persebaran per wilayah, produk yang dihasilkan, dan gambaran dari angkatan kerja yang diserap.</p>
			<p>Besar harapan data ini bisa dinikmati teman-teman kembali menjadi artikel ataupun infografis yang lebih membantu di masa mendatang.</p>
			<p>Terima kasih atas bantuannya.</p>
			<p><a href="https://twitter.com/eldidito">Didit Putra</a></p>
		</div>
	</div>

	<div class="container">

		<div class="col-md-8">
			<form action="" method="post">
				<div class="form-group required">
					<label class="control-label" for="txt-studio-name">Nama Studio</label>
					<input id="txt-studio-name" class="form-control" type="text" placeholder="Nama studio Anda" maxlength="255">
				</div>

				<div class="form-group">
					<label class="control-label" for="txt-studio-url">Situs Studio</label>
					<input id="txt-studio-url" class="form-control" type="text" placeholder="Alamat situs studio Anda" maxlength="255" value="http://">
				</div>

				<div class="form-group required">
					<label class="control-label" for="txt-studio-location">Lokasi Studio</label>
					<select class="form-control" id="txt-studio-location">
						<optgroup label="Daerah Istimewa Yogyakarta">
							<option value="">mBantul</option>
							<option value="">Gunung Kidul</option>
							<option value="">Kulon Progo</option>
							<option value="">Sleman</option>
							<option value="">Yogyakarta</option>
						</optgroup>
					</select>
				</div>
				
				<div class="form-group required">
					<label class="control-label" for="txt-studio-start">Tahun Beroperasi</label>
					<select class="form-control" id="txt-studio-start">
						<option value="">2015</option>
					</select>
				</div>
				
				<div class="form-group required">
					<label class="control-label" for="txt-studio-personnels">Pendidikan Terakhir Anggota Tim</label>
					<div class="container-fluid">
						<div class="row">
							<div class="col-md-6">
								<select class="form-control" id="txt-studio-personnels">
									<option value="">1 orang</option>
								</select>
							</div>
							<div class="col-md-6">
								<select class="form-control" id="txt-studio-personnels">
									<option value="">lulus S-1</option>
								</select>
							</div>
						</div>
							
					</div>
						
				</div>

			</form>
		</div>
			
	</div>

	<footer>
			
	</footer>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
	<script src="js/gamedev.js"></script>
</body>
</html>