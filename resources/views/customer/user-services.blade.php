@extends('adminlte::page')

@section('content')
<h3 class="text-center mb-5">USER-SERVICES</h3>

@include('partials.tables', ['data' => $customerServices, 'customer' => $customer,'route' => "services",'name' => "Service",'routeActivateAll' => "customer.services.active-all", 'active' => $active])

@endsection
<script>
    function toggle(source) {
        checkboxes = document.getElementsByName('checkbox');
        for (var i = 0, n = checkboxes.length; i < n; i++) {
            checkboxes[i].checked = source.checked;
        }
    }
</script>
