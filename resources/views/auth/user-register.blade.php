@extends('layouts.guest')

@section('content')
    <div class="container my-5">
        <div class="row g-4 align-items-center">

            <div class="col-12 col-lg-6 text-center">
                <img src="{{ asset('images/login_bus.png') }}" class="img-fluid auth-bus-img" alt="Bus illustration" />
            </div>

            <div class="col-12 col-lg-6 d-flex justify-content-center mb-5">
                <div class="w-100" style="max-width: 520px;">
                    <div class="mb-2 text-center text-lg-start">
                        <h2 class="fw-semibold mb-1 text-dark">สมัครสมาชิก </h2>
                        <small class="text-secondary">สมัครสมาชิกเพื่อเริ่มต้นใช้งาน</small>
                    </div>


                    <form class="mt-3" id="registerForm" method="post" action="{{ url('/auth/users/register') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">ชื่อ</label>
                                <input id="first_name" name="first_name" type="text" class="form-control" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">นามสกุล</label>
                                <input id="last_name" name="last_name" type="text" class="form-control" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">อีเมล</label>
                            <input id="email" name="email" type="email" class="form-control" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label d-block">เพศ</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="gender_m" value="M">
                                <label class="form-check-label" for="gender_m">ชาย</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" id="gender_f" value="F">
                                <label class="form-check-label" for="gender_f">หญิง</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-12 mb-3">
                                <label for="password" class="form-label">รหัสผ่าน</label>
                                <input id="password" name="password" type="password" class="form-control" />
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label for="password_confirmation" class="form-label">ยืนยันรหัสผ่าน</label>
                                <input id="password_confirmation" name="password_confirmation" type="password"
                                    class="form-control" />
                            </div>
                        </div>

                        <div class="float-end">
                            <button class="btn btn-primary" id='registerBtn'>สมัครสมาชิก</button>
                        </div>
                        <div class="clearfix"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const $form = $('#registerForm');
        const $btn = $('#registerBtn');

        $form.validate({
            onkeyup: function(element) {
                $(element).valid();
            },
            onfocusout: function(element) {
                $(element).valid();
            },
            errorElement: 'div',
            rules: {
                first_name: {
                    required: true,
                    maxlength: 50,
                    normalizer: v => $.trim(v)
                },
                last_name: {
                    required: true,
                    maxlength: 50,
                    normalizer: v => $.trim(v)
                },
                email: {
                    required: true,
                    email: true,
                    maxlength: 100,
                    normalizer: v => $.trim(v)
                },
                gender: {
                    required: true
                },
                password: {
                    required: true,
                    minlength: 6
                },
                password_confirmation: {
                    required: true,
                    equalTo: '#password'
                }
            },
            messages: {
                first_name: {
                    required: 'กรุณากรอกชื่อ',
                    maxlength: 'ไม่เกิน 50 ตัวอักษร'
                },
                last_name: {
                    required: 'กรุณากรอกนามสกุล',
                    maxlength: 'ไม่เกิน 50 ตัวอักษร'
                },
                email: {
                    required: 'กรุณากรอกอีเมล',
                    email: 'รูปแบบอีเมลไม่ถูกต้อง',
                    maxlength: 'ไม่เกิน 100 ตัวอักษร'
                },
                gender: {
                    required: 'กรุณาเลือกเพศ'
                },
                password: {
                    required: 'กรุณากรอกรหัสผ่าน',
                    minlength: 'อย่างน้อย 6 ตัวอักษร'
                },
                password_confirmation: {
                    required: 'กรุณายืนยันรหัสผ่าน',
                    equalTo: 'รหัสผ่านไม่ตรงกัน'
                }
            },
            errorClass: 'is-invalid',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                if (element.attr('name') === 'gender') {
                    element.closest('.mb-3').append(error);
                } else {
                    error.insertAfter(element);
                }
            },
            success: function(label) {
                label.remove();
            },
            highlight: function(element) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid');
                var $next = $(element).next('.invalid-feedback');
                if ($next.length) {
                    $next.remove();
                }
            }
        });

        $form.on('submit', function(e) {
            e.preventDefault();
            if (!$form.valid()) return;

            startBtnLoading($btn[0]);
            $.ajax({
                    url: '/auth/users/register',
                    method: 'POST',
                    data: $form.serialize(),
                })
                .done(function(res) {
                    window.location.href = '/';
                })
                .fail(function(xhr) {
                    endBtnLoading($btn[0]);
                    alert(xhr.responseJSON.message);
                })

        });
    </script>
@endpush
