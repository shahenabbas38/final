@extends('adminlte::auth.register')

@section('auth_header', 'تسجيل حساب مدير جديد')

{{-- التعديل: نستخدم route لأنك فعلت use_route_url في الإعدادات --}}
@php( $register_url = route('register') )
@php( $login_url = route('login') )

@section('auth_body')
    <form action="{{ $register_url }}" method="post">
        @csrf

        {{-- حقل البريد الإلكتروني --}}
        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="البريد الإلكتروني" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
            @error('email')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        {{-- حقل رقم الهاتف --}}
        <div class="input-group mb-3">
            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                   value="{{ old('phone') }}" placeholder="رقم الهاتف" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-phone"></span>
                </div>
            </div>
            @error('phone')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        {{-- حقل كلمة المرور --}}
        <div class="input-group mb-3">
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   placeholder="كلمة المرور" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
            @error('password')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        {{-- حقل تأكيد كلمة المرور --}}
        <div class="input-group mb-3">
            <input type="password" name="password_confirmation" class="form-control"
                   placeholder="تأكيد كلمة المرور" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-block btn-flat btn-primary">
            <span class="fas fa-user-plus"></span>
            إنشاء الحساب
        </button>
    </form>
@stop