@extends('../layout')



@section('title','Dashboard')
@push('css')
<style>
    span.relative.z-0.inline-flex.shadow-sm.rounded-md {
        display: flex;
        align-items: center;
    }

    span.relative.z-0.inline-flex.shadow-sm.rounded-md svg.w-5.h-5 {
        width: 25px;
    }

    span.relative.z-0.inline-flex.shadow-sm.rounded-md a {
        height: 50px;
    }

    relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md leading-5 span.relative.inline-flex.items-center.px-4.py-2.-ml-px.text-sm.font-medium.text-gray-500.bg-white.border.border-gray-300.cursor-default.leading-5 {
        height: 50px;
    }

    .d-flex.justify-content-center.pagination {}

    .d-flex.justify-content-center.pagination span {}

    span.relative.inline-flex.items-center.px-2 {}

    span.relative.inline-flex.items-center.px-2.py-2.text-sm.font-medium.text-gray-500.bg-white.border.border-gray-300.cursor-default.rounded-l-md.leading-5 {}

    .tabs {
        padding-top: 50px !important;
    }

    .chart-container {
        position: relative;
        width: 100%;
        height: 335px;
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .chart-title {
        position: absolute;
        top: 10px;
        left: 20px;
        font-size: 14px;
        color: #666;
    }

    .chart-subtitle {
        position: absolute;
        top: 30px;
        left: 20px;
        font-size: 24px;
        color: #333;
        font-weight: bold;
    }

    .chart-dropdown {
        position: absolute;
        top: 10px;
        right: 20px;
        font-size: 14px;
        color: #333;
        cursor: pointer;
    }


    .header-title {
        font-size: 24px;
        color: #333;
        padding-bottom: 5px;
        margin-top: 20px;
    }

    .table-responsive {
        background-color: #ffffff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        overflow-x: auto;

    }

    .custom-table thead {
        background-color: #ffff;
        border: 1px solid rgba(139, 44, 160, 1);
        vertical-align: middle;
    }

    .custom-table thead th {
        /* border-bottom: 2px solid #ddd; */
        padding: 10px;
        color: #555;
        font-weight: bold;
        text-align: center;
    }

    .custom-table tbody td {
        padding: 15px;
        vertical-align: middle;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }

    .table-row:hover {
        background-color: #f1f9ff;
    }

    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
    }

    .profile-image {
        width: 40px;
        height: 40px;
        object-fit: cover;
    }

    .payment-icon {
        width: 50px;
        object-fit: contain;
    }

    .badge {
        font-size: 14px;
        padding: 5px 12px;
        border-radius: 12px;
    }

    .badge.bg-success {
        background-color: #28a745;
        color: #fff;
    }

    .date-selector {
        display: inline-flex;
        align-items: center;
        padding: 16px 12px;
        border-radius: 20px;
        border: 1px solid #e0e0e0;
        background-color: #ffffff;
        font-family: Arial, sans-serif;
        font-size: 14px;
        box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        margin-bottom: 22px;

    }

    .icon-container img {
        width: 20px;
        height: 20px;
        margin-right: 8px;
    }

    .date-selector span {
        margin-right: 8px;
        color: #000;
    }

    .dropdown-icon {
        font-size: 12px;
        color: #6e6e6e;
    }

    /* date css */
    /* dates */

    .date-selector {
        position: relative;
        display: inline-block;
    }

    .icon-container {
        display: inline-block;
    }

    #displaydate {
        display: inline-block;
        margin-left: 5px;
    }

    .dropdown-icon {
        display: inline-block;
        cursor: pointer;
        margin-left: 5px;
    }

    #calendar {
        /* Ensure the input field is properly styled or hidden */
        display: none;
    }

    .flatpickr-calendar {
        z-index: 1000;
        /* Ensure the datepicker appears above other elements */
    }

    .flatpickr-calendar.animate.open.arrowTop.arrowLeft {
        top: 95% !important;
        left: 30% !important;
        right: auto;
    }
</style>

