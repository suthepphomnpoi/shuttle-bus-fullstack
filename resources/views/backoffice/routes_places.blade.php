@extends('layouts.backoffice')

@section('title', 'เส้นทาง & จุดรับ–ส่ง')

@section('content')
<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">เส้นทาง</h5>
          <button class="btn btn-primary" id="btnAddRoute"><i class="ti ti-plus"></i> เพิ่มเส้นทาง</button>
        </div>
        <div class="table-responsive">
          <table id="routeTable" class="table table-bordered w-100 text-nowrap">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>ชื่อเส้นทาง</th>
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
          <h5 class="mb-0">สถานที่</h5>
          <button class="btn btn-primary" id="btnAddPlace"><i class="ti ti-plus"></i> เพิ่มสถานที่</button>
        </div>
        <div class="table-responsive">
          <table id="placeTable" class="table table-bordered w-100 text-nowrap">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>ชื่อ</th>
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

<!-- Route Modal -->
<div class="modal fade" id="routeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="routeModalTitle">เพิ่มเส้นทาง</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="routeForm">
          <input type="hidden" id="route_id">
          <div class="mb-3">
            <label class="form-label">ชื่อเส้นทาง</label>
            <input type="text" class="form-control" id="route_name" name="name" required maxlength="100">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
        <button class="btn btn-primary" id="btnSaveRoute">บันทึก</button>
      </div>
    </div>
  </div>
</div>

