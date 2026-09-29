<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        html{
            font-size:20px;
           
        }
        body{
            width:100%;
            top:10px;
        }
        li{
            margin:0;
            padding:0;
        }
        h3{
            margin:5px;
        }
        ul{
            list-style: none;
        }
        h1{
            margin:5px;
        }
       
    </style>
</head>
<body>
    <table style="width:100%">
        <tr style="vertical-align:top">
            <td>
                <h1>Remitente</h1>
                <ul>
                    <li><h3>Nombre:</h3> Agencia Lupa SAS</li>
                    <li><h3>Documento:</h3> Nit - 901086443-7</li>
                    <li><h3>Dirección:</h3> Carrera 1 #23-60 Cali</li>
                    <li><h3>Teléfono:</h3> 3165288931</li>
                   
                </ul>
            </td>
            <td>
                <h1>Destinatario</h1>
                <ul>
                    <li><h3>Nombre:</h3> {{ $guia->contacto }} - {{$guia->empresa}}</li>
                    <li><h3>Documento:</h3> {{ $guia->tipo_documento }}  {{$guia->documento}}</li>
                    <li><h3>Teléfono:</h3> {{ $guia->telefono }} </li>
                    <li><h3>Direccion:</h3> {{ $guia->direccion }} </li>
                    <li><h3>Ciudad:</h3> {{ $guia->ciudad }}</li>
                </ul>
            </td>
        </tr>
    </table>
    
</body>
</html>