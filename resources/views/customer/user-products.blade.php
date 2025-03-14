@extends('adminlte::page')

@section('content')
<h3 class="text-center mb-5">USER-PRODUCTS</h3>
@include('partials.tables', ['data' => $customerProduct, 'customer' => $customer,'route' => "products",'name' => "Product",'routeActivateAll' => "customer.products.active-all", 'active' => $active])

@endsection

<script>
    function toggle(source) {
        checkboxes = document.getElementsByName('checkbox');
        for (var i = 0, n = checkboxes.length; i < n; i++) {
            checkboxes[i].checked = source.checked;
        }
    }
</script>
