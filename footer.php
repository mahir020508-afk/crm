</main>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
function openModal(modalId) {
    const m = document.getElementById(modalId);
    if(m) {
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
}
function closeModal(modalId) {
    const m = document.getElementById(modalId);
    if(m) {
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
}

$(document).ready(function() {
    $('#dataTable').DataTable({
        responsive: true,
        dom: '<"flex flex-col md:flex-row justify-between items-center mb-4 gap-4"Bf>rt<"flex flex-col md:flex-row justify-between items-center mt-4 gap-4"ip>',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fa-solid fa-file-excel text-emerald-600 mr-1.5"></i> Excel',
                className: 'hover:border-slate-400'
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fa-solid fa-file-pdf text-rose-600 mr-1.5"></i> PDF',
                className: 'hover:border-slate-400'
            },
            {
                extend: 'print',
                text: '<i class="fa-solid fa-print text-brand-electric mr-1.5"></i> Yazdır',
                className: 'hover:border-slate-400'
            }
        ],
        language: {
            search: "",
            searchPlaceholder: "Tabloda canlı ara...",
            info: "_TOTAL_ kayıttan _START_ - _END_ gösteriliyor",
            infoEmpty: "Kayıt bulunamadı",
            zeroRecords: "Eşleşen kayıt yok",
            paginate: { next: "Sonraki →", previous: "← Önceki" }
        }
    });
});
</script>
</body>
</html>