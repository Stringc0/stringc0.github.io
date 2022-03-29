<html>

<head>
<title>"Seguro"</title>
</head>
<body>
<?php include('conf.php');?>
<p></p>

<form action="secured_tables.php" method="get">
	<input type="text" name="search" placeholder="Buscar obras de arte por ID...">
	<input type="submit">
</form>

<?php
//Estableciendo conexión con la base de datos
$dbb = conectar();

//Preparación de la consulta. Creando plantilla
$sql = "SELECT * FROM galeria WHERE id = ? AND estado = 'publica'";
$stmt = $dbb->prepare($sql);

//Binding y ejecución
$search = $_GET['search'];
$stmt->bind_param("i", $search);
$stmt->execute();

//Guardando el resultado de la consulta
$result = $stmt->get_result();

// Mostrando resultado de la consulta
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
	} else {
		echo "La obra de arte es privada o no está disponible.";
		echo "<br><br>";
		echo "Búsqueda:" . " $search";
	}
}
$dbb->close();
?>
</body>
</html>
