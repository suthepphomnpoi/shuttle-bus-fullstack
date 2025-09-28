@extends('layouts.backoffice')

@section('title', 'จัดการจุดรับ–ส่งของเส้นทาง')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">สถานที่ของเส้นทาง: <span id="routeName"></span></h5>
                    <small class="text-muted">ลากเพื่อจัดลำดับ และแก้ไขเวลาเดินทางสะสม (นาที)</small>
                </div>
                <div>
                    <a href="{{ url('backoffice/routes-places') }}" class="btn btn-secondary me-2"><i
                            class="ti ti-arrow-left"></i> กลับ</a>
                    <button class="btn btn-primary" id="btnAddRoutePlace"><i class="ti ti-plus"></i> เพิ่มจุด</button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="routePlaceTable" class="table table-bordered w-100 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>ลำดับ</th>
                            <th>จุดรับ–ส่ง</th>
                            <th>เวลา (นาที)</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="routePlaceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="routePlaceModalTitle">เพิ่มจุด</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="routePlaceForm">
                        <input type="hidden" id="route_place_id">
                        <div class="mb-3">
                            <label class="form-label">สถานที่</label>
                            <select class="form-select" id="rp_place_id" name="place_id" required></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">เวลา (นาที)</label>
                            <input type="number" min="0" class="form-control" id="rp_duration_min"
                                name="duration_min" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button class="btn btn-primary" id="btnSaveRoutePlace">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
        <script src="{{ asset('js/additional-methods.min.js') }}"></script>
        <script>
            function escapeHtml(text) {
                return $('<div>').text(text ?? '').html();
            }
            $(function() {
                const routeId = {{ request()->route('route') ?? 'null' }};
                if (!routeId) {
                    showSwalError('ไม่พบเส้นทาง');
                    return;
                }

                // Load route name
                $.get(`{{ url('backoffice/routes') }}/${routeId}`, res => {
                    $('#routeName').text(res.name);
                });

                // Load places to select
                function loadAllPlaces(selected) {
                    $.get(`{{ url('backoffice/places/list') }}`, resp => {
                        const opts = (resp || []).map(r =>
                            `<option value="${r.place_id}">${escapeHtml(r.name)}</option>`).join('');
                        $('#rp_place_id').html(`<option value=\"\">-- เลือก --</option>` + opts);
                        if (selected) $('#rp_place_id').val(selected);
                    });
                }

                const table = $('#routePlaceTable').DataTable({
                    serverSide: true,
                    processing: true,
                    ajax: {
                        url: `{{ url('backoffice/routes') }}/${routeId}/route-places`,
                        type: 'GET'
                    },
                    rowId: r => `rp_${r.route_place_id}`,
                    columns: [{
                            data: 'sequence_no',
                            width: 80
                        },
                        {
                            data: 'place_name'
                        },
                        {
                            data: 'duration_min',
                            width: 140
                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            className: 'text-nowrap',
                            width: 120,
                            render: (row) =>
                                `
          <button class="btn btn-sm btn-info me-1 btn-edit" data-id="${row.route_place_id}"><i class="ti ti-edit"></i></button>
          <button class="btn btn-sm btn-danger btn-del" data-id="${row.route_place_id}"><i class="ti ti-trash"></i></button>`
                        }
                    ],
                    order: [
                        [0, 'asc']
                    ]
                });

                // Drag reorder using HTML5 drag + minimal helper
                let draggingId = null;
                $('#routePlaceTable tbody').on('dragstart', 'tr', function() {
                    draggingId = $(this).attr('id');
                });
                $('#routePlaceTable tbody').on('dragover', 'tr', function(e) {
                    e.preventDefault();
                });
                $('#routePlaceTable tbody').on('drop', 'tr', function(e) {
                    e.preventDefault();
                    if (!draggingId) return;
                    const $drag = $('#' + draggingId);
                    if (this === $drag[0]) return;
                    if ($(this).index() > $drag.index()) $(this).after($drag);
                    else $(this).before($drag);
                    draggingId = null;
                    saveOrder();
                });
                // Make rows draggable
                $('#routePlaceTable').on('draw.dt', () => {
                    $('#routePlaceTable tbody tr').attr('draggable', true);
                });

                function saveOrder() {
                    const ids = $('#routePlaceTable tbody tr')
                        .map(function() {
                            return $(this).attr('id').replace('rp_', '');
                        })
                        .get()
                        .map(v => parseInt(v, 10));
                    $.post(`{{ url('backoffice/routes') }}/${routeId}/route-places/reorder`, {
                        order: ids
                    }, () => {
                        table.ajax.reload(null, false);

                    }).fail(xhr => {
                        showSwalError(xhr.responseJSON?.message || 'Error');
                        table.ajax.reload(null, false);
                    });
                }

                const modal = new bootstrap.Modal(document.getElementById('routePlaceModal'));
                const $form = $('#routePlaceForm');
                $form.validate({
                    onkeyup: (el) => $(el).valid(),
                    onfocusout: (el) => $(el).valid(),
                    errorElement: 'div',
                    rules: {
                        place_id: {
                            required: true
                        },
                        duration_min: {
                            required: true,
                            digits: true
                        }
                    },
                    messages: {
                        place_id: {
                            required: 'กรุณาเลือกสถานที่'
                        },
                        duration_min: {
                            required: 'กรุณาระบุเวลา',
                            digits: 'ต้องเป็นตัวเลขจำนวนเต็ม'
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

                $('#btnAddRoutePlace').on('click', () => {
                    $('#route_place_id').val('');
                    $form[0].reset();
                    $('#routePlaceModalTitle').text('เพิ่มจุด');
                    loadAllPlaces();
                    modal.show();
                });
                
                $('#routePlaceTable').on('click', '.btn-edit', function() {
                    const id = $(this).data('id'); // fetch row data
                    const row = table.row('#rp_' + id).data();
                    $('#route_place_id').val(id);
                    loadAllPlaces(row.place_id);
                    $('#rp_duration_min').val(row.duration_min);
                    $('#routePlaceModalTitle').text('แก้ไขจุด');
                    modal.show();
                });

                $('#btnSaveRoutePlace').on('click', function() {
                    if (!$form.valid()) return;

                    const id = $('#route_place_id').val();


                    const btn = this;
                    startBtnLoading(btn, 'กำลังบันทึก...');


                    const payload = {
                        place_id: $('#rp_place_id').val(),
                        duration_min: $('#rp_duration_min').val()
                    };


                    $.ajax({
                        url: id ? `{{ url('backoffice/routes') }}/${routeId}/route-places/${id}` :
                            `{{ url('backoffice/routes') }}/${routeId}/route-places`,
                        type: id ? 'PUT' : 'POST',
                        data: payload,
                        success: () => {
                            modal.hide();
                            table.ajax.reload(null, false);
                            showSwalSuccess('บันทึกสำเร็จ');
                        },
                        error: (xhr) => {
                            showSwalError(xhr.responseJSON?.message || 'Error');
                        },
                        complete: () => endBtnLoading(btn)
                    });
                });

                $('#routePlaceTable').on('click', '.btn-del', function() {
                    const id = $(this).data('id');
                    confirmSwal('ยืนยันการลบจุดนี้?').then(res => {
                        if (!res.isConfirmed) return;
                        $.ajax({
                            url: `{{ url('backoffice/routes') }}/${routeId}/route-places/${id}`,
                            type: 'DELETE',
                            success: () => {
                                table.ajax.reload(null, false);
                                showSwalSuccess('ลบสำเร็จ');
                            },
                            error: () => showSwalError('Error')
                        });
                    });
                });
            });
        </script>
    @endpush
@endsection
