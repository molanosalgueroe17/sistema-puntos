Consulta tus puntos

<form action="{{ url('/empleado') }}" method="post" enctype="multipart/form-data">
@csrf

<input type="text" name="id_card" id="id_card" placeholder="Ingrese su cédula">
<br>

<button type="submit" value>Consultar</button>
<br>

</form>
