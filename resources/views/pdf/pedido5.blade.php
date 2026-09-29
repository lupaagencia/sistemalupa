
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    
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
        .linea{
        background: #33e034;
        height: 20px;
        }
        .encabezado{
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 10px 30px;
        }
        .encabezado ul{
            list-style: none;
            align-content: center;
        }
        .datos .col-sm-6 div{
            display: flex;
            flex-direction: row;
            line-height: ;
           
        }
       
    </style>
</head>
<body>
<div >
      
      <div class="card-body" id="pedido">
          <div class="row">
              <div class="col-sm-12 linea" ></div>
              <div class="col-sm-12 encabezado" style="display:ruby-text ">
                    <table>
                        <tr>

                            <td>
                                
                                <figure ><img src="img/LOGO-LUPA.jpg" alt=""> </figure>
                            </td>
                            <td>

                                <div class="datosempresa" >
                                    <ul style="text-align:center">
                                        <li>EMPAQUES LUPA Y/O AGENCIA LUPA SAS</li>
                                        <li>Nit. 901086443-7</li>
                                        <li>Carrera 1 # 23-60 Cali - Colombia - Valle del Cauca</li>
                                        <li>Cel. 57 + 316 5288931</li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </table>
              </div>
              <div class="col-sm-12 datos">
                  <table class="row">
                        <tr class="col-sm-6 ">
                            <td>
                                <div>
                                    <label style=" padding: 0; line-height:0; font-weight: bold; font-size:16px;" for="">Fecha </label>
                                    <div style="line-height:10px; font-size:16px;">{{$pedido->fecha}}</div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <label  style="font-weight: bold; font-size:16px; line-height:10px;" for="">Pedido </label>
                                    <div style="line-height:10px; font-size:16px; color:red">{{$pedido->id}}</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div>
                                    <label style="font-weight: bold; font-size:16px; line-height:10px;" for="">Nombre Cliente </label>
                                    <div style="line-height:10px; font-size:16px;">{{$pedido->cliente->razonsocial}}</div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <label style="font-weight: bold; font-size:16px; line-height:10px;" for="">Nit </label>
                                    <div style="line-height:10px; font-size:16px;" ></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div>
                                    <label style="font-weight: bold; font-size:16px; line-height:10px;" for="">Dirección / Sede </label>
                                    <div style="line-height:10px; font-size:16px;">{{$pedido->cliente->direccionf}}</div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <label style="font-weight: bold; font-size:16px; line-height:10px;" for="">Celular </label>
                                    <div style="line-height:10px; font-size:16px;">

                                    </div>
                                </div>
                            </td>
                        </tr> 
                </table> 
                 
              </div>
              <div class="col-sm-12 detalles">
                      
                  <div class="seccion-body" >
                      <table class="table">
                          
                          <tr class="titulodetalles">

                          <th>Cantidad</th><th >Detalles</th><th>V. Unit. </th><th>Valor</th>
                          </tr>
                          @foreach($pedido->lineas as $linea)
                        
                          <tr >
                           
                              
                              <td > 
                                {{$linea->cantidad}}
                              </td>
                              <td>
                                  {{$linea->articulo->nombre}}
                                    @foreach($linea->detalles as $detalle)
                                      - <span>
                                        
                                         
                                                  - {{ $detalle->valor}} {{$detalle->descripcion}}  &nbsp; 
                                            
                                         
                                      </span>
                                    @endforeach
                                 
                              </td>
                          
                              <td class="valores"> 
                                  $  {{$linea->valor_unitario}}
                              </td>
                              <td class="valores"> 
                                  $ {{$linea->valor_total}}
                              </td>
                          </tr>
                          @endforeach
                      </table>
                  </div>
              </div>
              <div class="col-sm-12">
                  Nota: Por favor tener en cuenta que el saldo final por pagar puede variar de + o -, por lo cual te descontaremos 
el faltante o pagaras despues de 10 unidades o mas el excedente
              </div>
              <div class="col-sm-12 totales">
                    <table class="float-right">
                              
                        <tr>
                            <th>SUBTOTAL $</th>
                            <td>{{$pedido->subtotal}}</td>
                        </tr>
                        <tr> 
                            <th>IVA 19% $</th> 
                            <td>{{$pedido->impuestos}}</td>
                        </tr>
                        <tr>
                            <th>TOTAL $</th>
                            <td>{{$pedido->total}}</td>
                        </tr>
                        <tr>
                            <th >ABONO $</th>
                            <td>{{$pedido->abono}}</td>
                        </tr>
                        <tr>
                            <th >
                                SALDO 
                            </th>
                            <td>{{$pedido->saldo}}</td>
                        </tr>
                    </table>
                    <div>
                        Valor Letras:
                    </div>
                </div>
             
            
          </div>
          
      </div>        
  </div>
    
</body>
</html>

