@extends('auth.contenido')



@section('login')

  <div class="row justify-content-center">

    <div class="col-md-8">

      <div class="card-group mb-0">

        <div class="card p-4">

          <form class="form-horizoantal was-validated" method="POST" action="{{ route('login')}}">

            {{ csrf_field() }}

            <div class="card-body">

              <div class="text-center mb-4">
                <img src="{{ asset('img/LOGO-LUPA.jpg') }}" alt="Logo" style="width: 120px;">
              </div>

              <h1>Acceder</h1>

              <p class="text-muted">Control de acceso al sistema</p>

              <div class="form-group mb-3{{$errors->has('usuario' ? 'is-invalid' : '')}}">

                <span class="input-group-addon"><i class="icon-user"></i></span>

                <input type="text" name="usuario" value="{{old('usuario')}}" id="usuario" class="form-control"
                  placeholder="Usuario">

                {!!$errors->first('usuario', '<span class="invalid-feedback">:message</span>')!!}

              </div>

              <div class="form-group mb-4{{$errors->has('password' ? 'is-invalid' : '')}}">

                <span class="input-group-addon"><i class="icon-lock"></i></span>

                <input type="password" name="password" id="password" class="form-control" placeholder="Password">

                {!!$errors->first('password', '<span class="invalid-feedback">:message</span>')!!}

              </div>

              <div class="row">

                <div class="col-6">

                  <button type="submit" class="btn btn-primary px-4">Acceder</button>

                </div>

                <div class="col-6 text-right">

                  <a href="{{ route('password.request') }}" class="btn btn-link px-0 text-white bg-transparent border-0" style="color: #20a8d8 !important; text-decoration: none;">¿Olvidó su contraseña?</a>

                </div>

              </div>

            </div>

          </form>

        </div>

        <div class="card text-white bg-primary py-5 d-md-down-none" style="width:44%">

          <div class="card-body text-center">

            <div>
              <img src="{{ asset('img/LOGO-LUPA.jpg') }}" alt="Logo Lupa" class="img-fluid mb-3"
                style="border-radius: 10px; background: white; padding: 10px; width: 80%;">
            </div>

          </div>

        </div>

      </div>

    </div>

  </div>

@endsection