    @extends('principal')
    @section('contenido')
    
    @if(Auth::check())
        @if(Auth::user()->idrol=='Administrador' || Auth::user()->idrol=='Superadministrador')
            <template v-if="menu==30">
                <resetpass :user={{Auth::user()}}></resetpass>
            </template>
            <template v-if="menu==31">
                <cambiarpass :user={{Auth::user()}}></cambiarpass>
            </template>
            <template v-if="menu==33">
                <empleados :user={{Auth::user()}}></empleados>
            </template>
            <template v-if="menu==0">
                <escritorio :user={{Auth::user()}}></escritorio>
            </template>
            <template v-if="menu==24">
                <registroproduccion :user={{Auth::user()}}></registroproduccion>
            </template>
            <template v-if="menu==21">
                <ingreso :user={{Auth::user()}}></ingreso>
            </template>
            <template v-if="menu==1">
                <categoria :user={{Auth::user()}}></categorias>
            </template>

            <template v-if="menu==2">
                <articulo :user={{Auth::user()}}></articulo>
            </template>
            <template v-if="menu==32">
                <tipoproducto :user={{Auth::user()}}></tipoproducto>
            </template>
            <template v-if="menu==23">
                <inventarios :user={{Auth::user()}}></inventarios>
            </template>
            <template v-if="menu==3">
                <ingresos :user={{Auth::user()}}></ingresos>
            </template>

            <template v-if="menu==13">
                <costop :user={{Auth::user()}}></costop>
            </template>

            <template v-if="menu==14">
                <orden pedido="0" listado="1" :user={{Auth::user()}} ></orden>
            </template>

            <template v-if="menu==18">
                <nuevocomprobante :user={{Auth::user()}}></nuevocomprobante>
            </template>

            <template v-if="menu==15">
                <programa :user={{Auth::user()}}></programa>
            </template>

            <template v-if="menu==4">
                <proveedor :user={{Auth::user()}}></proveedor>
            </template>
            
        
            <template v-if="menu==5">
                <ventas :user={{Auth::user()}}></ventas>
            </template>
            
            <template v-if="menu==17">
                <seguimientov :user={{Auth::user()}}></seguimientov>
            </template>

            <template v-if="menu==52">
                <estadisticasventas :user={{Auth::user()}}></estadisticasventas>
            </template>

            <template v-if="menu==16">
                <cartera :user={{Auth::user()}}></cartera>
            </template>
            

            <template v-if="menu==6">
                <cliente user={{Auth::user()}}></cliente>
            </template>

            <template v-if="menu==7">
                <user :user={{Auth::user()}}></user>
            </template>
            <template v-if="menu==71">
                <actividad :user={{Auth::user()}}></actividad>
            </template>

            <template v-if="menu==8">
                <rol :user={{Auth::user()}}></rol>
            </template>
            
            <template v-if="menu==19">
                <reportespro :user={{Auth::user()}}></reportespro>
            </template>
            <template v-if="menu==9" >
                <h1>Reprote de ingresos</h1>
            </template>
            
            <template v-if="menu==40">
                <analisisflujo :user={{Auth::user()}}></analisisflujo>
            </template>
            <template v-if="menu==45">
                <seguimiento-optimizado :user="{{Auth::user()}}"></seguimiento-optimizado>
            </template>
            <template v-if="menu==10">

                <h1>Reporte de ventas</h1>
            </template>
            
            <template v-if="menu==20">
                <ajustes :user={{Auth::user()}}></ajustes>
            </template>
            @if(Auth::user()->idrol === 'Superadministrador')
            <template v-if="menu==99">
                <web-ide :user={{Auth::user()}}></web-ide>
            </template>
            @endif
            <template v-if="menu==11">
                <h1>Ayuda</h1>
            </template>

            <template v-if="menu==12">
                <h1>Acerca de...</h1>
            </template>
            <template v-if="menu==22">
                <statuspro :user={{Auth::user()}}></statuspro>
            </template>
            <template v-if="menu==41">
                <bancoscajas :user={{Auth::user()}}></bancoscajas>
            </template>
            <template v-if="menu==42">
                <cuentasporpagar :user={{Auth::user()}}></cuentasporpagar>
            </template>
            <template v-if="menu==51">
                <pagosprogramados :user={{Auth::user()}}></pagosprogramados>
            </template>
            <template v-if="menu==53">
                <controlasistencia :user={{Auth::user()}}></controlasistencia>
            </template>
            <template v-if="menu==43">
                <gastosegresos :user={{Auth::user()}}></gastosegresos>
            </template>
            <template v-if="menu==44">
                <nomina :user={{Auth::user()}}></nomina>
            </template>
            <template v-if="menu==46">
                <puc :user={{Auth::user()}}></puc>
            </template>
            <template v-if="menu==47">
                <movimientos-contables :user={{Auth::user()}}></movimientos-contables>
            </template>
            <template v-if="menu==48">
                <reportes-contables :user={{Auth::user()}}></reportes-contables>
            </template>
            <template v-if="menu==49">
                <simulador-contable :user={{Auth::user()}}></simulador-contable>
            </template>
            <template v-if="menu==100">
                <atributos :user={{Auth::user()}}></atributos>
            </template>
            <template v-if="menu==50">
                <despiece-muebles :user={{Auth::user()}}></despiece-muebles>
            </template>
            <template v-if="menu==60">
                <crm-main :user="{{Auth::user()}}"></crm-main>
            </template>
            <template v-if="menu==80">
                <web-sliders :user="{{Auth::user()}}"></web-sliders>
            </template>
        @elseif(Auth::user()->idrol=='Coordinador')
            <template v-if="menu==30">
                <resetpass :user={{Auth::user()}}></resetpass>
            </template>
            <template v-if="menu==31">
                <cambiarpass :user={{Auth::user()}}></cambiarpass>
            </template>
            <template v-if="menu==33">
                <empleados :user={{Auth::user()}}></empleados>
            </template>
            <template v-if="menu==0">
                <escritorio :user={{Auth::user()}}></escritorio>
            </template>
            <template v-if="menu==24">
                <registroproduccion :user={{Auth::user()}}></registroproduccion>
            </template>
            <template v-if="menu==21">
                <ingreso :user={{Auth::user()}}></ingreso>
            </template>
            <template v-if="menu==1">
                <categoria :user={{Auth::user()}}></categorias>
            </template>

            <template v-if="menu==2">
                <articulo :user={{Auth::user()}}></articulo>
            </template>
            <template v-if="menu==32">
                <tipoproducto :user={{Auth::user()}}></tipoproducto>
            </template>
            <template v-if="menu==23">
                <inventarios :user={{Auth::user()}}></inventarios>
            </template>
            <template v-if="menu==3">
                <ingresos :user={{Auth::user()}}></ingresos>
            </template>

            <template v-if="menu==13">
                <costop :user={{Auth::user()}}></costop>
            </template>

            <template v-if="menu==14">
                <orden pedido="0" listado="1" :user={{Auth::user()}} ></orden>
            </template>

            <template v-if="menu==18">
                <nuevocomprobante :user={{Auth::user()}}></nuevocomprobante>
            </template>

            <template v-if="menu==15">
                <programa :user={{Auth::user()}}></programa>
            </template>

            <template v-if="menu==4">
                <proveedor :user={{Auth::user()}}></proveedor>
            </template>
            
        
            <template v-if="menu==5">
                <ventas :user={{Auth::user()}}></ventas>
            </template>
            
            <template v-if="menu==17">
                <seguimientov :user={{Auth::user()}}></seguimientov>
            </template>

            <template v-if="menu==16">
                <cartera :user={{Auth::user()}}></cartera>
            </template>
            

            <template v-if="menu==6">
                <cliente user={{Auth::user()}}></cliente>
            </template>

            <template v-if="menu==7">
                <user :user={{Auth::user()}}></user>
            </template>
            <template v-if="menu==71">
                <actividad :user={{Auth::user()}}></actividad>
            </template>

            <template v-if="menu==8">
                <rol :user={{Auth::user()}}></rol>
            </template>
            
            <template v-if="menu==19">
                <reportespro :user={{Auth::user()}}></reportespro>
            </template>
            <template v-if="menu==9" >
                <h1>Reprote de ingresos</h1>
            </template>
            
            <template v-if="menu==10">
                <h1>Reporte de ventas</h1>
            </template>
            
            <template v-if="menu==20">
                <ajustes :user={{Auth::user()}}></ajustes>
            </template>
            <template v-if="menu==45">
                <seguimiento-optimizado :user="{{Auth::user()}}"></seguimiento-optimizado>
            </template>
            <template v-if="menu==11">
                <h1>Ayuda</h1>
            </template>

            <template v-if="menu==12">
                <h1>Acerca de...</h1>
            </template>
            <template v-if="menu==22">
                <statuspro :user={{Auth::user()}}></statuspro>
            </template>
            <template v-if="menu==100">
                <atributos :user={{Auth::user()}}></atributos>
            </template>
            <template v-if="menu==50">
                <despiece-muebles :user={{Auth::user()}}></despiece-muebles>
            </template>
            <template v-if="menu==51">
                <pagosprogramados :user={{Auth::user()}}></pagosprogramados>
            </template>
            <template v-if="menu==53">
                <controlasistencia :user={{Auth::user()}}></controlasistencia>
            </template>
            <template v-if="menu==60">
                <crm-main :user="{{Auth::user()}}"></crm-main>
            </template>
        @elseif (Auth::user()->idrol=='Vendedor')
            <template v-if="menu==5">
                <h1>Ventas</h1>
            </template>
            <template v-if="menu==60">
                <crm-main :user="{{Auth::user()}}"></crm-main>
            </template>
            <template v-if="menu==6">
                <cliente :user={{Auth::user()}}></cliente>
            </template>
            <template v-if="menu==10">
                <h1>Reporte de ventas</h1>
            </template>
            <template v-if="menu==11">
                <h1>Ayuda</h1>
            </template>

            <template v-if="menu==12">
                <h1>Acerca de...</h1>
            </template>
        @elseif (Auth::user()->idrol=='Gerente Comercial')
            <template v-if="menu==0">
                <escritorio :user={{Auth::user()}}></escritorio>
            </template>
            <template v-if="menu==5">
                <ventas :user={{Auth::user()}}></ventas>
            </template>
            <template v-if="menu==6">
                <cliente :user={{Auth::user()}}></cliente>
            </template>
            <template v-if="menu==60">
                <crm-main :user="{{Auth::user()}}"></crm-main>
            </template>
            <template v-if="menu==52">
                <estadisticasventas :user={{Auth::user()}}></estadisticasventas>
            </template>
        @elseif (Auth::user()->idrol=='Diseñador')
            <template v-if="menu==30">
                <resetpass :user={{Auth::user()}}></resetpass>
            </template>
            <template v-if="menu==31">
                <cambiarpass :user={{Auth::user()}}></cambiarpass>
            </template>

            <template v-if="menu==0">
                <escritorio :user={{Auth::user()}}></escritorio>
            </template>
            <template v-if="menu==24">
                <registroproduccion :user={{Auth::user()}}></registroproduccion>
            </template>

            <template v-if="menu==1">
                <categoria :user={{Auth::user()}}></categorias>
            </template>

            <template v-if="menu==2">
                <articulo :user={{Auth::user()}}></articulo>
            </template>
            <template v-if="menu==32">
                <tipoproducto :user={{Auth::user()}}></tipoproducto>
            </template>
            <template v-if="menu==23">
                <inventarios :user={{Auth::user()}}></inventarios>
            </template>
            <template v-if="menu==3">
                <ingresos :user={{Auth::user()}}></ingresos>
            </template>

            <template v-if="menu==13">
                <costop :user={{Auth::user()}}></costop>
            </template>

            <template v-if="menu==14">
                <orden pedido="0" listado="1" :user={{Auth::user()}} ></orden>
            </template>

            <template v-if="menu==18">
                <nuevocomprobante :user={{Auth::user()}}></nuevocomprobante>
            </template>

            <template v-if="menu==15">
                <programa :user={{Auth::user()}}></programa>
            </template>

            <template v-if="menu==4">
                <proveedor :user={{Auth::user()}}></proveedor>
            </template>
            

            

            <template v-if="menu==6">
                <cliente user={{Auth::user()}}></cliente>
            </template>


            
            <template v-if="menu==19">
                <reportespro :user={{Auth::user()}}></reportespro>
            </template>
            <template v-if="menu==9" >
                <h1>Reprote de ingresos</h1>
            </template>
            
            <template v-if="menu==40">
                <analisisflujo :user={{Auth::user()}}></analisisflujo>
            </template>
            <template v-if="menu==45">
                <seguimiento-optimizado :user="{{Auth::user()}}"></seguimiento-optimizado>
            </template>
            <template v-if="menu==10">
                <h1>Reporte de ventas</h1>
            </template>
            
            <template v-if="menu==20">
                <ajustes :user={{Auth::user()}}></ajustes>
            </template>
            <template v-if="menu==11">
                <h1>Ayuda</h1>
            </template>

            <template v-if="menu==12">
                <h1>Acerca de...</h1>
            </template>
            <template v-if="menu==22">
                <statuspro :user={{Auth::user()}}></statuspro>
            </template>
            <template v-if="menu==100">
                <atributos :user={{Auth::user()}}></atributos>
            </template>
            <template v-if="menu==50">
                <despiece-muebles :user={{Auth::user()}}></despiece-muebles>
            </template>
        @elseif (Auth::user()->idrol=='Operario')
            <template v-if="menu==24">
                <registroproduccion :user={{Auth::user()}}></registroproduccion>
            </template>
            <template v-if="menu==30">
                <resetpass :user={{Auth::user()}}></resetpass>
            </template>
            <template v-if="menu==31">
                <cambiarpass :user={{Auth::user()}}></cambiarpass>
            </template>
            <template v-if="menu==0">
                <escritorio :user={{Auth::user()}}></escritorio>
            </template>
            <template v-if="menu==21">
                <ingreso :user={{Auth::user()}}></ingreso>
            </template>
            <template v-if="menu==1">
                <categoria :user={{Auth::user()}}></categorias>
            </template>

            <template v-if="menu==2">
                <articulo :user={{Auth::user()}}></articulo>
            </template>

            <template v-if="menu==3">
                <ingresos :user={{Auth::user()}}></ingresos>
            </template>

            <template v-if="menu==13">
                <costop :user={{Auth::user()}}></costop>
            </template>

            <template v-if="menu==14">
                <orden :user={{Auth::user()}} pedido="0" listado="1"></orden>
            </template>

            <template v-if="menu==18">
                <nuevocomprobante :user={{Auth::user()}}></nuevocomprobante>
            </template>

            <template v-if="menu==15">
                <programa :user={{Auth::user()}}></programa>
            </template>

            <template v-if="menu==4">
                <proveedor :user={{Auth::user()}}></proveedor>
            </template>

            
            <template v-if="menu==17">
                <seguimientov :user={{Auth::user()}}></seguimientov>
            </template>

            

            <template v-if="menu==6">
                <cliente :user={{Auth::user()}}></cliente>
            </template>

            
            <template v-if="menu==19">
                <reportespro :user={{Auth::user()}}></reportespro>
            </template>
            <template v-if="menu==9" >
                <h1>Reprote de ingresos</h1>
            </template>
            
            <template v-if="menu==10">
                <h1>Reporte de ventas</h1>
            </template>
            
            
            <template v-if="menu==11">
                <h1>Ayuda</h1>
            </template>

            <template v-if="menu==12">
                <h1>Acerca de...</h1>
            </template>
            <template v-if="menu==22">
                <statuspro :user={{Auth::user()}}></statuspro>
            </template>
        @elseif (Auth::user()->idrol=='Auxiliar producción' || Auth::user()->idrol=='Auxiliar de producción')
            <template v-if="menu==24">
                <registroproduccion :user={{Auth::user()}}></registroproduccion>
            </template>
            <template v-if="menu==30">
                <resetpass :user={{Auth::user()}}></resetpass>
            </template>
            <template v-if="menu==31">
                <cambiarpass :user={{Auth::user()}}></cambiarpass>
            </template>
            <template v-if="menu==0">
                <escritorio :user={{Auth::user()}}></escritorio>
            </template>
            <template v-if="menu==21">
                <ingreso :user={{Auth::user()}}></ingreso>
            </template>
            <template v-if="menu==1">
                <categoria :user={{Auth::user()}}></categorias>
            </template>

            <template v-if="menu==2">
                <articulo :user={{Auth::user()}}></articulo>
            </template>

            <template v-if="menu==3">
                <ingresos :user={{Auth::user()}}></ingresos>
            </template>

            <template v-if="menu==13">
                <costop :user={{Auth::user()}}></costop>
            </template>

            <template v-if="menu==14">
                <orden :user={{Auth::user()}} pedido="0" listado="1"></orden>
            </template>

            <template v-if="menu==18">
                <nuevocomprobante :user={{Auth::user()}}></nuevocomprobante>
            </template>

            <template v-if="menu==15">
                <programa :user={{Auth::user()}}></programa>
            </template>

            <template v-if="menu==4">
                <proveedor :user={{Auth::user()}}></proveedor>
            </template>

            
            <template v-if="menu==17">
                <seguimientov :user={{Auth::user()}}></seguimientov>
            </template>

            

            <template v-if="menu==6">
                <cliente :user={{Auth::user()}}></cliente>
            </template>

            
            <template v-if="menu==19">
                <reportespro :user={{Auth::user()}}></reportespro>
            </template>
            <template v-if="menu==9" >
                <h1>Reprote de ingresos</h1>
            </template>
            
            <template v-if="menu==10">
                <h1>Reporte de ventas</h1>
            </template>
            
            
            <template v-if="menu==11">
                <h1>Ayuda</h1>
            </template>

            <template v-if="menu==12">
                <h1>Acerca de...</h1>
            </template>
            <template v-if="menu==22">
                <statuspro :user={{Auth::user()}}></statuspro>
            </template>
        @elseif (Auth::user()->idrol=='Contador')
            <template v-if="menu==21">
                <ingreso :user={{Auth::user()}}></ingreso>
            </template>
            <template v-if="menu==41">
                <bancoscajas :user={{Auth::user()}}></bancoscajas>
            </template>
            <template v-if="menu==16">
                <cartera :user={{Auth::user()}}></cartera>
            </template>
            <template v-if="menu==42">
                <cuentasporpagar :user={{Auth::user()}}></cuentasporpagar>
            </template>
            <template v-if="menu==51">
                <pagosprogramados :user={{Auth::user()}}></pagosprogramados>
            </template>
            <template v-if="menu==53">
                <controlasistencia :user={{Auth::user()}}></controlasistencia>
            </template>
            <template v-if="menu==43">
                <gastosegresos :user={{Auth::user()}}></gastosegresos>
            </template>
            <template v-if="menu==44">
                <nomina :user={{Auth::user()}}></nomina>
            </template>
            <template v-if="menu==46">
                <puc :user={{Auth::user()}}></puc>
            </template>
            <template v-if="menu==47">
                <movimientos-contables :user={{Auth::user()}}></movimientos-contables>
            </template>
            <template v-if="menu==48">
                <reportes-contables :user={{Auth::user()}}></reportes-contables>
            </template>
            <template v-if="menu==49">
                <simulador-contable :user={{Auth::user()}}></simulador-contable>
            </template>
        @endif
    @endif
        
        
    @endsection