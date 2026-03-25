<script src="{{ asset('vendor/global/global.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap-datetimepicker/js/moment.js') }}"></script>
<script src="{{ asset('vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('vendor/peity/jquery.peity.min.js') }}"></script>
<script src="{{ asset('vendor/apexchart/apexchart.js') }}"></script>
<!-- <script src="{{ asset('js/dashboard/dashboard-1.js') }}"></script> -->
<script src="{{ asset('js/custom.min.js') }}"></script>
<script src="{{ asset('js/deznav-init.js') }}"></script>
<script src="{{ asset('ajax/api.js') }}"></script>

@if(request()->is('admin/dashboard'))
    <script src="{{ asset('js/dashboard-1.js') }}"></script>
@endif