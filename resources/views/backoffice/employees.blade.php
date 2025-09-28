@extends('layouts.backoffice')

@section('title', 'Employees Management')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">จัดการพนักงาน</h5>
                <button class="btn btn-primary" id="btnAddEmp"><i class="ti ti-plus"></i> เพิ่มพนักงาน</button>
            </div>
            <div class="table-responsive">
                <table id="empTable" class="table table-bordered w-100 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>อีเมล</th>
                            <th>ชื่อ</th>
                            <th>นามสกุล</th>
                            <th>เพศ</th>
                            <th>แผนก</th>
                            <th>ตำแหน่ง</th>
                            <th>สร้างเมื่อ</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="empModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="empModalTitle">เพิ่มพนักงาน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="empForm">
                        <input type="hidden" id="employee_id">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">อีเมล</label>
                                <input type="email" class="form-control" id="emp_email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ชื่อ</label>
                                <input type="text" class="form-control" id="emp_first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">นามสกุล</label>
                                <input type="text" class="form-control" id="emp_last_name" name="last_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เพศ</label>
                                <select class="form-select" id="emp_gender" name="gender" required>
                                    <option value="">- เลือก -</option>
                                    <option value="M">ชาย</option>
                                    <option value="F">หญิง</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">รหัสผ่าน</label>
                                <input type="password" class="form-control" id="emp_password" name="password">
                                <div class="form-text">กรณีแก้ไข หากไม่เปลี่ยนให้เว้นว่าง</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">แผนก</label>
                                <select class="form-select" id="emp_dept_id" name="dept_id" required></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ตำแหน่ง</label>
                                <select class="form-select" id="emp_position_id" name="position_id" required></select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary" id="btnSaveEmp">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
        <script src="{{ asset('js/additional-methods.min.js') }}"></script>
        <script>
            $(function() {
                const $form = $('#empForm');
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
                        dept_id: {
                            required: true
                        },
                        position_id: {
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
                        dept_id: {
                            required: 'กรุณาเลือกแผนก'
                        },
                        position_id: {
                            required: 'กรุณาเลือกตำแหน่ง'
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

                function loadOptions() {
                    return Promise.all([
                        $.get(`{{ url('backoffice/departments/list') }}`),
                        $.get(`{{ url('backoffice/positions/list') }}`)
                    ]).then(([depts, poss]) => {
                        const $dept = $('#emp_dept_id').empty().append('<option value="">- เลือก -</option>');
                        depts.forEach(x => $dept.append(`<option value="${x.id}">${x.name}</option>`));
                        const $pos = $('#emp_position_id').empty().append(
                            '<option value="">- เลือก -</option>');
                        poss.forEach(x => $pos.append(`<option value="${x.id}">${x.name}</option>`));
                    });
                }

                const table = $('#empTable').DataTable({
                    serverSide: true,
                    processing: true,
                    ajax: {
                        url: `{{ url('backoffice/employees/data') }}`,
                        type: 'GET'
                    },
                    columns: [{
                            data: 'employee_id',
                            width: 60
                        },
                        {
                            data: 'email'
                        },
                        {
                            data: 'first_name'
                        },
                        {
                            data: 'last_name'
                        },
                        {
                            data: 'gender',
                            render: d => d === 'M' ? 'ชาย' : 'หญิง',
                            width: 80,
                            className: 'text-center'
                        },
                        {
                            data: 'dept_name'
                        },
                        {
                            data: 'position_name'
                        },
                        {
                            data: 'created_at',
                            width: 160
                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            className: 'text-nowrap',
                            width: 100,
                            render: (row) => `
        <button class="btn btn-sm btn-info me-1 btn-edit" data-id="${row.employee_id}"><i class="ti ti-edit"></i></button>
        <button class="btn btn-sm btn-danger btn-delete" data-id="${row.employee_id}"><i class="ti ti-trash"></i></button>
      `
                        }
                    ],
                    order: [
                        [0, 'desc']
                    ]
                });

                const modal = new bootstrap.Modal(document.getElementById('empModal'));
                $('#btnAddEmp').on('click', function() {
                    $('#employee_id').val('');
                    $('#empForm')[0].reset();
                    $('#emp_password').attr('required', true);
                    $('#empModalTitle').text('เพิ่มพนักงาน');
                    loadOptions().then(() => modal.show());
                });

                $('#empTable').on('click', '.btn-edit', function() {

                    const id = $(this).data('id');

                    $.get(`{{ url('backoffice/employees') }}/${id}`, (res) => {

                        $('#employee_id').val(res.employee_id);
                        $('#emp_email').val(res.email);
                        $('#emp_first_name').val(res.first_name);
                        $('#emp_last_name').val(res.last_name);
                        $('#emp_gender').val(res.gender);
                        $('#emp_password').val('');
                        $('#emp_password').removeAttr('required');
                        $('#empModalTitle').text('แก้ไขพนักงาน');
                        loadOptions().then(() => {
                            $('#emp_dept_id').val(res.dept_id);
                            $('#emp_position_id').val(res.position_id);
                            modal.show();
                        });


                        
                    });
                });

                $('#btnSaveEmp').on('click', function() {

                    if (!$form.valid()) return;

                    const id = $('#employee_id').val();
                    const method = id ? 'PUT' : 'POST';
                    const url = id ? `{{ url('backoffice/employees') }}/${id}` :
                        `{{ url('backoffice/employees') }}`;

                    const payload = {
                        email: $('#emp_email').val(),
                        first_name: $('#emp_first_name').val(),
                        last_name: $('#emp_last_name').val(),
                        gender: $('#emp_gender').val(),
                        dept_id: $('#emp_dept_id').val(),
                        position_id: $('#emp_position_id').val(),
                        password: $('#emp_password').val()
                    };


                    const btn = this;
                    startBtnLoading(btn, 'กำลังบันทึก...');

                    $.ajax({
                        url,
                        type: method,
                        data: payload,

                        success: function() {

                            modal.hide();
                            table.ajax.reload(null, false);

                    
                            showSwalSuccess('บันทึกสำเร็จ');

                        },

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

                $('#empTable').on('click', '.btn-delete', function() {
                    const id = $(this).data('id');
                    confirmSwal('ยืนยันการลบพนักงาน?').then((res) => {
                        if (!res.isConfirmed) return;
                        $.ajax({
                            url: `{{ url('backoffice/employees') }}/${id}`,
                            type: 'DELETE',
                            success: function() {
                                table.ajax.reload(null, false);

                                showSwalSuccess('ลบข้อมูลสำเร็จ');

                            },
                            error: function() {
                                showSwalError('เกิดข้อผิดพลาด');
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
@endsection
