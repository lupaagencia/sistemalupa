@extends('auth.contenido')

@section('login')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card-group mb-0">
      <div class="card p-4">
        <form class="form-horizontal was-validated" method="POST" action="{{ route('password.update') }}">
          {{ csrf_field() }}
          <input type="hidden" name="token" value="{{ $token }}">

          <div class="card-body">
            <div class="text-center mb-4">
              <img src="{{ asset('img/LOGO-LUPA.jpg') }}" alt="Logo" style="width: 120px;">
            </div>

            <h1>Restablecer Clave</h1>
            <p class="text-muted">Ingresa tus datos y tu nueva contraseña</p>

            <div class="form-group mb-3">
              <span class="input-group-addon"><i class="icon-envelope"></i></span>
              <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" placeholder="Correo Electrónico" required autocomplete="email" autofocus>
              @if ($errors->has('email'))
                <span class="invalid-feedback" role="alert" style="display: block;">
                  <strong>{{ $errors->first('email') }}</strong>
                </span>
              @endif
            </div>

            <div class="form-group mb-3">
              <span class="input-group-addon"><i class="icon-lock"></i></span>
              <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" placeholder="Nueva Contraseña" required autocomplete="new-password">
              @if ($errors->has('password'))
                <span class="invalid-feedback" role="alert" style="display: block;">
                  <strong>{{ $errors->first('password') }}</strong>
                </span>
              @endif
            </div>

            <div class="form-group mb-4">
              <span class="input-group-addon"><i class="icon-lock"></i></span>
              <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Confirmar Nueva Contraseña" required autocomplete="new-password">
            </div>

            <div class="row">
              <div class="col-6">
                <button type="submit" class="btn btn-primary px-4">Restablecer Clave</button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
