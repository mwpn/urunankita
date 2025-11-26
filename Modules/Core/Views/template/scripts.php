<script src="<?= base_url('admin-template/js/jquery.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/popper.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/moment.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/bootstrap.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/simplebar.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/daterangepicker.js') ?>"></script>
<script src="<?= base_url('admin-template/js/jquery.stickOnScroll.js') ?>"></script>
<script src="<?= base_url('admin-template/js/tinycolor-min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/config.js') ?>"></script>
<script src="<?= base_url('admin-template/js/d3.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/topojson.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/datamaps.all.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/datamaps-zoomto.js') ?>"></script>
<script src="<?= base_url('admin-template/js/datamaps.custom.js') ?>"></script>
<script src="<?= base_url('admin-template/js/Chart.min.js') ?>"></script>
<script>
  /* defind global options */
  Chart.defaults.global.defaultFontFamily = base.defaultFontFamily;
  Chart.defaults.global.defaultFontColor = colors.mutedColor;
</script>
<script src="<?= base_url('admin-template/js/gauge.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/jquery.sparkline.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/apexcharts.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/apexcharts.custom.js') ?>"></script>
<script src="<?= base_url('admin-template/js/jquery.mask.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/select2.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/jquery.steps.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/jquery.validate.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/jquery.timepicker.js') ?>"></script>
<script src="<?= base_url('admin-template/js/dropzone.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/uppy.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/quill.min.js') ?>"></script>
<script>
  $(document).ready(function() {
    $('.select2').select2({
      theme: 'bootstrap4',
    });
    $('.select2-multi').select2({
      multiple: true,
      theme: 'bootstrap4',
    });
    $('.drgpicker').daterangepicker({
      singleDatePicker: true,
      timePicker: false,
      showDropdowns: true,
      locale: {
        format: 'MM/DD/YYYY'
      }
    });
    $('.time-input').timepicker({
      'scrollDefault': 'now',
      'zindex': '9999' /* fix modal open */
    });
    /** date range picker */
    if ($('.datetimes').length) {
      $('.datetimes').daterangepicker({
        timePicker: true,
        startDate: moment().startOf('hour'),
        endDate: moment().startOf('hour').add(32, 'hour'),
        locale: {
          format: 'M/DD hh:mm A'
        }
      });
    }
    var start = moment().subtract(29, 'days');
    var end = moment();

    function cb(start, end) {
      $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }
    if ($('#reportrange').length) {
      $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
          'Today': [moment(), moment()],
          'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          'Last 7 Days': [moment().subtract(6, 'days'), moment()],
          'Last 30 Days': [moment().subtract(29, 'days'), moment()],
          'This Month': [moment().startOf('month'), moment().endOf('month')],
          'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
      }, cb);
      cb(start, end);
    }
    $('.input-placeholder').mask("00/00/0000", {
      placeholder: "__/__/____"
    });
    $('.input-zip').mask('00000-000', {
      placeholder: "____-___"
    });
    $('.input-money').mask("#.##0", {
      reverse: true
    });
    $('.input-phoneus').mask('(000) 000-0000');
    $('.input-mixed').mask('AAA 000-S0S');
    $('.input-ip').mask('0ZZ.0ZZ.0ZZ.0ZZ', {
      translation: {
        'Z': {
          pattern: /[0-9]/,
          optional: true
        }
      },
      placeholder: "___.___.___.___"
    });
    // editor
    var editor = document.getElementById('editor');
    if (editor) {
      var toolbarOptions = [
        [{
          'font': []
        }],
        [{
          'header': [1, 2, 3, 4, 5, 6, false]
        }],
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        [{
          'header': 1
        }, {
          'header': 2
        }],
        [{
          'list': 'ordered'
        }, {
          'list': 'bullet'
        }],
        [{
          'script': 'sub'
        }, {
          'script': 'super'
        }],
        [{
          'indent': '-1'
        }, {
          'indent': '+1'
        }], // outdent/indent
        [{
          'direction': 'rtl'
        }], // text direction
        [{
          'color': []
        }, {
          'background': []
        }], // dropdown with defaults from theme
        [{
          'align': []
        }],
        ['clean'] // remove formatting button
      ];
      var quill = new Quill(editor, {
        modules: {
          toolbar: toolbarOptions
        },
        theme: 'snow'
      });
    }
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function() {
      'use strict';
      window.addEventListener('load', function() {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.getElementsByClassName('needs-validation');
        // Loop over them and prevent submission
        var validation = Array.prototype.filter.call(forms, function(form) {
          form.addEventListener('submit', function(event) {
            if (form.checkValidity() === false) {
              event.preventDefault();
              event.stopPropagation();
            }
            form.classList.add('was-validated');
          }, false);
        });
      }, false);
    })();
  });
