<!DOCTYPE html>
<html>
<head>
	<title>Universitas Gadjah Mada</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<link href="https://fonts.googleapis.com/css?family=Lora:400,400i,700|Montserrat:400,700|Open+Sans:400,400i,700,700i" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="<?php echo UGM_THEME_URI_ASSETS ?>/css/style.css">
</head>
<body id="maintenance">
	<div class="box-info">
		<div class="info-img"><img src="<?php echo UGM_THEME_URI_ASSETS ?>/images/ugm_blue.png" alt="Universitas Gadjah Mada"></div>
		<h1><?php if (!empty($heading)) { echo stripslashes($heading); } ?></h1>
		<?php if (!empty($text)) { echo stripslashes($text); } ?>
	</div>
</body>
</html>