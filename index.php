<?php
ini_set('display_errors',1);
require_once(__DIR__.'/../../shared/config/opsworks.php');
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Simple PHP App</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="assets/css/bootstrap.min.css" rel="stylesheet">
        <style>body {margin-top: 40px; background-color: #333;}</style>
        <link href="assets/css/bootstrap-responsive.min.css" rel="stylesheet">
        <!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    </head>

    <body>
        <div class="container">
            <div class="hero-unit">
                <h1>Simple PHP App</h1>
                <h2>Congratulations!</h2>
                <p>Your PHP application is now running on the host &ldquo;<?php echo gethostname(); ?>&rdquo; in your own dedicated environment in the AWS&nbsp;Cloud.</p>
                <p>This host is running PHP version <?php echo phpversion(); ?>.</p>
            </div>
        </div>
		<div class="container">
			<div class="hero-unit">
				<h1>OpsWork Configuration</h1>
				<p>This configurations are provided by the OpsWork PHP Config recipe (<a href="https://github.com/aws/opsworks-cookbooks/blob/master-chef-11.4/php/recipes/configure.rb">here</a>)</a></p>
				<h2>Database</h2>
				<?php
					$DataBase = new OpsWorksDb();
					print_r($DataBase);
				?>
				<h2>Layers:</h2>
				<?php
				$OpsWorks = new OpsWorks();
				foreach ($OpsWorks->layers() as $layer) {
					echo '<h3>'.$layer.'</h3>';
					var_dump($OpsWorks->hosts($layer));
				}
				?>
			</div>
		</div>
		<div class="container">
			<div class="hero-unit">
				<h1>Now this script produces some CPU load</h1>
				<p>This is good for demonstrating the auto scaling :-)</p>
				<?php
					$key = pack('H*', "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3");
					$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
					$plaintext = "Hello Opswork ".date('r');
					$encrypted = encrypt($plaintext,$iv_size,$key);
					echo 'Encrypted: '.$encrypted;
					echo '<hr>Decrypted: '.decrypt($encrypted,$iv_size,$key);
					//some random encryption
					for ($i=0;$i<1000;$i++) {
						$plaintext = rand(0,99999999);
						$encrypted = encrypt($plaintext,$iv_size,$key);
						decrypt($encrypted,$iv_size,$key);
					}
				?>
			</div>
		</div>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
    </body>

</html>
<?php

function encrypt($plaintext,$iv_size,$key) {

	# create a random IV to use with CBC encoding
	$iv_size =
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

	# creates a cipher text compatible with AES (Rijndael block size = 128)
	# to keep the text confidential
	# only suitable for encoded input that never ends with value 00h
	# (because of default zero padding)
	$ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key,
		$plaintext, MCRYPT_MODE_CBC, $iv);

	# prepend the IV for it to be available for decryption
	$ciphertext = $iv . $ciphertext;

	# encode the resulting cipher text so it can be represented by a string
	$ciphertext_base64 = base64_encode($ciphertext);

	return  $ciphertext_base64;
}

function decrypt($ciphertext_base64,$iv_size,$key) {
	$ciphertext_dec = base64_decode($ciphertext_base64);

	# retrieves the IV, iv_size should be created using mcrypt_get_iv_size()
	$iv_dec = substr($ciphertext_dec, 0, $iv_size);

	# retrieves the cipher text (everything except the $iv_size in the front)
	$ciphertext_dec = substr($ciphertext_dec, $iv_size);

	# may remove 00h valued characters from end of plain text
	$plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key,
		$ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);

	return  $plaintext_dec;
}
