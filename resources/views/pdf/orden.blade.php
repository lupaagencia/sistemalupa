<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <style>
        html{
            margin:20px,20px;
            font-size:12px;
        }
        li{
            margin:0 !important;
            padding:0 !important;
        }
        h3{
            margin:5px;
        }
        ul{
            list-style: none !important;
        }
        h1{
            margin:5px;
        }
        td{
            padding:2px;
            min-width: 80px;
        }
      
       
    </style>
</head>
<body>
    <div class="card vieworden">
        <div class="card-header header_vieworden">
            <div class="logo_vieworden">

                <img src="img/LOGO-LUPA.jpg" alt="" width="100px">
            </div>
            <div class="fecha_norden">
                <div class="card fecha_orden">
                    <div class="card-body ">
                        <table>
                            <tr>
                                <th>ORDEN DE TRABAJO NO. </th>
                                <td>{{ $orden->idorden }}</td>
                                
                            </tr>
                        <tr>
                            <th>Fecha de creación:</th>
                            <td>{{ $orden->fecha }}</td>
                            <th>Prioridad:</th>
                            <td>{{ $orden->prioridad }}</td>
                            <th>Fecha de entrega:</th>
                            <td>{{ $orden->fecha_entrega }}</td>
                        </tr>    
                        <tr></tr>
                        </table>
                        
                    </div>
                </div>
            </div> 
        </div>
      
        <div class="datos_cliente">
            <div class="card">
                <div class="card-header">
                    <h4>Cliente</h4>
                </div>
                <div class="card-body">
                    <table>
                        <tr>
                            <th>Cliente:</th>
                            <td>{{ $orden->rasonsocial }}</td>
                            <th>Contacto:</th>
                            <td>{{ $orden->contacto }}</td>
                        </tr>
                        <tr>
                            <th>Correo:</th>
                            <td>{{ $orden->email }}</td>
                            <th>Documento:</th>
                            <td>{{ $orden->num_documento }}</td>
                            <th>Teléfono empresa:</th>
                            <td>{{ $orden->telefono }}</td>
                        </tr>
                        <tr>
                            <th>Dirección:</th>
                            <td>{{ $orden->direccion }}</td>
                        </tr>
                    </table>
                    
                </div>
            </div>
        </div>
        <div class="datosorden">
            <div class="card">
                <div class="card-body">
                    <table>
                        <tr>
                            <th  valign="top">Producto:</th>
                            <td>{{ $orden->articulo }}
                                @foreach($orden->detalles as $de)
                                <li >
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <th >{{ $de->titulo_detalle }}</th>
                                                <td >{{ $de->valor_detalle }}</td>
                                                <td >{{ $de->descripcion_detalle }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </li>
                                @endforeach
                                
                            </td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <th>Cantidad:</th>
                            <td>{{ $orden->cantidad }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
    </div>
    
</body>
</html>