<!-- Place Modal -->
<div class="modal fade" id="placeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
  <h5 class="modal-title" id="placeModalTitle">เพิ่มสถานที่</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="placeForm">
          <input type="hidden" id="place_id">
          <div class="mb-3">
            <label class="form-label">ชื่อสถานที่</label>
            <input type="text" class="form-control" id="place_name" name="name" required maxlength="100">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
        <button class="btn btn-primary" id="btnSavePlace">บันทึก</button>
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
  // Routes table
  const routeTable = $('#routeTable').DataTable({
    serverSide:true, processing:true,
    ajax:{ url:'{{ url('backoffice/routes/data') }}', type:'GET' },
    columns:[
      { data:'route_id', width:60 },
      { data:'name' },
      { data:'created_at', width:160 },
      { data:null, orderable:false, searchable:false, className:'text-nowrap', width:160,
        render: (row)=>`
          <button class="btn btn-sm btn-info me-1 btn-edit-route" data-id="${row.route_id}"><i class="ti ti-edit"></i></button>
          <button class="btn btn-sm btn-danger me-1 btn-del-route" data-id="${row.route_id}"><i class="ti ti-trash"></i></button>
          <a class="btn btn-sm btn-secondary" href="{{ url('backoffice/routes') }}/${row.route_id}/route-places-page"><i class="ti ti-map-pin"></i></a>
        ` }
    ],
    order:[[0,'desc']]
  });

  const routeModal = new bootstrap.Modal(document.getElementById('routeModal'));
  const $routeForm = $('#routeForm');
  $routeForm.validate({
    onkeyup:(el)=>$(el).valid(), onfocusout:(el)=>$(el).valid(), errorElement:'div',
    rules:{ name:{ required:true, maxlength:100, normalizer:v=>$.trim(v) } },
    messages:{ name:{ required:'กรุณากรอกชื่อเส้นทาง', maxlength:'ไม่เกิน 100 ตัวอักษร' } },
    errorClass:'is-invalid', errorPlacement:(e,el)=>{ e.addClass('invalid-feedback'); e.insertAfter(el); },
    highlight:(el)=>$(el).addClass('is-invalid').removeClass('is-valid'),
    unhighlight:(el)=>{ $(el).removeClass('is-invalid'); $(el).next('.invalid-feedback').remove(); }
  });

  $('#btnAddRoute').on('click',()=>{ $('#route_id').val(''); $routeForm[0].reset(); $('#routeModalTitle').text('เพิ่มเส้นทาง'); routeModal.show(); });
  $('#routeTable').on('click','.btn-edit-route',function(){ const id=$(this).data('id'); $.get(`{{ url('backoffice/routes') }}/${id}`, res=>{ $('#route_id').val(res.route_id); $('#route_name').val(res.name); $('#routeModalTitle').text('แก้ไขเส้นทาง'); routeModal.show(); }); });
  $('#btnSaveRoute').on('click',function(){ if(!$routeForm.valid()) return; const id=$('#route_id').val(); const btn=this; startBtnLoading(btn,'กำลังบันทึก...'); $.ajax({ url: id? `{{ url('backoffice/routes') }}/${id}`: `{{ url('backoffice/routes') }}`, type: id? 'PUT':'POST', data:{ name: $('#route_name').val() }, success:()=>{ routeModal.hide(); routeTable.ajax.reload(null,false); showSwalSuccess('บันทึกสำเร็จ'); }, error:(xhr)=>{ showSwalError(xhr.responseJSON?.message||'Error'); }, complete:()=>endBtnLoading(btn) }); });
  $('#routeTable').on('click','.btn-del-route',function(){ const id=$(this).data('id'); confirmSwal('ยืนยันการลบเส้นทาง?').then(res=>{ if(!res.isConfirmed) return; $.ajax({ url:`{{ url('backoffice/routes') }}/${id}`, type:'DELETE', success:()=>{ routeTable.ajax.reload(null,false); showSwalSuccess('ลบสำเร็จ'); }, error:()=>showSwalError('Error') }); }); });

  // Places table
  const placeTable = $('#placeTable').DataTable({
    serverSide:true, processing:true,
    ajax:{ url:'{{ url('backoffice/places/data') }}', type:'GET' },
    columns:[
      { data:'place_id', width:60 },
      { data:'name' },
      { data:'created_at', width:160 },
      { data:null, orderable:false, searchable:false, className:'text-nowrap', width:120,
        render:(row)=>`
          <button class="btn btn-sm btn-info me-1 btn-edit-place" data-id="${row.place_id}"><i class="ti ti-edit"></i></button>
          <button class="btn btn-sm btn-danger btn-del-place" data-id="${row.place_id}"><i class="ti ti-trash"></i></button>` }
    ], order:[[0,'desc']]
  });

  const placeModal = new bootstrap.Modal(document.getElementById('placeModal'));
  const $placeForm = $('#placeForm');
  $placeForm.validate({ onkeyup:(el)=>$(el).valid(), onfocusout:(el)=>$(el).valid(), errorElement:'div',
    rules:{ name:{ required:true, maxlength:100, normalizer:v=>$.trim(v) } },
  messages:{ name:{ required:'กรุณากรอกชื่อสถานที่', maxlength:'ไม่เกิน 100 ตัวอักษร' } },
    errorClass:'is-invalid', errorPlacement:(e,el)=>{ e.addClass('invalid-feedback'); e.insertAfter(el); },
    highlight:(el)=>$(el).addClass('is-invalid').removeClass('is-valid'),
    unhighlight:(el)=>{ $(el).removeClass('is-invalid'); $(el).next('.invalid-feedback').remove(); }
  });

  $('#btnAddPlace').on('click',()=>{ $('#place_id').val(''); $placeForm[0].reset(); $('#placeModalTitle').text('เพิ่มสถานที่'); placeModal.show(); });
  $('#placeTable').on('click','.btn-edit-place',function(){ const id=$(this).data('id'); $.get(`{{ url('backoffice/places') }}/${id}`, res=>{ $('#place_id').val(res.place_id); $('#place_name').val(res.name); $('#placeModalTitle').text('แก้ไขสถานที่'); placeModal.show(); }); });
  $('#btnSavePlace').on('click',function(){ if(!$placeForm.valid()) return; const id=$('#place_id').val(); const btn=this; startBtnLoading(btn,'กำลังบันทึก...'); $.ajax({ url: id? `{{ url('backoffice/places') }}/${id}`: `{{ url('backoffice/places') }}`, type: id? 'PUT':'POST', data:{ name: $('#place_name').val() }, success:()=>{ placeModal.hide(); placeTable.ajax.reload(null,false); showSwalSuccess('บันทึกสำเร็จ'); }, error:(xhr)=>{ showSwalError(xhr.responseJSON?.message||'Error'); }, complete:()=>endBtnLoading(btn) }); });
  $('#placeTable').on('click','.btn-del-place',function(){ const id=$(this).data('id'); confirmSwal('ยืนยันการลบสถานที่?').then(res=>{ if(!res.isConfirmed) return; $.ajax({ url:`{{ url('backoffice/places') }}/${id}`, type:'DELETE', success:()=>{ placeTable.ajax.reload(null,false); showSwalSuccess('ลบสำเร็จ'); }, error:()=>showSwalError('Error') }); }); });
});
</script>
@endpush
@endsection
