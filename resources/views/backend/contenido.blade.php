    @extends('principal')
    @section('contenido')
    
        @if(Auth::check())
            @if(Auth::user()->idrol==1)
                <template v-if="menu==0">
                
                </template>
        
                <template v-if="menu==1">
                    <categoria></categorias>
                </template>
        
                <template v-if="menu==2">
                    <articulo></articulo>
                </template>
        
                <template v-if="menu==3">
                    <ingreso></ingreso>
                </template>

                <template v-if="menu==13">
                    <costop></costop>
                </template>

                <template v-if="menu==14">
                    <orden></orden>
                </template>

                <template v-if="menu==18">
                    <nuevocomprobante></nuevocomprobante>
                </template>
        
                <template v-if="menu==15">
                    <programa></programa>
                </template>
        
                <template v-if="menu==4">
                    <proveedor></proveedor>
                </template>
        
                <template v-if="menu==5">
                    <ventas></ventas>
                </template>
                
                <template v-if="menu==17">
                    <seguimientov></seguimientov>
                </template>

                <template v-if="menu==16">
                    <cartera></cartera>
                </template>
               
        
                <template v-if="menu==6">
                    <cliente></cliente>
                </template>
        
                <template v-if="menu==7">
                    <user></user>
                </template>
        
                <template v-if="menu==8">
                    <rol></rol>
                </template>
        
                <template v-if="menu==9">
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
            @elseif (Auth::user()->idrol==2)
                <template v-if="menu==5">
                    <h1>Ventas</h1>
                </template>
                <template v-if="menu==6">
                    <cliente></cliente>
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
            @elseif (Auth::user()->idrol==3)
                <template v-if="menu==1">
                    <categoria></categorias>
                </template>
        
                <template v-if="menu==2">
                    <articulo></articulo>
                </template>
        
                <template v-if="menu==3">
                    <ingreso></ingreso>
                </template>
        
                <template v-if="menu==4">
                    <proveedor></proveedor>
                </template>
                <template v-if="menu==9">
                    <h1>Reprote de ingresos</h1>
                </template>
                <template v-if="menu==11">
                    <h1>Ayuda</h1>
                </template>
        
                <template v-if="menu==12">
                    <h1>Acerca de...</h1>
                </template>
            @elseif (Auth::user()->idrol==4)
                <template v-if="menu==14">
                    <orden></orden>
                </template>
                <template v-if="menu==15">
                    <programa></programa>
                </template>
            @endif
        @endif
        
        
    @endsection