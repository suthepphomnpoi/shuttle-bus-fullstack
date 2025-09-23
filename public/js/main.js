const LOADING_KEY = 'data-loading-original';

function makeSpinner() {
    // Bootstrap-like small spinner + text
    const span = document.createElement('span');
    span.className = 'spinner-border spinner-border-sm me-2';
    span.role = 'status';
    span.ariaHidden = 'true';
    return span;
}

function startBtnLoading(selector, text = 'กำลังดำเนินการ...') {
    const btn = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!btn || btn.hasAttribute('disabled')) return;
    // Store original HTML if not already stored
    if (!btn.hasAttribute(LOADING_KEY)) {
        btn.setAttribute(LOADING_KEY, btn.innerHTML);
    }
    btn.disabled = true;
    // Build loading HTML
    const wrapper = document.createElement('span');
    wrapper.appendChild(makeSpinner());
    wrapper.appendChild(document.createTextNode(text));
    btn.innerHTML = '';
    btn.appendChild(wrapper);
}

function endBtnLoading(selector) {
    const btn = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!btn) return;
    const original = btn.getAttribute(LOADING_KEY);
    if (original !== null) {
        btn.innerHTML = original;
        btn.removeAttribute(LOADING_KEY);
    }
    btn.disabled = false;
}

// Setup global AJAX defaults with CSRF from <meta name="csrf-token">
(function () {
    try {
        var meta = document.querySelector('meta[name="csrf-token"]');
        var token = meta && meta.getAttribute('content');
        if (token && typeof window.jQuery !== 'undefined') {
            window.jQuery.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
        }
    } catch (e) {
        // noop
    }
})();

// Global DataTables Thai localization
(function(){
    if (typeof window.jQuery !== 'undefined' && $.fn && $.fn.dataTable) {
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                emptyTable: 'ไม่พบข้อมูล',
                info: 'แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ',
                infoEmpty: 'แสดง 0 ถึง 0 จากทั้งหมด 0 รายการ',
                infoFiltered: '(กรองจากทั้งหมด _MAX_ รายการ)',
                lengthMenu: 'แสดง _MENU_ รายการ',
                loadingRecords: 'กำลังโหลด...',
                processing: 'กำลังประมวลผล...',
                search: 'ค้นหา:',
                zeroRecords: 'ไม่พบรายการที่ตรงกัน',
                paginate: {
                    first: '<<',
                    last: '>>',
                    next: '>',
                    previous: '<'
                },
                aria: {
                    sortAscending: ': เปิดเพื่อเรียงจากน้อยไปมาก',
                    sortDescending: ': เปิดเพื่อเรียงจากมากไปน้อย'
                }
            }
        });
    }
})();

// SweetAlert2 helpers
window.showSwalSuccess = function(message = 'สำเร็จ', title = 'Success!', options = {}){
    const cfg = Object.assign({
        icon: 'success',
        title: title,
        text: message,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    }, options);
    return Swal.fire(cfg);
}

window.showSwalError = function(message = 'เกิดข้อผิดพลาด', title = 'Oops!', options = {}){
    const cfg = Object.assign({
        icon: 'error',
        title: title,
        text: message,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    }, options);
    return Swal.fire(cfg);
}

window.confirmSwal = function(message = 'ยืนยันการทำรายการ?', title = 'คุณแน่ใจหรือไม่?', confirmText = 'ยืนยัน', cancelText = 'ยกเลิก', options = {}){
    const cfg = Object.assign({
        title: title,
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText
    }, options);
    return Swal.fire(cfg);
}
