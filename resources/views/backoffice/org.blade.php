@extends('layouts.backoffice')

@section('title', 'จัดการแผนกและตำแหน่ง')

@section('content')
    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">แผนก</h5>
                        <button class="btn btn-primary" id="btnAddDept"><i class="ti ti-plus"></i> เพิ่มแผนก</button>
                    </div>
                    <div class="table-responsive">
                        <table id="deptTable" class="table table-bordered w-100 text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>ชื่อแผนก</th>
                                    <th>สร้างเมื่อ</th>
                                    <th>การจัดการ</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">ตำแหน่ง</h5>
                        <button class="btn btn-primary" id="btnAddPos"><i class="ti ti-plus"></i> เพิ่มตำแหน่ง</button>
                    </div>
                    <div class="table-responsive">
                        <table id="posTable" class="table table-bordered w-100 text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>ชื่อตำแหน่ง</th>
                                    <th>สร้างเมื่อ</th>
                                    <th>การจัดการ</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dept Modal -->
    <div class="modal fade" id="deptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deptModalTitle">เพิ่มแผนก</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="deptForm">
                        <input type="hidden" id="dept_id">
                        <div class="mb-3">
                            <label class="form-label">ชื่อแผนก</label>
                            <input type="text" class="form-control" id="dept_name" name="dept_name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary" id="btnSaveDept">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Position Modal -->
    <div class="modal fade" id="posModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="posModalTitle">เพิ่มตำแหน่ง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="posForm">
                        <input type="hidden" id="position_id">
                        <div class="mb-3">
                            <label class="form-label">ชื่อตำแหน่ง</label>
                            <input type="text" class="form-control" id="position_name" name="position_name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary" id="btnSavePos">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
        <script src="{{ asset('js/additional-methods.min.js') }}"></script>
        <script>
            $(function() {
                function escapeHtml(str) {
                    return String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
                }
                // Validate: Departments
                const $deptForm = $('#deptForm');
                $deptForm.validate({
                    onkeyup: function(el) {
                        $(el).valid();
                    },
                    onfocusout: function(el) {
                        $(el).valid();
                    },
                    errorElement: 'div',
                    rules: {
                        dept_name: {
                            required: true,
                            maxlength: 100,
                            normalizer: v => $.trim(v)
                        }
                    },
                    messages: {
                        dept_name: {
                            required: 'กรุณากรอกชื่อแผนก',
                            maxlength: 'ไม่เกิน 100 ตัวอักษร'
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
                // Departments DataTable
                const deptTable = $('#deptTable').DataTable({
                    serverSide: true,
                    processing: true,
                    ajax: {
                        url: '{{ url('backoffice/departments/data') }}',
                        type: 'GET'
                    },
                    columns: [{
                            data: 'dept_id',
                            width: 60
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
              <button class="btn btn-sm btn-info me-1 btn-edit-dept" data-id="${row.dept_id}"><i class="ti ti-edit"></i></button>
              <button class="btn btn-sm btn-danger btn-delete-dept" data-id="${row.dept_id}"><i class="ti ti-trash"></i></button>
          `
                        }
                    ],
                    order: [
                        [0, 'desc']
                    ]
                });

                const deptModal = new bootstrap.Modal(document.getElementById('deptModal'));
                $('#btnAddDept').on('click', () => {
                    $('#dept_id').val('');
                    $('#dept_name').val('');
                    $('#deptModalTitle').text('เพิ่มแผนก');
                    deptModal.show();
                });
                $('#deptTable').on('click', '.btn-edit-dept', function() {

                    const id = $(this).data('id');


                    $.get(`{{ url('backoffice/departments') }}/${id}`, (res) => {


                        $('#dept_id').val(res.dept_id);
                        $('#dept_name').val(res.name);
                        $('#deptModalTitle').text('แก้ไขแผนก');



                        deptModal.show();


                    });
                });
                $('#btnSaveDept').on('click', function() {


                    if (!$deptForm.valid()) return;



                    const id = $('#dept_id').val();



                    const btn = this;
                    startBtnLoading(btn, 'กำลังบันทึก...');




                    $.ajax({
                        url: id ? `{{ url('backoffice/departments') }}/${id}` :
                            `{{ url('backoffice/departments') }}`,
                        type: id ? 'PUT' : 'POST',
                        data: {
                            name: $('#dept_name').val()
                        },
                        success: function() {

                            deptModal.hide();


                            deptTable.ajax.reload(null, false);


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
                $('#deptTable').on('click', '.btn-delete-dept', function() {


                    const id = $(this).data('id');


                    confirmSwal('ยืนยันการลบแผนก?').then((res) => {

                        if (!res.isConfirmed) return;



                        $.ajax({
                            url: `{{ url('backoffice/departments') }}/${id}`,
                            type: 'DELETE',
                            success: function() {

                                deptTable.ajax.reload(null, false);
                                
                                showSwalSuccess('ลบข้อมูลสำเร็จ');
                            },
                            error: function() {
                                showSwalError('Error');
                            }
                        });
                    });
                });

                // Positions DataTable
                // Validate: Positions
                const $posForm = $('#posForm');
                $posForm.validate({
                    onkeyup: function(el) {
                        $(el).valid();
                    },
                    onfocusout: function(el) {
                        $(el).valid();
                    },
                    errorElement: 'div',
                    rules: {
                        position_name: {
                            required: true,
                            maxlength: 100,
                            normalizer: v => $.trim(v)
                        }
                    },
                    messages: {
                        position_name: {
                            required: 'กรุณากรอกชื่อตำแหน่ง',
                            maxlength: 'ไม่เกิน 100 ตัวอักษร'
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
                const posTable = $('#posTable').DataTable({
                    serverSide: true,
                    processing: true,
                    ajax: {
                        url: '{{ url('backoffice/positions/data') }}',
                        type: 'GET'
                    },
                    columns: [{
                            data: 'position_id',
                            width: 60
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
              <button class="btn btn-sm btn-info me-1 btn-edit-pos" data-id="${row.position_id}"><i class="ti ti-edit"></i></button>
              <button class="btn btn-sm btn-dark me-1 btn-access-pos" data-id="${row.position_id}" data-name="${escapeHtml(row.name)}"><i class="ti ti-lock"></i></button>
              <button class="btn btn-sm btn-danger btn-delete-pos" data-id="${row.position_id}"><i class="ti ti-trash"></i></button>
          `
                        }
                    ],
                    order: [
                        [0, 'desc']
                    ]
                });

                const posModal = new bootstrap.Modal(document.getElementById('posModal'));
                $('#btnAddPos').on('click', () => {
                    $('#position_id').val('');
                    $('#position_name').val('');
                    $('#posModalTitle').text('เพิ่มตำแหน่ง');
                    posModal.show();
                });
                $('#posTable').on('click', '.btn-edit-pos', function() {
                    const id = $(this).data('id');
                    $.get(`{{ url('backoffice/positions') }}/${id}`, (res) => {
                        $('#position_id').val(res.position_id);
                        $('#position_name').val(res.name);
                        $('#posModalTitle').text('แก้ไขตำแหน่ง');
                        posModal.show();
                    });
                });
                $('#btnSavePos').on('click', function() {

                    if (!$posForm.valid()) return;


                    const id = $('#position_id').val();


                    const btn = this;
                    startBtnLoading(btn, 'กำลังบันทึก...');


                    
                    $.ajax({
                        url: id ? `{{ url('backoffice/positions') }}/${id}` :
                            `{{ url('backoffice/positions') }}`,
                        type: id ? 'PUT' : 'POST',
                        data: {
                            name: $('#position_name').val()
                        },
                        success: function() {
                            posModal.hide();
                            posTable.ajax.reload(null, false);
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
                $('#posTable').on('click', '.btn-delete-pos', function() {
                    const id = $(this).data('id');
                    confirmSwal('ยืนยันการลบตำแหน่ง?').then((res) => {
                        if (!res.isConfirmed) return;
                        $.ajax({
                            url: `{{ url('backoffice/positions') }}/${id}`,
                            type: 'DELETE',
                            success: function() {
                                posTable.ajax.reload(null, false);
                                showSwalSuccess('ลบข้อมูลสำเร็จ');
                            },
                            error: function() {
                                showSwalError('Error');
                            }
                        });
                    });
                });

                // Manage Access Modal
                const accessModalEl = document.getElementById('accessModal');
                const accessModal = new bootstrap.Modal(accessModalEl);
                let currentPosId = null;

                function renderMenuCheckboxes(menus, selectedIds) {
                    const set = new Set(selectedIds.map(Number));
                    const html = menus.map(m => {
                        const checked = set.has(Number(m.id)) ? 'checked' : '';
                        return `<div class="form-check form-check-inline me-4 mb-2">
            <input class="form-check-input menu-chk" type="checkbox" value="${m.id}" id="menu_${m.id}" ${checked}>
            <label class="form-check-label" for="menu_${m.id}">${m.name} <small class="text-muted">(${m.key_name})</small></label>
          </div>`;
                    }).join('');
                    $('#menuChecklist').html(html || '<div class="text-muted">ไม่มีเมนู</div>');
                }

                function loadMenusAndAccess(positionId) {
                    // Fetch all menus and current access in parallel
                    return Promise.all([
                        $.get(`{{ url('backoffice/menus/all') }}`),
                        $.get(`{{ url('backoffice/positions') }}/${positionId}/menus`)
                    ]);
                }
                $('#posTable').on('click', '.btn-access-pos', function() {
                    const id = $(this).data('id');
                    const name = $(this).data('name');
                    currentPosId = id;
                    $('#accessModalTitle').html(`กำหนดสิทธิ์เมนู — ${name}`);
                    $('#menuChecklist').html('<div class="text-muted">กำลังโหลด...</div>');
                    accessModal.show();
                    loadMenusAndAccess(id).then(([menus, selected]) => {
                        renderMenuCheckboxes(menus, selected);
                    }).catch(() => {
                        $('#menuChecklist').html('<div class="text-danger">โหลดเมนูล้มเหลว</div>');
                    });
                });
                $('#btnSaveAccess').on('click', function() {
                    if (!currentPosId) return;
                    const btn = this;
                    startBtnLoading(btn, 'กำลังบันทึก...');
                    const menu_ids = $('.menu-chk:checked').map((i, el) => Number(el.value)).get();
                    $.ajax({
                        url: `{{ url('backoffice/positions') }}/${currentPosId}/menus`,
                        type: 'POST',
                        data: {
                            menu_ids
                        },
                        success: function() {
                            showSwalSuccess('บันทึกสิทธิ์สำเร็จ');
                            accessModal.hide();
                        },
                        error: function() {
                            showSwalError('บันทึกล้มเหลว');
                        },
                        complete: function() {
                            endBtnLoading(btn);
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection

<!-- Access Modal -->
<div class="modal fade" id="accessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accessModalTitle">กำหนดสิทธิ์เมนู</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="menuChecklist" class="d-flex flex-wrap"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-primary" id="btnSaveAccess">บันทึก</button>
            </div>
        </div>
    </div>
</div>
