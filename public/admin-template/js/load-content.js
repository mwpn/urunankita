/**
 * Load content dynamically without page reload
 * This allows using single layout (index.html) for all pages
 */
window.loadContent = function(contentPath) {
  const contentPlaceholder = document.getElementById('main-content-placeholder');
  if (!contentPlaceholder) {
    console.error('Content placeholder not found.');
    return;
  }

  // Show loading state
  contentPlaceholder.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>';

  // Load content
  fetch(contentPath)
    .then(response => {
      if (!response.ok) {
        throw new Error('Content not found: ' + contentPath);
      }
      return response.text();
    })
    .then(data => {
      contentPlaceholder.innerHTML = data;
      
      // Initialize any scripts or components after content is loaded
      initializeContent();
    })
    .catch(error => {
      console.error('Error loading content:', error);
      contentPlaceholder.innerHTML = '<div class="alert alert-danger m-4">Error loading content: ' + error.message + '</div>';
    });
};

/**
 * Initialize content-specific components
 */
window.initializeContent = function() {
  console.log('initializeContent called');
  // Wait a bit for DOM to be ready
  setTimeout(function() {
    console.log('initializeContent executing');
    // Initialize Select2 if exists
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
      jQuery('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
      });
    }
    
    // Initialize Quill Editor if exists
    if (typeof Quill !== 'undefined' && document.getElementById('deskripsi-editor')) {
      var toolbarOptions = [
        [{ 'header': [1, 2, 3, false] }],
        ['bold', 'italic', 'underline'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        ['link', 'image'],
        ['clean']
      ];
      var quill = new Quill('#deskripsi-editor', {
        modules: { toolbar: toolbarOptions },
        theme: 'snow'
      });
    }
    
    // Initialize Dropzone if exists
    if (typeof Dropzone !== 'undefined') {
      Dropzone.autoDiscover = false;
      
      // Single image dropzone
      if (document.getElementById('dropzone-single')) {
        var dropzoneSingle = new Dropzone("#dropzone-single", {
          url: "#",
          maxFiles: 1,
          maxFilesize: 5,
          acceptedFiles: "image/*",
          addRemoveLinks: true,
          dictDefaultMessage: "Klik atau drag gambar ke sini",
          dictRemoveFile: "Hapus",
          dictCancelUpload: "Batal"
        });
      }
      
      // Multiple images dropzone
      if (document.getElementById('dropzone-multiple')) {
        var dropzoneMultiple = new Dropzone("#dropzone-multiple", {
          url: "#",
          maxFiles: 5,
          maxFilesize: 5,
          acceptedFiles: "image/*",
          addRemoveLinks: true,
          dictDefaultMessage: "Klik atau drag gambar ke sini",
          dictRemoveFile: "Hapus",
          dictCancelUpload: "Batal"
        });
      }
    }
    
    // Initialize money mask if exists
    if (typeof jQuery !== 'undefined' && jQuery.fn.mask) {
      jQuery('.input-money').mask("#.##0,00", {
        reverse: true
      });
    }
    
    // Initialize ApexCharts if exists
    if (typeof ApexCharts !== 'undefined' && document.querySelector("#donasiChart")) {
      var options = {
        series: [{
          name: 'Donasi',
          data: [45000000, 52000000, 48000000, 61000000, 55000000, 67000000, 63000000]
        }],
        chart: {
          type: 'area',
          height: 300,
          toolbar: {
            show: false
          }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'smooth'
        },
        xaxis: {
          categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return "Rp " + val.toLocaleString('id-ID');
            }
          }
        }
      };
      var chart = new ApexCharts(document.querySelector("#donasiChart"), options);
      chart.render();
    }
    
    // Initialize date range picker if exists
    if (typeof jQuery !== 'undefined' && jQuery.fn.daterangepicker && document.getElementById('reportrange')) {
      jQuery('#reportrange').daterangepicker({
        opens: 'left',
        locale: {
          format: 'DD/MM/YYYY'
        }
      }, function(start, end, label) {
        console.log("Date range selected");
      });
    }
    
    // Form validation
    var forms = document.getElementsByClassName('needs-validation');
    Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        } else {
          event.preventDefault();
          alert('Form submitted! (This is a demo)');
        }
        form.classList.add('was-validated');
      }, false);
    });
    
    // Toggle target and deadline fields based on jenis
    setTimeout(function() {
      var jenisSelect = document.getElementById('jenis');
      var targetRow = document.getElementById('target-row');
      var deadlineRow = document.getElementById('deadline-row');
      var deadlineInput = document.getElementById('deadline');
      var deadlineRequired = document.getElementById('deadline-required');
      
      if (!jenisSelect) {
        console.warn('jenisSelect not found');
        return;
      }
      
      console.log('Setting up toggle for jenisSelect');
      
      // Function to toggle fields
      function toggleFields() {
        var value = jenisSelect.value;
        console.log('Toggle fields triggered, value:', value);
        
        if (value === 'targeted') {
          // Show target field
          if (targetRow) {
            targetRow.style.display = 'block';
            targetRow.classList.remove('d-none');
          }
          
          // Show deadline field (required)
          if (deadlineRow) {
            deadlineRow.style.display = 'block';
            deadlineRow.classList.remove('d-none');
          }
          
          // Make target required
          var targetInput = document.getElementById('target');
          if (targetInput) {
            targetInput.setAttribute('required', 'required');
          }
          
          // Make deadline required
          if (deadlineInput) {
            deadlineInput.setAttribute('required', 'required');
          }
          
          // Show required indicator
          if (deadlineRequired) {
            deadlineRequired.style.display = 'inline';
          }
          
          console.log('Targeted mode: Target and Deadline shown');
        } else if (value === 'open') {
          // Hide target field
          if (targetRow) {
            targetRow.style.display = 'none';
            targetRow.classList.add('d-none');
          }
          
          // Show deadline field (optional)
          if (deadlineRow) {
            deadlineRow.style.display = 'block';
            deadlineRow.classList.remove('d-none');
          }
          
          // Remove target required
          var targetInput = document.getElementById('target');
          if (targetInput) {
            targetInput.removeAttribute('required');
            targetInput.value = '';
          }
          
          // Make deadline optional
          if (deadlineInput) {
            deadlineInput.removeAttribute('required');
          }
          
          // Hide required indicator
          if (deadlineRequired) {
            deadlineRequired.style.display = 'none';
          }
          
          console.log('Open mode: Only Deadline shown (optional)');
        } else {
          // Hide both if no selection
          if (targetRow) {
            targetRow.style.display = 'none';
            targetRow.classList.add('d-none');
          }
          if (deadlineRow) {
            deadlineRow.style.display = 'none';
            deadlineRow.classList.add('d-none');
          }
        }
      }
      
      // Add event listener directly
      jenisSelect.addEventListener('change', toggleFields);
      
      console.log('Event listener attached to jenisSelect');
      
      // Check initial value
      if (jenisSelect.value === 'targeted' || jenisSelect.value === 'open') {
        toggleFields();
      }
    }, 300);
    
    // Button handlers
    var btnDraft = document.getElementById('btn-draft');
    if (btnDraft) {
      btnDraft.addEventListener('click', function() {
        alert('Draft berhasil disimpan! (Ini hanya demo)');
      });
    }
    
    var btnSubmit = document.getElementById('btn-submit');
    if (btnSubmit) {
      btnSubmit.addEventListener('click', function() {
        var form = document.querySelector('form');
        if (form) {
          form.submit();
        }
      });
    }
  }, 100);
}


