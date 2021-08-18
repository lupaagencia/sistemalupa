<!DOCTYPE html>
<html lang="es">
@if(Auth::check())
    @include('backend.principalAdmin')
@else
    @include('frontend.principalWeb')
@endif

</html>