<style>
    .tabs {
        padding-top: 50px !important;
    }

    .chart-container {
        position: relative;
        width: 100%;
        height: 335px;
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .chart-title {
        position: absolute;
        top: 10px;
        left: 20px;
        font-size: 14px;
        color: #666;
    }

    .chart-subtitle {
        position: absolute;
        top: 30px;
        left: 20px;
        font-size: 24px;
        color: #333;
        font-weight: bold;
    }

    .chart-dropdown {
        position: absolute;
        top: 10px;
        right: 20px;
        font-size: 14px;
        color: #333;
        cursor: pointer;
    }


    .header-title {
        font-size: 24px;
        color: #333;
        padding-bottom: 5px;
        margin-top: 20px;
    }

    .table-responsive {
        background-color: #ffffff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        overflow-x: auto;

    }

    .custom-table thead {
        background-color: #ffff;
        border: 1px solid rgba(139, 44, 160, 1);
        vertical-align: middle;
    }

    .custom-table thead th {
        /* border-bottom: 2px solid #ddd; */
        padding: 10px;
        color: #555;
        font-weight: bold;
        text-align: center;
    }

    .custom-table tbody td {
        padding: 15px;
        vertical-align: middle;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }

    .table-row:hover {
        background-color: #f1f9ff;
    }

    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
    }

    .profile-image {
        width: 40px;
        height: 40px;
        object-fit: cover;
    }

    .payment-icon {
        width: 50px;
        object-fit: contain;
    }

    .badge {
        font-size: 14px;
        padding: 5px 12px;
        border-radius: 12px;
    }

    .badge.bg-success {
        background-color: #28a745;
        color: #fff;
    }

    .date-selector {}

    .icon-container img {
        width: 20px;
        height: 20px;
        margin-right: 8px;
    }

    .date-selector span {}

    .dropdown-icon {
        font-size: 12px;
        color: #6e6e6e;
    }

    /* date css */
    /* dates */

    .date-selector {}

    .icon-container {
        display: inline-block;
    }

    #displaydate {
        display: inline-block;
        margin-left: 5px;
    }

    .dropdown-icon {
        display: inline-block;
        cursor: pointer;
        margin-left: 5px;
    }

    #calendar {

        display: none;
    }

    .flatpickr-calendar {
        z-index: 1000;
        /* Ensure the datepicker appears above other elements */
    }

    .flatpickr-calendar.animate.open.arrowTop.arrowLeft {
        top: 41% !important;
        left: 30% !important;
        right: auto;
    }

    .btn-delete {
        padding: 4px 8px;
        border-radius: 4px;
        background: linear-gradient(to top, rgba(139, 44, 160, 1), rgba(3, 191, 200, 1));
        text-decoration: none;
        display: inline-block;
        font-size: 12px;
        font-family: 'Roboto', sans-serif;
        font-weight: 700;
        color: white;
        /* Default text color */
    }

    a:hover {
        color: white !important;
    }



    .form_btn {
        padding: 10px 8px !important;
        font-size: 13.51px !important;
        width: unset !important;
        background: linear-gradient(to top, #00C3C9, #8B2CA0);
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .button-calen {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
    }
</style>
@endpush

@section('content')
<div class="dash-set">
    <h3 id="Notification_head">Order Management</h3>
</div>
<div class="cart_box">
    <div class="row">
        <div class="col-md-7">
            <div id="container" style="width: 100%; height:335px;"></div>

        </div>
        <div class="col-md-5">
            <div class="chart-container">
                <div class="chart-title">Total Orders</div>
                <div class="chart-subtitle">{{$orderCount}}</div>
                <div id="semiDonutContainer" style="width: 100%; height: 335px;"></div>
                <div class="chart-dropdown">This Month <i class="fas fa-chevron-down"></i></div>
            </div>
        </div>
    </div>



</div>

<div class="row">
    <div class="col-md-12">
        <div class="tables" style="padding-bottom:40px;">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center tabs">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-tab" onclick="showTab('all')" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">All</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ongoing-tab" onclick="showTab('ongoing')" data-bs-toggle="tab" data-bs-target="#ongoing" type="button" role="tab" aria-controls="ongoing" aria-selected="false">Ongoing</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="complete-tab" onclick="showTab('complete')" data-bs-toggle="tab" data-bs-target="#complete" type="button" role="tab" aria-controls="complete" aria-selected="false">Complete</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="refund-tab" onclick="showTab('refund')" data-bs-toggle="tab" data-bs-target="#refund" type="button" role="tab" aria-controls="refund" aria-selected="false">Refund</button>
                    </li>
                </ul>
            </div>
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="header-title">Recent Orders</h3>

            </div>
            <!-- <div class="date-selector">
                <div class="icon-container">
                    <img src="public/images/Calendar.png" alt="Calendar Icon">
                </div>
                <span id="displaydate">{{date('d M, Y')}}</span>
                <div class="dropdown-icon" id="dropdown">
                    &#x25BC;
                </div>
                <input type="text" id="calendar">
            </div> -->

            <form action="{{ route('orderManagement') }}" method="GET">
            <div class="date-selector">
                <div class="icon-container">
                    <img src="public/images/Calendar.png" alt="Calendar Icon">
                </div>
                <!-- <span id="displaydate">{{date('d ,M, Y')}}</span>
                <div class="dropdown-icon" id="dropdown">
                    ▼
                </div> -->
                <!-- The datepicker will be attached to this hidden input field -->
                <input type="hidden" id="calendar" name='date' class="flatpickr-input" readonly="readonly"><input class="flatpickr-input flatpickr-mobile" name='date' tabindex="1" type="date" placeholder="">
            </div>
            <div class="date-selector">
            <select  name="status" >
        <option value="">select status</option>
        <option value="pending">pending</option>
        <option value="refund">refund</option>
        <option value="COMPLETED">completed</option>
        <option value="rejected">rejected</option>
        <option value="accepted">accepted</option>
    </select>
            </div>

            <div class="date-selector">
           
    <input type="search" class="form-group"  placeholder="Search" class="form-control" name="search" id="">
            </div>
            <div class="date-selector">
           <button type="submit">Search</button>
            </div>
            </form>
            <input type="hidden" id="date">



            <!-- Orders Table -->
            <div class="table-responsive" id="all">
                <table class="table table-borderless custom-table">
                    <thead>
                        <tr>
                            <th>Orders</th>
                            <th>Date & Time</th>
                            <th>Customer</th>
                            <!-- <th>Seller</th> -->
                            <th>Order Status</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Order Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- all -->
                        <!-- Example Row -->
                        @foreach($order as $orders)
                        
                        <tr class="table-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{$orders->orderDetails[0]->product->media[0]->name  ?? 'https://notnewbackendv2.testingwebsitelink.com/image/category/1734476352.jpg'}}" alt="Nike Dunk Low" class="rounded-circle product-image me-2">
                                    <div>
                                        <p class="mb-0">{{$orders->orderDetails[0]->product->name}}</p>
                                        <small>{{$orders->orderid}}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{$orders->created_at}}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span>{{$orders['buyer']['name']??""}}</span>
                                </div>
                            </td>
                            <!-- <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/🤖 AI Generated Avatars_ Amir Fakhri.png" alt="Seller" class="rounded-circle profile-image me-2">
                                    <span>Zaire Herwitz</span>
                                </div>
                            </td> -->
                            <td><span class="badge bg-success">{{ucfirst(strtolower($orders->orderDetails[0]->status))}}</span></td>
                            <td>${{$orders->order_total+$orders->shipping_cost}}</td>
                            <td>{{$orders->payment_type}}</td>
                            <!-- <td><img src="./images/Group 4261.png" alt="MasterCard" class="payment-icon"></td> -->
                            <td>

                                <form action="{{ route('order.detail', $orders->id) }}" method="GET" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn form_btn">Detail</button>


                                </form>
                            </td>
                        </tr>
                        @endforeach


                    </tbody>





                </table>


            </div>
            <div class="table-responsive" id="ongoing">
                <table class="table table-borderless custom-table">
                    <thead>
                        <tr>
                            <th>Orders</th>
                            <th>Date & Time</th>
                            <th>Customer</th>
                            <!-- <th>Seller</th> -->
                            <th>Order Status</th>
                            <th>Amount</th>
                            <th>Payment Method</th>

                        </tr>
                    </thead>
                    <tbody>
                        <!-- all -->
                        <!-- Example Row -->
                        @foreach($ongonig as $orders)
                        <tr class="table-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{$orders->orderDetails[0]->product->media[0]->name}}" alt="Nike Dunk Low" class="rounded-circle product-image me-2">
                                    <div>
                                        <p class="mb-0">{{$orders->orderDetails[0]->product->name}}</p>
                                        <small>{{$orders->orderid}}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{$orders->created_at}}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span>{{$orders['buyer']['name']??""}}</span>
                                </div>
                            </td>
                          
                            <td><span class="badge bg-success">Pending</span></td>
                            <td>${{$orders->order_total+$orders->shipping_cost}}</td>
                            <td>{{$orders->payment_type}}</td>
                            <!-- <td><img src="./images/Group 4261.png" alt="MasterCard" class="payment-icon"></td> -->
                            <td>

                                <form action="{{ route('order.detail', $orders->id) }}" method="GET" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn form_btn">Detail</button>


                                </form>
                            </td>
                        </tr>
                        @endforeach


                    </tbody>





                </table>

            </div>
            <div class="table-responsive" id="complete">
                <table class="table table-borderless custom-table">
                    <thead>
                        <tr>
                            <th>Orders</th>
                            <th>Date & Time</th>
                            <th>Customer</th>
                            <!-- <th>Seller</th> -->
                            <th>Order Status</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- all -->
                        <!-- Example Row -->
                        @foreach($complete as $orders)
                        <tr class="table-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{$orders->orderDetails[0]->product->media[0]->name ??  'https://notnewbackendv2.testingwebsitelink.com/image/category/1734476352.jpg'}}" alt="Nike Dunk Low" class="rounded-circle product-image me-2">
                                    <div>
                                        <p class="mb-0">{{$orders->orderDetails[0]->product->name}}</p>
                                        <small>{{$orders->orderid}}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{$orders->created_at}}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span>{{$orders['buyer']['name']??""}}</span>
                                </div>
                            </td>
                            <!-- <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/🤖 AI Generated Avatars_ Amir Fakhri.png" alt="Seller" class="rounded-circle profile-image me-2">
                                    <span>Zaire Herwitz</span>
                                </div>
                            </td> -->
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>${{$orders->order_total+$orders->shipping_cost}}</td>
                            <td>{{$orders->payment_type}}</td>
                            <!-- <td><img src="./images/Group 4261.png" alt="MasterCard" class="payment-icon"></td> -->
                            <td>

                                <form action="{{ route('order.detail', $orders->id) }}" method="GET" style="display:inline;">
                                    @csrf
                                    <button type="submit"  class="btn form_btn">Detail</button>


                                </form>
                            </td>
                        </tr>
                        @endforeach


                    </tbody>





                </table>
            </div>
            <div class="table-responsive" id="refund">
                <table class="table table-borderless custom-table">
                    <thead>
                        <tr>
                            <th>Orders</th>
                            <th>Date & Time</th>
                            <th>Customer</th>
                            <!-- <th>Seller</th> -->
                            <th>Order Status</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- all -->
                        <!-- Example Row -->
                        @foreach($refund as $orders)
                        <tr class="table-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{$orders->orderDetails[0]->product->media[0]->name}}" alt="Nike Dunk Low" class="rounded-circle product-image me-2">
                                    <div>
                                        <p class="mb-0">{{$orders->orderDetails[0]->product->name}}</p>
                                        <small>{{$orders->orderid}}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{$orders->created_at}}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span>{{$orders['buyer']['name']??""}}</span>
                                </div>
                            </td>
                            <!-- <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/🤖 AI Generated Avatars_ Amir Fakhri.png" alt="Seller" class="rounded-circle profile-image me-2">
                                    <span>Zaire Herwitz</span>
                                </div>
                            </td> -->
                            <td><span class="badge bg-success">Refunded</span></td>
                            <td>${{$orders->order_total+$orders->shipping_cost}}</td>
                            <td>{{$orders->payment_type}}</td>
                            <!-- <td><img src="./images/Group 4261.png" alt="MasterCard" class="payment-icon"></td> -->
                            <td>

                                <form action="{{ route('order.detail', $orders->id) }}" method="GET" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn form_btn">Detail</button>


                                </form>
                            </td>
                        </tr>
                        @endforeach


                    </tbody>





                </table>
            </div>

         

        </div>
    </div>