</script>
<script>
  var uptarg = document.getElementById('drag-drop-area');
  if (uptarg) {
    var uppy = Uppy.Core().use(Uppy.Dashboard, {
      inline: true,
      target: uptarg,
      proudlyDisplayPoweredByUppy: false,
      theme: 'dark',
      width: 770,
      height: 210,
      plugins: ['Webcam']
    }).use(Uppy.Tus, {
      endpoint: 'https://master.tus.io/files/'
    });
    uppy.on('complete', (result) => {
      console.log('Upload complete! We've uploaded these files:', result.successful)
    });
  }
</script>
<script src="<?= base_url('admin-template/js/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?= base_url('admin-template/js/apps.js') ?>"></script>

<!-- Sidebar Role Filter -->
<script>
    // Set user role dari PHP
    <?php
    // Get userRole from variable (from controller), session, or default
    // Priority: 1. Variable from controller, 2. Session auth_user role, 3. Default based on URL
    if (!isset($userRole) || empty($userRole)) {
        $authUser = session()->get('auth_user') ?? [];
        $currentUri = uri_string();
        $isAdminPage = (strpos($currentUri, '/admin/') === 0);
        $userRole = $authUser['role'] ?? ($isAdminPage ? 'admin' : 'penggalang_dana');
    }
    
    // Normalize role values
    if (in_array($userRole, ['superadmin', 'super_admin', 'admin'])) {
        $userRole = 'admin';
    } elseif (empty($userRole) || !in_array($userRole, ['admin', 'penggalang_dana'])) {
        $currentUri = uri_string();
        $isAdminPage = (strpos($currentUri, '/admin/') === 0);
        $userRole = $isAdminPage ? 'admin' : 'penggalang_dana';
    }
    ?>
    window.userRole = '<?= $userRole ?>';
    localStorage.setItem('userRole', window.userRole);
    console.log('Sidebar userRole set to:', window.userRole);
</script>
<script src="<?= base_url('admin-template/js/sidebar-role.js') ?>"></script>

<!-- Initialize components -->
<script>
    // Initialize DataTables for .datatables class - hanya jika belum diinisialisasi
    // Note: Inisialisasi spesifik per table dilakukan di section scripts masing-masing view
    // Script ini hanya untuk table yang tidak punya inisialisasi khusus
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('.datatables').each(function() {
                // Skip jika table punya ID yang akan diinisialisasi di view
                if ($(this).attr('id') && $(this).attr('id').length > 0) {
                    return; // Skip, akan diinisialisasi di view
                }
                
                // Hanya inisialisasi jika belum diinisialisasi
                if (!$.fn.dataTable.isDataTable(this)) {
                    $(this).DataTable({
                        autoWidth: true,
                        "lengthMenu": [
                            [10, 25, 50, -1],
                            [10, 25, 50, "All"]
                        ],
                        "language": {
                            "search": "Cari:",
                            "lengthMenu": "Tampilkan _MENU_ data per halaman",
                            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                            "infoFiltered": "(difilter dari _MAX_ total data)",
                            "paginate": {
                                "first": "Pertama",
                                "last": "Terakhir",
                                "next": "Selanjutnya",
                                "previous": "Sebelumnya"
                            }
                        }
                    });
                }
            });
        }
    });
</script>

