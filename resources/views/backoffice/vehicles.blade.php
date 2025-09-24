@extends('layouts.backoffice')

@section('title', 'รถ & ประเภทรถ')

@section('content')
<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">รถ</h5>
          <button class="btn btn-primary" id="btnAddVehicle"><i class="ti ti-plus"></i> เพิ่มรถ</button>
        </div>
        <div class="table-responsive">
          <table id="vehicleTable" class="table table-bordered w-100 text-nowrap">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>ทะเบียน</th>
                <th>ประเภทรถ</th>
                <th>ที่นั่ง</th>
                <th>สถานะ</th>
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
          <h5 class="mb-0">ประเภทรถ</h5>
          <button class="btn btn-primary" id="btnAddType"><i class="ti ti-plus"></i> เพิ่มประเภทรถ</button>
        </div>
        <div class="table-responsive">
          <table id="typeTable" class="table table-bordered w-100 text-nowrap">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>ชื่อ</th>
                <th>การจัดการ</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Vehicle Modal -->
<div class="modal fade" id="vehicleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="vehicleModalTitle">เพิ่มรถ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="vehicleForm">
          <input type="hidden" id="vehicle_id">
          <div class="mb-3">
            <label class="form-label">ทะเบียนรถ</label>
            <input type="text" class="form-control" id="license_plate" name="license_plate" required maxlength="50">
          </div>
          <div class="mb-3">
            <label class="form-label">ประเภทรถ</label>
            <select class="form-select" id="vehicle_type_id" name="vehicle_type_id" required></select>
          </div>
          <div class="mb-3">
            <label class="form-label">จำนวนที่นั่ง</label>
            <input type="number" min="1" class="form-control" id="v_capacity" name="capacity" required>
          </div>
          <div class="mb-3">
            <label class="form-label">คำอธิบาย</label>
            <input type="text" class="form-control" id="description" name="description" maxlength="255">
          </div>
          <div class="mb-3">
            <label class="form-label">สถานะ</label>
            <select class="form-select" id="status" name="status" required>
              <option value="active">พร้อมใช้งาน</option>
              <option value="maintenance">ซ่อมบำรุง</option>
              <option value="retired">ปลดระวาง</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
        <button class="btn btn-primary" id="btnSaveVehicle">บันทึก</button>
      </div>
    </div>
  </div>
</div>

<!-- Vehicle Type Modal -->
<div class="modal fade" id="typeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="typeModalTitle">เพิ่มประเภทรถ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="typeForm">
          <input type="hidden" id="vehicle_type_id_hidden">
          <div class="mb-3">
            <label class="form-label">ชื่อประเภทรถ</label>
            <input type="text" class="form-control" id="type_name" name="name" required maxlength="100">
          </div>
          <!-- ลบจำนวนที่นั่งออกจากประเภทรถ (ย้ายไปอยู่ที่รถ) -->
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
        <button class="btn btn-primary" id="btnSaveType">บันทึก</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script src="{{ asset('js/jquery.validate.min.js') }}"></script>