</div>

@endsection

@push('js')


    
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

   
    <script>
         document.addEventListener('DOMContentLoaded', function() {
             // Initialize Flatpickr
             const calendar = flatpickr("#calendar", {
                 dateFormat: 'j F Y',
                 onChange: function(selectedDates, dateStr, instance) {
                     // Update the hidden input and display date span
                     document.getElementById('date').value = dateStr;
                     document.getElementById('displaydate').textContent = dateStr;
                 }
             });
 
             // Show the datepicker when clicking the dropdown icon
             document.getElementById('dropdown').addEventListener('click', function() {
                 calendar.open(); // Open the Flatpickr calendar
             });
         });
     </script>
    
  

        
<script>
Highcharts.chart('semiDonutContainer', {
    chart: {
        type: 'pie',
        backgroundColor: null,
        height: '100%',
    },
    title: {
        text: '',
    },
    subtitle: {
        text: '',
    },
    tooltip: {
        enabled: false,
    },
    plotOptions: {
        pie: {
            startAngle: -90,
            endAngle: 90,
            center: ['50%', '75%'],
            size: '110%',
            innerSize: '60%',
            dataLabels: {
                enabled: true,
                format: '{y}%',
                distance: -30,
                style: {
                    fontSize: '16px',
                    color: '#fff',
                    textOutline: 0,
                }
            },
            borderWidth: 0,
        }
    },
    series: [{
        name: 'Orders',
        data: [{
                name: 'Refund',
                y: {{$refundPercentage}},
                color: '#775dd0'
            },
            {
                name: 'In Process',
                y: {{$ongoingPercentage}},
                color: '#008ffb'
            },
            {
                name: 'Complete',
                y: {{$completedPercentage}},
                color: '#00e396'
            }
        ]
    }]
});

