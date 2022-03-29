<html>

<head>
<title>Inseguro</title>
</head>
<body>
<?php include('conf.php');?>
<p></p>

<form action="tables.php" method="get">
	<input type="text" name="search" placeholder="Buscar obras de arte por ID...">
	<input type="submit">
</form>

<?php
//Estableciendo conexión con la base de datos
$dbb = conectar();

//Guardando input del usuario
$search = $_GET['search'];

//Utilizando el input para realizar una consulta
$sql = "SELECT * FROM galeria WHERE id = {$search} AND estado = 'publica'";

//Enviando consulta y guardando el resultado
$result = $dbb->query($sql);

//Mostrando el resultado de la consulta
if ($result) {
	if (mysqli_num_rows($result) > 0) {
		echo "<table>";
		echo "<tr>";
		echo "<th>Id</th>";
		echo "<th>Nombre</th>";
		echo "<th>Obra</th>";
		echo "<th>Autor</th>";
		echo "<th>Estado</th>";
		echo "</tr>";
		while ($row = mysqli_fetch_array($result)) {
			echo "<tr>";
			echo "<td>".$row['id']."</td>";
			echo "<td>".$row['nombre']."</td>";
			echo '<td>' . '<img src = "data:image/png;base64,' . base64_encode($row['obra']) . '" width = "50px" height = "50px"/>' . '</td>';
			echo "<td>".$row['autor']."</td>";
			echo "<td>".$row['estado']."</td>";
			echo "</tr>";
		}
		echo "</table>";
                echo "<br>";
                echo "Búsqueda:" . " $search";
		mysqli_free_res($result);
	}
	else {
		echo "La obra de arte es privada o no está disponible.";
                echo "<br><br>";
                echo "Búsqueda:" . " $search";
	}
}
else {
	echo "ERROR: Algo salió terriblemente mal. El error es el siguiente: {$sql}" .mysqli_error($dbb);
}
$dbb->close();
?>
</body>
</html>
