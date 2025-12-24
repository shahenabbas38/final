@extends('adminlte::auth.login')

@section('auth_type', 'login')

{{-- استخدام رابط كامل يمنع النظام من إضافة أي كلمات زائدة مثل api --}}
@php( $login_url = url('/panel/login') ) 
@php( $register_url = url('/panel/register') )