</script>

<script>
  $(function () { 
  Highcharts.setOptions({
    colors: ['#465a95'],
    chart: {
        style: {
            fontFamily: 'sans-serif',
            color: '#8B2CA0'
        }
    }
});  
  $('#container').highcharts({
        chart: {
            type: 'column',
            backgroundColor: '#FFF'
        },
        title: {
            text: 'Weekly Revenue',
            style: {  
              color: '#000'
            }
        },
        xAxis: {
            tickWidth: 0,
            labels: {
              style: {
                  color: '#000',
                 }
              },
            categories: @json($dates)
        },
        yAxis: {
           gridLineWidth: .5,
		      gridLineDashStyle: 'dash',
		      gridLineColor: 'black',
           title: {
                text: '',
                style: {
                  color: '#000'
                 }
            },
            labels: {
              formatter: function() {
                        return '$'+Highcharts.numberFormat(this.value, 0, '', ',');
                    },
              style: {
                  color: '#000',
                 }
              }
            },
        legend: {
            enabled: false,
        },
        credits: {
            enabled: false
        },
        tooltip: {
           valuePrefix: '$'
        },
        plotOptions: {
		      column: {
			      borderRadius: 2,
             pointPadding: 0,
			      groupPadding: 0.1
            } 
		    },
        series: [{
            name: 'Revenue',
            data: @json($orderTotals)
        }]
    });
});
</script>
<script>
                function showTab(tabId) {
                    // Hide all tab contents
                    document.getElementById('all').style.display = 'none';
                    document.getElementById('ongoing').style.display = 'none';
                    document.getElementById('complete').style.display = 'none';
                    document.getElementById('refund').style.display = 'none';

                    // Show the selected tab content
                    document.getElementById(tabId).style.display = 'block';

                    // Remove active class from all tabs
                    document.getElementById('all-tab').classList.remove('active');
                    document.getElementById('ongoing-tab').classList.remove('active');
                    document.getElementById('complete-tab').classList.remove('active');
                    document.getElementById('refund-tab').classList.remove('active');

                    // Add active class to the selected tab button
                    if (tabId === 'all') {
                        document.getElementById('all-tab').classList.add('active');
                    } else if (tabId === 'ongoing') {
                        document.getElementById('ongoing-tab').classList.add('active');
                    } else if (tabId === 'complete') {
                        document.getElementById('complete-tab').classList.add('active');
                    } else if (tabId === 'refund') {
                        document.getElementById('refund-tab').classList.add('active');
                    } else {
                        document.getElementById('all-tab').classList.add('active');
                    }
                }

                // Set default tab as active when the page loads
                window.onload = function() {
                    showTab('all');
                };
            </script>
@endpush