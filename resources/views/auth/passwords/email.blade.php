@extends('auth.contenido')

@section('login')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card-group mb-0">
      <div class="card p-4">
        <form class="form-horizontal was-validated" method="POST" action="{{ route('password.email') }}">
          {{ csrf_field() }}

          <div class="card-body">
            <div class="text-center mb-4">
              <img src="{{ asset('img/LOGO-LUPA.jpg') }}" alt="Logo" style="width: 120px;">
            </div>

            <h1>Recuperar Clave</h1>
            <p class="text-muted">Ingresa tu correo para recibir el enlace de restablecimiento</p>

            @if (session('status'))
              <div class="alert alert-success" role="alert">
                {{ session('status') }}
              </div>
            @endif

            <div class="form-group mb-4">
              <span class="input-group-addon"><i class="icon-envelope"></i></span>
              <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" placeholder="Correo Electrónico" required autocomplete="email" autofocus>
              @if ($errors->has('email'))
                <span class="invalid-feedback" role="alert" style="display: block;">
                  <strong>{{ $errors->first('email') }}</strong>
                </span>
              @endif
            </div>

            <div class="row">
              <div class="col-6">
                <button type="submit" class="btn btn-primary px-4">Enviar Enlace</button>
              </div>
              <div class="col-6 text-right">
                <a href="{{ route('login') }}" class="btn btn-link px-0 text-white bg-transparent border-0" style="color: #20a8d8 !important; text-decoration: none;">Volver al Login</a>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
