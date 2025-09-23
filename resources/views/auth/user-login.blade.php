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
                        <h2 class="fw-semibold mb-1 text-dark">ยินดีตอนรับ</h2>
                        <small class="text-secondary">เข้าสู่ระบบเพื่อใช้งาน</small>
                    </div>

                    <form class="mt-3" id="loginForm" method="post" action="{{ url('/auth/users/login') }}">
                        <div class="mb-3">
                            <label for="email" class="form-label">อีเมล</label>
                            <input id="email" name="email" type="email" class="form-control" />
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">รหัสผ่าน</label>
                            <input id="password" name="password" type="password" class="form-control" />
                        </div>

                        <div class="float-end">
                            <button class="btn btn-primary" id="loginBtn">เข้าสู่ระบบ</button>
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
        const $form = $('#loginForm');
        const $btn = $('#loginBtn');

        $form.validate({
            onkeyup: function(el) {
                $(el).valid();
            },
            onfocusout: function(el) {
                $(el).valid();
            },
            errorElement: 'div',
            rules: {
                email: {
                    required: true,
                    email: true,
                    normalizer: v => $.trim(v)
                },
                password: {
                    required: true
                }
            },
            messages: {
                email: {
                    required: 'กรุณากรอกอีเมล',
                    email: 'รูปแบบอีเมลไม่ถูกต้อง'
                },
                password: {
                    required: 'กรุณากรอกรหัสผ่าน'
                }
            },
            errorClass: 'is-invalid',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                error.insertAfter(element);
            },
            success: function(label) {
                label.remove();
            },
            highlight: function(el) {
                $(el).addClass('is-invalid');
            },
            unhighlight: function(el) {
                $(el).removeClass('is-invalid');
            }
        });

        $form.on('submit', function(e) {
            e.preventDefault();
            if (!$form.valid()) return;

            startBtnLoading($btn[0]);
            $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    dataType: 'json'
                })
                .done(function(res) {
                    window.location.href = '/';
                })
                .fail(function(err) {
                    alert(err.responseJSON.message);
                })
                .always(function(){
                    endBtnLoading($btn[0]);
                })

        });
    </script>
@endpush
