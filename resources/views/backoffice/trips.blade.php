@extends('layouts.backoffice')

@section('title', 'จัดการรอบรถ (Trips)')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">รอบรถ</h5>
                <button class="btn btn-primary" id="btnAddTrip"><i class="ti ti-plus"></i> เพิ่มรอบ</button>
            </div>
            <div class="table-responsive">
                <table id="tripTable" class="table table-bordered w-100 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>รอบ</th>
                            <th>วันที่</th>
                            <th>เวลา</th>
                            <th>เส้นทาง</th>
                            <th>รถ</th>
                            <th>คนขับ</th>
                            <th>จอง/ที่นั่ง</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="tripModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tripModalTitle">เพิ่มรอบ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="tripForm">
                        <input type="hidden" id="trip_id">
                        <div class="mb-3">
                            <label class="form-label">รอบ</label>
                            <input type="number" min="1" class="form-control" id="round_no" name="round_no" placeholder="ปล่อยว่างให้ระบบกำหนดอัตโนมัติ">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">วันที่</label>
                            <input type="date" class="form-control" id="service_date" name="service_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">เวลา (HH:MM)</label>
                            <input type="text" class="form-control" id="depart_time" name="depart_time" placeholder="08:30" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">เส้นทาง</label>
                            <select class="form-select" id="route_id" name="route_id" required></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">รถ</label>
                            <select class="form-select" id="vehicle_id" name="vehicle_id" required></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">คนขับ</label>
                            <select class="form-select" id="driver_id" name="driver_id" required></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">จำนวนที่นั่ง</label>
                            <input type="number" min="1" class="form-control" id="capacity" name="capacity" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">จองแล้ว</label>
                            <input type="number" min="0" class="form-control" id="reserved_seats" name="reserved_seats" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">สถานะ</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="scheduled">scheduled</option>
                                <option value="ongoing">ongoing</option>
                                <option value="completed">completed</option>
                                <option value="cancelled">cancelled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">หมายเหตุ</label>
                            <input type="text" class="form-control" id="notes" name="notes" maxlength="500">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button class="btn btn-primary" id="btnSaveTrip">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function escapeHtml(text){ return $('<div>').text(text ?? '').html(); }
            $(function(){
                const tTable = $('#tripTable').DataTable({
                    serverSide: true,
                    processing: true,
                    ajax: { url: '{{ url('backoffice/trips/data') }}', type: 'GET' },
                    columns: [
                        { data: 'trip_id', width: 60 },
                        { data: 'round_no', width: 60 },
                        { data: 'service_date' },
                        { data: 'depart_time' },
                        { data: 'route_name' },
                        { data: 'vehicle_plate' },
                        { data: 'driver_name' },
                        { data: null, render: (d,t,row)=> `${row.reserved_seats}/${row.capacity}` },
                        { data: 'status' },
                        { data: null, orderable:false, searchable:false, width:110, render: (d,t,row)=> `
                            <button class="btn btn-sm btn-info me-1 btn-edit-trip" data-id="${row.trip_id}"><i class="ti ti-edit"></i></button>
                            <button class="btn btn-sm btn-danger btn-del-trip" data-id="${row.trip_id}"><i class="ti ti-trash"></i></button>
                        ` }
                    ],
                    order: [[0,'desc']]
                });

                const tripModal = new bootstrap.Modal(document.getElementById('tripModal'));
                function loadInit(selected){
                    $.get(`{{ url('backoffice/trips/init') }}`, res => {
                        const rOpts = (res.routes||[]).map(r=>`<option value="${r.route_id}">${escapeHtml(r.name)}</option>`).join('');
                        const vOpts = (res.vehicles||[]).map(v=>`<option value="${v.vehicle_id}" data-capacity="${v.capacity ?? ''}">${escapeHtml(v.license_plate)}</option>`).join('');
                        const dOpts = (res.drivers||[]).map(d=>`<option value="${d.employee_id}">${escapeHtml(d.name)}</option>`).join('');
                        $('#route_id').html(`<option value="">-- เลือก --</option>`+rOpts);
                        $('#vehicle_id').html(`<option value="">-- เลือก --</option>`+vOpts);
                        $('#driver_id').html(`<option value="">-- เลือก --</option>`+dOpts);
                        if (selected){
                            $('#route_id').val(selected.route_id);
                            $('#vehicle_id').val(selected.vehicle_id);
                            $('#driver_id').val(selected.driver_id);
                            // Autofill capacity from selected vehicle when editing
                            const cap = $('#vehicle_id option:selected').data('capacity');
                            if (cap) $('#capacity').val(cap);
                        }
                    });
                }

                // When vehicle changes, set capacity from vehicle default
                $(document).on('change', '#vehicle_id', function(){
                    const cap = $('#vehicle_id option:selected').data('capacity');
                    if (cap) $('#capacity').val(cap);
                });

                $('#btnAddTrip').on('click', ()=>{
                    $('#trip_id').val('');
                    $('#tripForm')[0].reset();
                    $('#tripModalTitle').text('เพิ่มรอบ');
                    loadInit();
                    tripModal.show();
                });

                $('#tripTable').on('click','.btn-edit-trip', function(){
                    const id = $(this).data('id');
                    $.get(`{{ url('backoffice/trips') }}/${id}`, res => {
                        $('#trip_id').val(res.trip_id);
                        $('#service_date').val(res.service_date?.substring(0,10));
                        $('#depart_time').val(res.depart_time);
                        $('#round_no').val(res.round_no ?? '');
                        $('#capacity').val(res.capacity);
                        $('#reserved_seats').val(res.reserved_seats ?? 0);
                        $('#status').val(res.status);
                        $('#notes').val(res.notes ?? '');
                        $('#tripModalTitle').text('แก้ไขรอบ');
                        loadInit({route_id:res.route_id, vehicle_id:res.vehicle_id, driver_id:res.driver_id});
                        tripModal.show();
                    });
                });

                $('#btnSaveTrip').on('click', function(){
                    const id = $('#trip_id').val();
                    const btn = this; startBtnLoading(btn,'กำลังบันทึก...');
                    const payload = {
                        service_date: $('#service_date').val(),
                        depart_time: $('#depart_time').val(),
                        round_no: $('#round_no').val() || undefined,
                        route_id: $('#route_id').val(),
                        vehicle_id: $('#vehicle_id').val(),
                        driver_id: $('#driver_id').val(),
                        capacity: $('#capacity').val(),
                        reserved_seats: $('#reserved_seats').val(),
                        status: $('#status').val(),
                        notes: $('#notes').val(),
                    };
                    $.ajax({
                        url: id ? `{{ url('backoffice/trips') }}/${id}` : `{{ url('backoffice/trips') }}`,
                        type: id ? 'PUT':'POST',
                        data: payload,
                        success: ()=>{ tripModal.hide(); $('#tripForm')[0].reset(); tTable.ajax.reload(null,false); showSwalSuccess('บันทึกสำเร็จ'); },
                        error: (xhr)=>{ showSwalError(xhr.responseJSON?.message || 'Error'); },
                        complete: ()=> endBtnLoading(btn)
                    });
                });

                $('#tripTable').on('click','.btn-del-trip', function(){
                    const id = $(this).data('id');
                    confirmSwal('ยืนยันการลบรอบ?').then(res=>{
                        if (!res.isConfirmed) return;
                        $.ajax({ url:`{{ url('backoffice/trips') }}/${id}`, type:'DELETE', success:()=>{ tTable.ajax.reload(null,false); showSwalSuccess('ลบสำเร็จ'); }, error:()=> showSwalError('Error') });
                    });
                });
            });
        </script>
    @endpush
@endsection
