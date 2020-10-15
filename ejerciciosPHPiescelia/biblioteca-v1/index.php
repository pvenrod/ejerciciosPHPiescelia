<!-- BIBLIOTECA VERSIÓN 1

     Características de esta versión:
       - Código monolítico (sin arquitectura MVC)
       - Sin seguridad
       - Sin sesiones ni control de acceso
       - Sin reutilización de código
-->


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  </head>
  <body>
    <?php

    $db = new mysqli("localhost:3306","root","bitnami", "biblioteca");

    if (isset($_REQUEST["action"])) {
    	$action = $_REQUEST["action"];
    } else {
	    $action = "mostrarListaLibros";  // Acción por defecto
    }

    switch($action) {

		case "formularioLogin":

		// --------------------------------- MOSTRAR LISTA DE LIBROS ----------------------------------------
			
        case "mostrarListaLibros":
			echo "<h1>Biblioteca</h1>";

			// Buscamos todos los libros de la biblioteca
            if ($result = $db->query("SELECT * FROM libros
					INNER JOIN escriben ON libros.idLibro = escriben.idLibro
					INNER JOIN personas ON escriben.idPersona = personas.idPersona
					ORDER BY libros.titulo")) {
				
				// La consulta se ha ejecutado con éxito. Vamos a ver si contiene registros
                if ($result->num_rows != 0) {            
					// La consulta ha devuelto registros: vamos a mostrarlos
                    
					// Primero, el formulario de búsqueda
					echo "<form action='index.php'>
								<input type='hidden' name='action' value='buscarLibros'>
                            	<input type='text' name='textoBusqueda'>
								<input type='submit' value='Buscar'>
                          </form><br>";
                
					// Ahora, la tabla con los datos de los libros
                    echo "<table border ='1'>";
                    while ($fila = $result->fetch_object()) {
                        echo "<tr>";
                        echo "<td>".$fila->titulo."</td>";
                        echo "<td>".$fila->genero."</td>";
                        echo "<td>".$fila->numPaginas."</td>";
		        		echo "<td>".$fila->nombre."</td>";
		        		echo "<td>".$fila->apellido."</td>";
                        echo "<td><a href='index.php?action=formularioModificarLibro&idLibro=".$fila->idLibro."'>Modificar</a></td>";
                        echo "<td><a href='index.php?action=borrarLibro&idLibro=".$fila->idLibro."'>Borrar</a></td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
					// La consulta no contiene registros
                    echo "No se encontraron datos";
                }
	        } else {
				// La consulta ha fallado
				echo "Error al tratar de recuperar los datos de la base de datos. Por favor, inténtelo más tarde";
			}
            echo "<p><a href='index.php?action=formularioInsertarLibros'>Nuevo</a></p>";
   	        break;

		// --------------------------------- FORMULARIO ALTA DE LIBROS ----------------------------------------
			
   	 	case "formularioInsertarLibros":
			echo "<h1>Modificación de libros</h1>";

			// Creamos el formulario con los campos del libro
			echo "<form action = 'index.php' method = 'get'>
                    Título:<input type='text' name='titulo'><br>
                    Género:<input type='text' name='genero'><br>
                    País:<input type='text' name='pais'><br>
                    Año:<input type='text' name='ano'><br>
                    Número de páginas:<input type='text' name='numPaginas'><br>";
		    
					// Añadimos un selector para el id del autor o autores
					$result = $db->query("SELECT * FROM personas");
					echo "Autores: <select name='autor[]' multiple size='3'>";
					while ($fila = $result->fetch_object()) {
						echo "<option value='".$fila->idPersona."'>".$fila->nombre." ".$fila->apellido."</option>";
		    }
		    echo "</select>";
			echo "<a href='index.php?action=formularioInsertarAutores'>Añadir nuevo</a><br>";
			
			// Finalizamos el formulario
	    	echo "  <input type='hidden' name='action' value='insertarLibro'>
                    <input type='submit'>
                  </form>";
			echo "<p><a href='index.php'>Volver</a></p>"; 

            break;

		// --------------------------------- INSERTAR LIBROS ----------------------------------------
			
        case "insertarLibro":
			echo "<h1>Alta de libros</h1>";

			// Vamos a procesar el formulario de alta de libros
			// Primero, recuperamos todos los datos del formulario
            $titulo = $_REQUEST["titulo"];
            $genero = $_REQUEST["genero"];
			$pais = $_REQUEST["pais"];
			$ano = $_REQUEST["ano"];
			$numPaginas = $_REQUEST["numPaginas"];
	    	$autores = $_REQUEST["autor"];
			
            // Lanzamos el INSERT contra la BD.
            $db->query("INSERT INTO libros (titulo,genero,pais,ano,numPaginas) VALUES ('$titulo','$genero', '$pais', '$ano', '$numPaginas')");
	    	if ($db->affected_rows == 1) {
				// Si la inserción del libro ha funcionado, continuamos insertando en la tabla "escriben"
				// Tenemos que averiguar qué idLibro se ha asignado al libro que acabamos de insertar
				$result = $db->query("SELECT MAX(idLibro) AS ultimoIdLibro FROM libros");
				$idLibro = $result->fetch_object()->ultimoIdLibro;
				// Ya podemos insertar todos los autores junto con el libro en "escriben"
				foreach ($autores as $idAutor) {
			    	$db->query("INSERT INTO escriben(idLibro, idPersona) VALUES('$idLibro', '$idAutor')");
				}
				echo "Libro insertado con éxito";
			}
	    	else {
				// Si la inserción del libro ha fallado, mostramos mensaje de error
				echo "Ha ocurrido un error al insertar el libro. Por favor, inténtelo más tarde.";
			}
			echo "<p><a href='index.php'>Volver</a></p>"; 

            break;

		// --------------------------------- BORRAR LIBROS ----------------------------------------
			
        case "borrarLibro":
			echo "<h1>Borrar libros</h1>";
			
			// Recuperamos el id del libro y lanzamos el DELETE contra la BD
            $idLibro = $_REQUEST["idLibro"];
            $db->query("DELETE FROM libros WHERE idLibro = '$idLibro'");
			
            // Mostramos mensaje con el resultado de la operación
            if ($db->affected_rows == 0) {
               echo "Ha ocurrido un error al borrar el libro. Por favor, inténtelo de nuevo";
            }
            else {
                echo "Libro borrado con éxito";	
            }
			echo "<p><a href='index.php'>Volver</a></p>"; 

            break;

		// --------------------------------- FORMULARIO MODIFICAR LIBROS ----------------------------------------
			
        case "formularioModificarLibro":
			echo "<h1>Modificación de libros</h1>";

			// Recuperamos el id del libro que vamos a modificar y sacamos el resto de sus datos de la BD
			$idLibro = $_REQUEST["idLibro"];
			$result = $db->query("SELECT * FROM libros WHERE libros.idLibro = '$idLibro'");
			$libro = $result->fetch_object();
			
			// Creamos el formulario con los campos del libro
			// y lo rellenamos con los datos que hemos recuperado de la BD
            echo "<form action = 'index.php' method = 'get'>
				    <input type='hidden' name='idLibro' value='$idLibro'>
                    Título:<input type='text' name='titulo' value='$libro->titulo'><br>
                    Género:<input type='text' name='genero' value='$libro->genero'><br>
                    País:<input type='text' name='pais' value='$libro->pais'><br>
                    Año:<input type='text' name='ano' value='$libro->ano'><br>
                    Número de páginas:<input type='text' name='numPaginas' value='$libro->numPaginas'><br>";
		    
			// Vamos a añadir un selector para el id del autor o autores.
			// Para que salgan preseleccionados los autores del libro que estamos modificando, vamos a buscar
			// también a esos autores.
		    $todosLosAutores = $db->query("SELECT * FROM personas");  // Obtener todos los autores
			$autoresLibro = $db->query("SELECT personas.idPersona FROM libros
					INNER JOIN escriben ON libros.idLibro = escriben.idLibro
					INNER JOIN personas ON escriben.idPersona = personas.idPersona
					WHERE libros.idLibro = '$idLibro'"); 			// Obtener solo los autores del libro que estamos buscando
			// Vamos a convertir esa lista de autores del libro en un array de ids de personas
			$listaAutoresLibro = array();
			while ($autor = $autoresLibro->fetch_object()) {
				$listaAutoresLibro[] = $autor->idPersona;
			}
			
			// Ya tenemos todos los datos para añadir el selector de autores al formulario
		    echo "Autores: <select name='autor[]' multiple size='3'>";
		    while ($fila = $todosLosAutores->fetch_object()) {
				if (in_array($fila->idPersona, $listaAutoresLibro))
					echo "<option value='$fila->idPersona' selected>$fila->nombre $fila->apellido</option>";
				else
					echo "<option value='$fila->idPersona'>$fila->nombre $fila->apellido</option>";
		    }
		    echo "</select>";
			
			// Por último, un enlace para crear un nuevo autor
			echo "<a href='index.php?action=formularioInsertarAutores'>Añadir nuevo</a><br>";
			
			// Finalizamos el formulario
	    	echo "  <input type='hidden' name='action' value='modificarLibro'>
                    <input type='submit'>
                  </form>";
			echo "<p><a href='index.php'>Volver</a></p>"; 

            break;

		// --------------------------------- MODIFICAR LIBROS ----------------------------------------
			
        case "modificarLibro":
			echo "<h1>Modificación de libros</h1>";

			// Vamos a procesar el formulario de modificación de libros
			// Primero, recuperamos todos los datos del formulario
			$idLibro = $_REQUEST["idLibro"];
            $titulo = $_REQUEST["titulo"];
            $genero = $_REQUEST["genero"];
			$pais = $_REQUEST["pais"];
			$ano = $_REQUEST["ano"];
			$numPaginas = $_REQUEST["numPaginas"];
	    	$autores = $_REQUEST["autor"];
			
            // Lanzamos el UPDATE contra la base de datos.
            $db->query("UPDATE libros SET
							titulo = '$titulo',
							genero = '$genero',
							pais = '$pais',
							ano = '$ano',
							numPaginas = '$numPaginas'
							WHERE idLibro = '$idLibro'");
			
	    	if ($db->affected_rows == 1) {
				// Si la modificación del libro ha funcionado, continuamos actualizando la tabla "escriben".
				// Primero borraremos todos los registros del libro actual y luego los insertaremos de nuevo
				$db->query("DELETE FROM escriben WHERE idLibro = '$idLibro'");
				// Ya podemos insertar todos los autores junto con el libro en "escriben"
				foreach ($autores as $idAutor) {
			    	$db->query("INSERT INTO escriben(idLibro, idPersona) VALUES('$idLibro', '$idAutor')");
				}
				echo "Libro actualizado con éxito";
			}
	    	else {
				// Si la modificación del libro ha fallado, mostramos mensaje de error
				echo "Ha ocurrido un error al modificar el libro. Por favor, inténtelo más tarde.";
			}
			echo "<p><a href='index.php'>Volver</a></p>"; 
            break;
		
		// --------------------------------- BUSCAR LIBROS ----------------------------------------
			
        case "buscarLibros":
			// Recuperamos el texto de búsqueda de la variable de formulario
			$textoBusqueda = $_REQUEST["textoBusqueda"];
			echo "<h1>Resultados de la búsqueda: \"$textoBusqueda\"</h1>";

			// Buscamos los libros de la biblioteca que coincidan con el texto de búsqueda
            if ($result = $db->query("SELECT * FROM libros
					INNER JOIN escriben ON libros.idLibro = escriben.idLibro
					INNER JOIN personas ON escriben.idPersona = personas.idPersona
					WHERE libros.titulo LIKE '%$textoBusqueda%'
					OR libros.genero LIKE '%$textoBusqueda%'
					OR personas.nombre LIKE '%$textoBusqueda%'
					OR personas.apellido LIKE '%$textoBusqueda%'
					ORDER BY libros.titulo")) {
				
				// La consulta se ha ejecutado con éxito. Vamos a ver si contiene registros
                if ($result->num_rows != 0) {            
					// La consulta ha devuelto registros: vamos a mostrarlos
 					// Primero, el formulario de búsqueda
					echo "<form action='index.php'>
								<input type='hidden' name='action' value='buscarLibros'>
                            	<input type='text' name='textoBusqueda'>
								<input type='submit' value='Buscar'>
                          </form><br>";
                   // Después, la tabla con los datos
                   echo "<table border ='1'>";
                    while ($fila = $result->fetch_object()) {
                        echo "<tr>";
                        echo "<td>".$fila->titulo."</td>";
                        echo "<td>".$fila->genero."</td>";
                        echo "<td>".$fila->numPaginas."</td>";
		        		echo "<td>".$fila->nombre."</td>";
		        		echo "<td>".$fila->apellido."</td>";
                        echo "<td><a href='index.php?action=formularioModificarLibro&idLibro=".$fila->idLibro."'>Modificar</a></td>";
                        echo "<td><a href='index.php?action=borrarLibro&idLibro=".$fila->idLibro."'>Borrar</a></td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
					// La consulta no contiene registros
                    echo "No se encontraron datos";
                }
	        } else {
				// La consulta ha fallado
				echo "Error al tratar de recuperar los datos de la base de datos. Por favor, inténtelo más tarde";
			}
            echo "<p><a href='index.php?action=formularioInsertarLibros'>Nuevo</a></p>";
			echo "<p><a href='index.php'>Volver</a></p>";
            break;

		// --------------------------------- ACTION NO ENCONTRADA ----------------------------------------

		default: 
			echo "<h1>Error 404: página no encontrada</h1>";
			echo "<a href='index.php'>Volver</a>";
            break;
        } // switch

    ?>

  </body>
</html>