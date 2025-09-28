@extends('layouts.backoffice')

@section('title', 'จัดการผู้ใช้')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">ผู้ใช้</h5>
                <button class="btn btn-primary" id="btnAdd">
                    <i class="ti ti-plus"></i> เพิ่มผู้ใช้
                </button>
            </div>

            <div class="table-responsive">
                <table id="usersTable" class="table table-bordered w-100 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>อีเมล</th>
                            <th>ชื่อ</th>
                            <th>นามสกุล</th>
                            <th>เพศ</th>
                            <th>สร้างเมื่อ</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle">เพิ่มผู้ใช้</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm">
                        <input type="hidden" id="user_id">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">อีเมล</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ชื่อ</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">นามสกุล</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เพศ</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">- เลือก -</option>
                                    <option value="M">ชาย</option>
                                    <option value="F">หญิง</option>
                                    <option value="N">ไม่ระบุเพศ</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">รหัสผ่าน</label>
                                <input type="password" class="form-control" id="password" name="password">
                                <div class="form-text">กรณีแก้ไข หากไม่เปลี่ยนให้เว้นว่าง</div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary" id="btnSave">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
        <script src="{{ asset('js/additional-methods.min.js') }}"></script>
        <script>
            $(function() {
                const $form = $('#userForm');
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
                            maxlength: 100,
                            normalizer: v => $.trim(v)
                        },
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
                        gender: {
                            required: true
                        },
                        password: {
                            minlength: 6
                        }
                    },
                    messages: {
                        email: {
                            required: 'กรุณากรอกอีเมล',
                            email: 'รูปแบบอีเมลไม่ถูกต้อง',
                            maxlength: 'ไม่เกิน 100 ตัวอักษร'
                        },
                        first_name: {
                            required: 'กรุณากรอกชื่อ',
                            maxlength: 'ไม่เกิน 50 ตัวอักษร'
                        },
                        last_name: {
                            required: 'กรุณากรอกนามสกุล',
                            maxlength: 'ไม่เกิน 50 ตัวอักษร'
                        },
                        gender: {
                            required: 'กรุณาเลือกเพศ'
                        },
                        password: {
                            minlength: 'อย่างน้อย 6 ตัวอักษร'
                        }
                    },
                    errorClass: 'is-invalid',
                    errorPlacement: function(error, element) {
                        error.addClass('invalid-feedback');
                        error.insertAfter(element);
                    },
                    highlight: function(el) {
                        $(el).addClass('is-invalid').removeClass('is-valid');
                    },
                    unhighlight: function(el) {
                        $(el).removeClass('is-invalid');
                        $(el).next('.invalid-feedback').remove();
                    }
                });

                const table = $('#usersTable').DataTable({
                    serverSide: true,
                    processing: true,
                    ajax: {
                        url: '{{ url('backoffice/users/data') }}',
                        type: 'GET'
                    },
                    columns: [{
                            data: 'user_id',
                            name: 'user_id',
                            width: 60
                        },
                        {
                            data: 'email',
                            name: 'email'
                        },
                        {
                            data: 'first_name',
                            name: 'first_name'
                        },
                        {
                            data: 'last_name',
                            name: 'last_name'
                        },
                        {
                            data: 'gender',
                            name: 'gender',
                            render: d => (d === 'M' ? 'ชาย' : (d === 'F' ? 'หญิง' : 'ไม่ระบุเพศ')),
                            width: 100,
                            className: 'text-center'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at',
                            width: 160
                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            className: 'text-nowrap',
                            width: 100,
                            render: (row) => `
					<button class="btn btn-sm btn-info me-1 btn-edit" data-id="${row.user_id}">
						<i class="ti ti-edit"></i>
					</button>
					<button class="btn btn-sm btn-danger btn-delete" data-id="${row.user_id}">
						<i class="ti ti-trash"></i>
					</button>
				`
                        }
                    ],
                    order: [
                        [0, 'desc']
                    ]
                });

                const userModal = new bootstrap.Modal(document.getElementById('userModal'));
                const resetForm = () => {
                    $('#user_id').val('');
                    $('#userForm')[0].reset();
                    $('#password').attr('required', true);
                    $('#userModalTitle').text('เพิ่มผู้ใช้');
                };

                $('#btnAdd').on('click', function() {
                    resetForm();
                    userModal.show();
                });

                $('#usersTable').on('click', '.btn-edit', function() {
                    const id = $(this).data('id');
                    $.get(`{{ url('backoffice/users') }}/${id}`, (res) => {
                        $('#user_id').val(res.user_id);
                        $('#email').val(res.email);
                        $('#first_name').val(res.first_name);
                        $('#last_name').val(res.last_name);
                        $('#gender').val(res.gender);
                        $('#password').val('');
                        $('#password').removeAttr('required');
                        $('#userModalTitle').text('แก้ไขผู้ใช้');
                        userModal.show();
                    });
                });
                

                $('#btnSave').on('click', function() {
                    if (!$form.valid()) return;

                    const id = $('#user_id').val();
                    const method = id ? 'PUT' : 'POST';
                    const url = id ? `{{ url('backoffice/users') }}/${id}` : `{{ url('backoffice/users') }}`;

                    const payload = {
                        email: $('#email').val(),
                        first_name: $('#first_name').val(),
                        last_name: $('#last_name').val(),
                        gender: $('#gender').val(),
                        password: $('#password').val()
                    };

                    const btn = this;
                    startBtnLoading(btn, 'กำลังบันทึก...');

                    $.ajax({
                        url,
                        type: method,
                        data: payload,
                        success: function() {
                            userModal.hide();
                            table.ajax.reload(null, false);
                            showSwalSuccess('บันทึกสำเร็จ');
                        },

                        // error สที่ส่งมาจาก server
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                const errs = xhr.responseJSON.errors || {};
                                let msgs = Object.values(errs).flat().join('\n');
                                showSwalError(msgs || 'Validation error');
                            } else {
                                showSwalError('เกิดข้อผิดพลาด');
                            }
                        },
                        complete: function() {
                            endBtnLoading(btn);
                        }
                    });
                });

                $('#usersTable').on('click', '.btn-delete', function() {
                    const id = $(this).data('id');
                    confirmSwal('ยืนยันการลบผู้ใช้?').then((res) => {
                        if (!res.isConfirmed) return;
                        $.ajax({
                            url: `{{ url('backoffice/users') }}/${id}`,
                            type: 'DELETE',
                            success: function() {
                                table.ajax.reload(null, false);

                                showSwalSuccess('ลบข้อมูลสำเร็จ');
                            },
                            error: function() {
                                showSwalError('เกิดข้อผิดพลาด');
                            }
                        })
                    });
                });
            });
        </script>
    @endpush
@endsection