<script src="{{ asset('js/additional-methods.min.js') }}"></script>
<script>
function escapeHtml(text){ return $('<div>').text(text ?? '').html(); }
$(function(){
  // Vehicles table
  const vTable = $('#vehicleTable').DataTable({
    serverSide:true, processing:true,
    ajax:{ url:'{{ url('backoffice/vehicles/data') }}', type:'GET' },
    columns:[
      { data:'vehicle_id', width:60 },
  { data:'license_plate' },
  { data:'type_name' },
  { data:'capacity', width:100 },
  { data:'status', width:140, render:(v)=>{
        const map={active:'พร้อมใช้งาน',maintenance:'ซ่อมบำรุง',retired:'ปลดระวาง'}; return map[v]||v;
      } },
      { data:null, orderable:false, searchable:false, className:'text-nowrap', width:140,
        render:(row)=>`
          <button class="btn btn-sm btn-info me-1 btn-edit-vehicle" data-id="${row.vehicle_id}"><i class="ti ti-edit"></i></button>
          <button class="btn btn-sm btn-danger btn-del-vehicle" data-id="${row.vehicle_id}"><i class="ti ti-trash"></i></button>` }
    ], order:[[0,'desc']]
  });

  // Load types
  function loadTypes(selected){
    $.get(`{{ url('backoffice/vehicle-types/list') }}`, resp=>{
      const opts = (resp||[]).map(r=>`<option value="${r.vehicle_type_id}">${escapeHtml(r.name)}</option>`).join('');
      $('#vehicle_type_id').html(`<option value=\"\">-- เลือก --</option>`+opts);
      if(selected) $('#vehicle_type_id').val(selected);
    });
  }

  const vModal = new bootstrap.Modal(document.getElementById('vehicleModal'));
  const $vForm = $('#vehicleForm');
  $vForm.validate({ onkeyup:(el)=>$(el).valid(), onfocusout:(el)=>$(el).valid(), errorElement:'div',
    rules:{ license_plate:{ required:true, maxlength:50 }, vehicle_type_id:{ required:true }, capacity:{ required:true, digits:true, min:1 }, description:{ maxlength:255 }, status:{ required:true } },
    messages:{ license_plate:{ required:'กรุณากรอกทะเบียนรถ', maxlength:'ไม่เกิน 50 ตัวอักษร' }, vehicle_type_id:{ required:'กรุณาเลือกประเภทรถ' }, capacity:{ required:'กรุณาระบุจำนวนที่นั่ง', digits:'ต้องเป็นตัวเลขจำนวนเต็ม', min:'ต้องมากกว่า 0' }, description:{ maxlength:'ไม่เกิน 255 ตัวอักษร' }, status:{ required:'กรุณาเลือกสถานะ' } },
    errorClass:'is-invalid', errorPlacement:(e,el)=>{ e.addClass('invalid-feedback'); e.insertAfter(el); },
    highlight:(el)=>$(el).addClass('is-invalid').removeClass('is-valid'),
    unhighlight:(el)=>{ $(el).removeClass('is-invalid'); $(el).next('.invalid-feedback').remove(); }
  });

  $('#btnAddVehicle').on('click',()=>{ $('#vehicle_id').val(''); $vForm[0].reset(); $('#vehicleModalTitle').text('เพิ่มรถ'); loadTypes(); vModal.show(); });
  $('#vehicleTable').on('click','.btn-edit-vehicle',function(){ const id=$(this).data('id'); $.get(`{{ url('backoffice/vehicles') }}/${id}`, res=>{ $('#vehicle_id').val(res.vehicle_id); $('#license_plate').val(res.license_plate); loadTypes(res.vehicle_type_id); $('#v_capacity').val(res.capacity||''); $('#description').val(res.description||''); $('#status').val(res.status); $('#vehicleModalTitle').text('แก้ไขรถ'); vModal.show(); }); });
  $('#btnSaveVehicle').on('click',function(){ if(!$vForm.valid()) return; const id=$('#vehicle_id').val(); const btn=this; startBtnLoading(btn,'กำลังบันทึก...');
  const payload = { license_plate: $('#license_plate').val(), vehicle_type_id: $('#vehicle_type_id').val(), capacity: $('#v_capacity').val(), description: $('#description').val(), status: $('#status').val() };
    $.ajax({ url: id? `{{ url('backoffice/vehicles') }}/${id}`: `{{ url('backoffice/vehicles') }}`, type: id? 'PUT':'POST', data: payload,
      success:()=>{ vModal.hide(); vTable.ajax.reload(null,false); showSwalSuccess('บันทึกสำเร็จ'); }, error:(xhr)=>{ showSwalError(xhr.responseJSON?.message||'Error'); }, complete:()=>endBtnLoading(btn) }); });
  $('#vehicleTable').on('click','.btn-del-vehicle',function(){ const id=$(this).data('id'); confirmSwal('ยืนยันการลบรถ?').then(res=>{ if(!res.isConfirmed) return; $.ajax({ url:`{{ url('backoffice/vehicles') }}/${id}`, type:'DELETE', success:()=>{ vTable.ajax.reload(null,false); showSwalSuccess('ลบสำเร็จ'); }, error:()=>showSwalError('Error') }); }); });

  // Types table
  const tTable = $('#typeTable').DataTable({
    serverSide:true, processing:true,
    ajax:{ url:'{{ url('backoffice/vehicle-types/data') }}', type:'GET' },
    columns:[
  { data:'vehicle_type_id', width:60 },
  { data:'name' },
      { data:null, orderable:false, searchable:false, className:'text-nowrap', width:120,
        render:(row)=>`
          <button class=\"btn btn-sm btn-info me-1 btn-edit-type\" data-id=\"${row.vehicle_type_id}\"><i class=\"ti ti-edit\"></i></button>
          <button class=\"btn btn-sm btn-danger btn-del-type\" data-id=\"${row.vehicle_type_id}\"><i class=\"ti ti-trash\"></i></button>` }
    ], order:[[0,'desc']]
  });

  const tModal = new bootstrap.Modal(document.getElementById('typeModal'));
  const $tForm = $('#typeForm');
  $tForm.validate({ onkeyup:(el)=>$(el).valid(), onfocusout:(el)=>$(el).valid(), errorElement:'div',
    rules:{ name:{ required:true, maxlength:100, normalizer:v=>$.trim(v) } },
    messages:{ name:{ required:'กรุณากรอกชื่อประเภทรถ', maxlength:'ไม่เกิน 100 ตัวอักษร' } },
    errorClass:'is-invalid', errorPlacement:(e,el)=>{ e.addClass('invalid-feedback'); e.insertAfter(el); },
    highlight:(el)=>$(el).addClass('is-invalid').removeClass('is-valid'),
    unhighlight:(el)=>{ $(el).removeClass('is-invalid'); $(el).next('.invalid-feedback').remove(); }
  });

  $('#btnAddType').on('click',()=>{ $('#vehicle_type_id_hidden').val(''); $tForm[0].reset(); $('#typeModalTitle').text('เพิ่มประเภทรถ'); tModal.show(); });
  $('#typeTable').on('click','.btn-edit-type',function(){ const id=$(this).data('id'); $.get(`{{ url('backoffice/vehicle-types') }}/${id}`, res=>{ $('#vehicle_type_id_hidden').val(res.vehicle_type_id); $('#type_name').val(res.name); $('#typeModalTitle').text('แก้ไขประเภทรถ'); tModal.show(); }); });
  $('#btnSaveType').on('click',function(){ if(!$tForm.valid()) return; const id=$('#vehicle_type_id_hidden').val(); const btn=this; startBtnLoading(btn,'กำลังบันทึก...');
  const payload = { name: $('#type_name').val() };
    $.ajax({ url: id? `{{ url('backoffice/vehicle-types') }}/${id}`: `{{ url('backoffice/vehicle-types') }}`, type: id? 'PUT':'POST', data: payload,
      success:()=>{ tModal.hide(); tTable.ajax.reload(null,false); showSwalSuccess('บันทึกสำเร็จ'); }, error:(xhr)=>{ showSwalError(xhr.responseJSON?.message||'Error'); }, complete:()=>endBtnLoading(btn) }); });
  $('#typeTable').on('click','.btn-del-type',function(){ const id=$(this).data('id'); confirmSwal('ยืนยันการลบประเภทรถ?').then(res=>{ if(!res.isConfirmed) return; $.ajax({ url:`{{ url('backoffice/vehicle-types') }}/${id}`, type:'DELETE', success:()=>{ tTable.ajax.reload(null,false); showSwalSuccess('ลบสำเร็จ'); }, error:()=>showSwalError('Error') }); }); });
});
</script>
@endpush
@endsection