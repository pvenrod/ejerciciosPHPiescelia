<!-- Versión del número secreto SIN VARIABLES DE SESIÓN-->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title></title>
  </head>
  <body>

	<?php
		if (!isset($_REQUEST["numero"])) {
			if (!isset($_REQUEST["aleatorio"])) {
				$intentos = 0;
				$aleatorio = rand(1,100);
			} else {
				$aleatorio = $_REQUEST["aleatorio"];
				$intentos = $_REQUEST["intentos"];
			}
			echo "<form action='05-numero-secreto.php' method='get'>
				Adivina mi número:
				<input type='text' name='numero'><br>
				<input type='hidden' name='aleatorio' value='$aleatorio'>
				<input type='hidden' name='intentos' value='$intentos'>
				<br>				
				<input type='submit'>
				</form>";
		} else {
			$n = $_REQUEST["numero"];
			$aleatorio = $_REQUEST["aleatorio"];
			$intentos = $_REQUEST["intentos"];
			$intentos++;
			echo "Tu número es: $n<br>";
			if ($n > $aleatorio) {
				echo "Mi número es MENOR";
			}
			else if ($n < $aleatorio) {
				echo "Mi número es MAYOR";
			}
			else {
				echo "<p>ENHORABUENA, HAS ACERTADO</p>";
				echo "Has necesitado $intentos intentos";
			}
			echo "<br><a href='05-numero-secreto.php?aleatorio=$aleatorio&intentos=$intentos'>Sigue jugando...</a>";
		}

	?>











  </body>
</html>