@extends('layouts.backoffice')

@section('title', 'จัดการเมนู')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">เมนู</h5>
                <button class="btn btn-primary" id="btnAddMenu"><i class="ti ti-plus"></i> เพิ่มเมนู</button>
            </div>
            <div class="table-responsive">
                <table id="menuTable" class="table table-bordered w-100 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Key</th>
                            <th>ชื่อเมนู</th>
                            <th>สร้างเมื่อ</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="menuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="menuModalTitle">เพิ่มเมนู</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="menuForm">
                        <input type="hidden" id="menu_id">
                        <div class="mb-3">
                            <label class="form-label">Key</label>
                            <input type="text" class="form-control" id="key_name" name="key_name" required
                                maxlength="50">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ชื่อเมนู</label>
                            <input type="text" class="form-control" id="menu_name" name="name" required
                                maxlength="100">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button class="btn btn-primary" id="btnSaveMenu">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
        <script src="{{ asset('js/additional-methods.min.js') }}"></script>
        <script>
            $(function() {
                const table = $('#menuTable').DataTable({
                    serverSide: true,
                    processing: true,
                    ajax: {
                        url: '{{ url('backoffice/menus/data') }}',
                        type: 'GET'
                    },
                    columns: [{
                            data: 'menu_id',
                            width: 60
                        },
                        {
                            data: 'key_name'
                        },
                        {
                            data: 'name'
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
          <button class="btn btn-sm btn-info me-1 btn-edit" data-id="${row.menu_id}"><i class="ti ti-edit"></i></button>
          <button class="btn btn-sm btn-danger btn-delete" data-id="${row.menu_id}"><i class="ti ti-trash"></i></button>
      `
                        }
                    ],
                    order: [
                        [0, 'desc']
                    ]
                });

                const modal = new bootstrap.Modal(document.getElementById('menuModal'));
                const $form = $('#menuForm');
                $form.validate({
                    onkeyup: (el) => $(el).valid(),
                    onfocusout: (el) => $(el).valid(),
                    errorElement: 'div',
                    rules: {
                        key_name: {
                            required: true,
                            maxlength: 50,
                            normalizer: v => $.trim(v)
                        },
                        name: {
                            required: true,
                            maxlength: 100,
                            normalizer: v => $.trim(v)
                        }
                    },
                    messages: {
                        key_name: {
                            required: 'กรุณากรอก Key',
                            maxlength: 'ไม่เกิน 50 ตัวอักษร'
                        },
                        name: {
                            required: 'กรุณากรอกชื่อเมนู',
                            maxlength: 'ไม่เกิน 100 ตัวอักษร'
                        }
                    },
                    errorClass: 'is-invalid',
                    errorPlacement: (e, el) => {
                        e.addClass('invalid-feedback');
                        e.insertAfter(el);
                    },
                    highlight: (el) => $(el).addClass('is-invalid').removeClass('is-valid'),
                    unhighlight: (el) => {
                        $(el).removeClass('is-invalid');
                        $(el).next('.invalid-feedback').remove();
                    }
                });

                $('#btnAddMenu').on('click', () => {
                    $('#menu_id').val('');
                    $form[0].reset();
                    $('#menuModalTitle').text('เพิ่มเมนู');
                    modal.show();
                });
                $('#menuTable').on('click', '.btn-edit', function() {
                    const id = $(this).data('id');
                    $.get(`{{ url('backoffice/menus') }}/${id}`, (res) => {
                        $('#menu_id').val(res.menu_id);
                        $('#key_name').val(res.key_name);
                        $('#menu_name').val(res.name);
                        $('#menuModalTitle').text('แก้ไขเมนู');
                        modal.show();
                    });
                });
                $('#btnSaveMenu').on('click', function() {
                    if (!$form.valid()) return;
                    const id = $('#menu_id').val();
                    const btn = this;
                    startBtnLoading(btn, 'กำลังบันทึก...');
                    $.ajax({
                        url: id ? `{{ url('backoffice/menus') }}/${id}` :
                            `{{ url('backoffice/menus') }}`,
                        type: id ? 'PUT' : 'POST',
                        data: {
                            key_name: $('#key_name').val(),
                            name: $('#menu_name').val()
                        },
                        success: function() {
                            modal.hide();
                            table.ajax.reload(null, false);
                            showSwalSuccess('บันทึกสำเร็จ');
                        },
                        error: function(xhr) {
                            showSwalError(xhr.responseJSON?.message || 'Error');
                        },
                        complete: function() {
                            endBtnLoading(btn);
                        }
                    });
                });
                $('#menuTable').on('click', '.btn-delete', function() {
                    const id = $(this).data('id');
                    confirmSwal('ยืนยันการลบเมนู?').then(res => {
                        if (!res.isConfirmed) return;
                        $.ajax({
                            url: `{{ url('backoffice/menus') }}/${id}`,
                            type: 'DELETE',
                            success: function() {
                                table.ajax.reload(null, false);
                                showSwalSuccess('ลบข้อมูลสำเร็จ');
                            },
                            error: function() {
                                showSwalError('Error');
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
@endsection
