
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
        .colores {
            display: flex;
            justify-content: space-between; /* Alineamos los div horizontalmente con espacio entre ellos */
            gap: 20px; /* Espacio entre los divs */
        }
       
    </style>
</head>
<body style="width:730px; height:945px; font-size:14px; line-height:18px; font-family: sans-serif;">
    <div style="height:20px;width:100%;background:#33e034"></div>
    <div style="padding:5px 15px">
      
      <div  id="pedido">
          <div>
              <div class="" ></div>
              <div class="" style="display:ruby-text ">
                    <table>
                        <tr>

                            <td>
                                
                                <figure ><img style="width:150px"  src="img/LOGO-LUPA.jpg" alt=""> </figure>
                            </td>
                            <td style="width:500px; text-align:center">

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
              <div class=" datos">
                <table>
                    <tr>
                        <td width="520px">
                            <table class="row" >
                                    
                                    <tr style="line-height:16px">
                                        <td style="vertical-align:top" width="80px">
                                            <label style="font-weight: bold; font-size:14px; " for="">Cliente: </label>
                                        </td>
                                        <td style="vertical-align:top; padding:2px 10px; ">
                                            <div style=" font-size:14px;">{{$pedido->cliente->razonsocial}}</div>
                                        </td>
                                        <td style="vertical-align:top">
                                            <label style="font-weight: bold; font-size:14px; " for="">Dirección:</label>
                                        </td>
                                        <td style="vertical-align:top; padding:2px 10px; ">
                                            <div style=" font-size:14px;">{{$pedido->cliente->direccionf}} {{$pedido->cliente->ciudad}}</div>
                                        </td>
                                        
                                    </tr>
                                    <tr style="line-height:16px">
                                        <td style="vertical-align:top">
                                            <label style="font-weight: bold; font-size:14px; " for="">Teléfono: </label>
                                        </td>
                                        <td style="vertical-align:top; padding:2px 10px; ">
                                             <div style=" font-size:14px;">{{ isset($pedido->cliente->contactos[0]) ? $pedido->cliente->contactos[0]->telefono : 'N/A' }}</div>
            
                                        </td>
                                        <td style="vertical-align:top" width="80px">
                                            <label style="font-weight: bold; font-size:14px; " for="">Contacto:</label>
                                        </td>
                                        <td style="vertical-align:top; padding:2px 10px; ">
                                           <div style=" font-size:14px;" >{{ isset($pedido->cliente->contactos[0]) ? $pedido->cliente->contactos[0]->nombre : 'N/A' }}</div>
                                        </td>
                                        
                                        
                                    </tr> 
                                   
                            </table> 
                        </td>
                        <td>
                            <table class="row">
                                
                                <tr style="line-height:16px">
                                    <td style="vertical-align:top"  colspan="2">
                                             <label  style="font-weight: bold; font-size:14px; " for="">{{ strtoupper($pedido->tipo == 'cuentacobro' ? 'Cuenta de Cobro' : $pedido->tipo) }}</label>
                                    </td>
                                    <td>
                                        <div style=" font-size:16px; color:red; font-weight: bold ">No. {{$pedido->id}}</div>
                                            
                                    </td>
                                </tr>
                                <tr class=" " style="background:#e8e7e5; border:1px solid #ccc;">
                                    <td style=" vertical-align:top; padding:2px 10px;" width="50px">
                                        <label style=" padding: 0;  font-weight: bold; font-size:14px;" for="">Fecha: </label>
                                    </td>
                                    <td colspan="2">
                                        <div style="font-size:14px; padding:2px 10px;">{{$pedido->fecha}}</div>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                       
                </table>
                 
              </div>
              <div class=" detalles" style="min-height:550px; margin-top:5px" >
                      
                  <div class="seccion-body" >
                      <table class="table">
                          
                          <tr class="titulodetalles" style="background:#33e034; line-height:16px">

                          @if($pedido->tipo != 'remision')
                          <th style="text-align:center;">Cantidad</th><th style="text-align:center;width:200px">Concepto</th><th colspan="2" style="text-align:center">V. Unit. </th><th style="text-align:center">Total</th>
                          @else
                          <th style="text-align:center;">Cantidad</th><th style="text-align:center;width:400px">Concepto</th>
                          @endif
                          </tr>
                            @if($pedido->tipo != 'remision')
                            <tr style="line-height:16px">
                                <td colspan="2"></td><td style="color:#33e034; font-size:10px; text-align:center">ANTES DE IVA</td><td style="color:#33e034; font-size:12px; text-align:center">CON IVA</td><td></td>
                            </tr>
                            @endif
                            @foreach($pedido->lineas as $linea)
                            @php
                                if($loop->index%2==0){
                                    
                                    $color='#e8e7e5';
                                    $border=1;
                                }else{
                                    $color='#fff';
                                    $border=0;
                                }
                            @endphp
                            
                          <tr  style="line-height:16px; font-size:14px; background:{{$color}}; border:{{$border}}px solid #ccc;">
                           
                              
                              <td style="text-align:center"> 
                                {{$linea->cantidad}} 
                              </td>
                              <td width="200">
                                 <strong>
                                     @if($linea->orden && !empty($linea->orden->detalles_diseno))
                                         {{ $linea->orden->detalles_diseno }}
                                     @elseif($linea->articulo)
                                         {{ $linea->articulo->nombre }}
                                     @else
                                         Trabajo Personalizado
                                     @endif
                                 </strong>
                                <ul>
                                    @foreach($linea->detalles as $detalle)
                                    <li >
                                    <table>
                                        <tr>
                                            <td>
                                            {{$detalle->titulo}}:
                                            </td>
                                        
                                       
                                            @if(is_array($detalle->valor) || is_object($detalle->valor))
                                        
                                                @foreach($detalle->valor as $valor) 
                                                    <td  style="box-sizing: border-box; width:45px; padding:2px 5px; color:#fff; background: {{$valor->hex}}">
                                                        {{ $valor->pantone}} 
                                                    </td>
                                                @endforeach
                                            
                                            @else
                                            <td>

                                                {{ $detalle->valor}} 
                                            </td>    
                                            @endif
                                            <td>

                                                {{$detalle->descripcion}}
                                            </td>
                                        </tr>
                                    </table>
                                    </li> 
                                    @endforeach
                                </ul> 
                                 
                              </td>
                          
                               @if($pedido->tipo != 'remision')
                              <td class="valores" style="text-align:center" > 
                                  $  {{number_format($linea->valor_unitario,0)}}
                              </td>
                              <td class="valores" style="text-align:center"> 
                                  $ {{number_format($linea->valorconiva,0)}}
                              </td>
                              <td class="valores" style="text-align:right"> 
                                  $ {{number_format($linea->valortotalconiva, 0)}}
                              </td>
                              @endif
                          </tr>
                          @endforeach
                      </table>
                  </div>
              </div>
              <div>
                  <table style="font-size:14px">
                    <tr>
                        <td style="width:520px; vertical-align:top" >
                            <div class="" style="font-weight: bold;">
                        Nota: Por favor tener en cuenta que el saldo final por pagar puede variar de + o -, por lo cual te descontaremos 
        el faltante o pagaras despues de 10 unidades o mas el excedente
                            </div>
                            <div >
                                <strong>Valor Letras: {{ $pedido->letras}} </strong>
                            </div>
                        </td>
                        <td>
                            @if($pedido->tipo != 'remision' && empty($pedido->ocultar_totales_pdf))
                            <div class=" totales" >
                                <table style="" >
                                        
                                    <tr>
                                        <th>SUBTOTAL</th>
                                        <td style="text-align:right">$ {{number_format($pedido->subtotal,0)}}</td>
                                    </tr>
                                    <tr> 
                                        <th>IVA 19%</th> 
                                        <td style="text-align:right">$ {{number_format($pedido->impuestos,0)}}</td>
                                    </tr>
                                    <tr>
                                        <th>TOTAL</th>
                                        <td style="text-align:right">$ {{number_format($pedido->total,0)}}</td>
                                    </tr>
                                    <tr>
                                        <th >ABONO</th>
                                        <td style="text-align:right">$ {{number_format($pedido->abono,0)}}</td>
                                    </tr>
                                    <tr>
                                        <th >
                                            SALDO 
                                        </th>
                                        <td style="text-align:right">$ {{number_format($pedido->saldo,0)}}</td>
                                    </tr>
                                </table>
                        
                            </div>
                            @endif
    
                        </td>
                    </tr>
                  </table>
              </div>
             
              
             
            
          </div>
          
      </div>        
  </div>
  <div style="height:20px;width:100%;background:#33e034"></div>   
</body>
</html>
