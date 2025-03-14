@extends('../layout')


@section('title','Dashboard')
@push('css')
<style>
    /* data table */
    /* Custom Styles */
    .dataTables_wrapper {
        font-size: 14px;
    }

    .dataTables_wrapper td img {
        width: 50px;
        height: 50px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 8px 16px;
        margin: 0 2px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background-color: #f8f9fa;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background-color: rgba(0, 195, 201, 1);
        color: white;
    }

    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        margin-bottom: 10px;
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .dataTables_wrapper .dataTables_info {
        margin-top: 10px;
    }

    .btn {
        padding: 4px 8px;
        border-radius: 4px;
        color: white;
        text-decoration: none;
        display: inline-block;
        font-size: 12px;
        font-family: 'Roboto', sans-serif;
        font-weight: 700;
    }

    .btn-edit {
        background-color: rgba(0, 195, 201, 1);
        font-family: 'Roboto', sans-serif;
    }

    .btn-delete {
        background-color: rgba(239, 42, 42, 1);
        font-family: 'Roboto', sans-serif;
    }

    .status-active {
        background-color: rgba(102, 234, 28, 1);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        text-align: center;
        font-family: 'Roboto', sans-serif;
    }

    .type-product {
        background-color: rgba(26, 140, 233, 1);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        text-align: center;
        font-family: 'Roboto', sans-serif;
    }

    .btn-edit-attr {
        background-color: rgba(0, 195, 201, 1);
    }
</style>
@endpush

@section('content')

<div class="col-md-12">
    <div class="dash-set">
        <h3>Seller Dashboard</h3>
       
    </div>
    <div class="boxes_div">
        <div class="box">
            <img src="https://notnewbackendv2.testingwebsitelink.com/public/images/tabler_coin.png" alt="">
            <p>Total Revenue</p>
            <h1>${{$vendorDashboard[0]['total']}}</h1>
        </div>
        <div class="box">
            <img src="https://notnewbackendv2.testingwebsitelink.com/public/images/Vector (2).png" alt="">
            <p>Ongoing Orders</p>
            <h1>{{$vendorDashboard[0]['ongoing']}}</h1>
        </div>
        <div class="box">
            <img src="https://notnewbackendv2.testingwebsitelink.com/public/images/trolly.png" alt="">
            <p>Complete Orders</p>
            <h1>{{$vendorDashboard[0]['complete']}}</h1>
        </div>
        <div class="box">
            <img src="https://notnewbackendv2.testingwebsitelink.com/public/images/tabler_coin (1).png" alt="">
            <p>Refund Orders</p>
            <h1>{{$vendorDashboard[0]['refund']}}</h1>
        </div>

    </div>
    <div class="cart_box mt-5">
        <div class="row">
            <div class="col-md-6">


                <div class="chartjs-wrapper">
                    <canvas id="linechart" style="width: 100% !important; height:335px !important;" class="chartjs"></canvas>
                </div>


            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-title">Total Orders</div>
                    <div class="chart-subtitle">{{$vendorDashboard[0]['totalOrder']}}</div>
                    <div id="semiDonutContainer" style="width: 100%; height: 335px;"></div>
                    <div class="chart-dropdown">This Month <i class="fas fa-chevron-down"></i></div>
                </div>
            </div>
        </div>



    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="container" style="padding: 0;">
                <div class="customer-card">
                    <div class="customer-info">
                        <img src="{{env('APP_URL').$vendorDashboard[0]['shop']['cover_image'] ?? 'https://notnewbackendv2.testingwebsitelink.com/public/images/logo.png'}} " style="border-radius: unset;" alt="">
                        <div class="customer-details">
                            <div class="name">{{$vendorDashboard[0]['shop']['fullname']}}</div>
                            <div class="meta">
                                <span>90% Positive feedback</span>
                                <span>125k Followers</span>
                                <span>6.7M Items Sold</span>
                            </div>
                        </div>
                    </div>
                    <div class="customer-actions">
                        <a href="#"><i class="fa-solid fa-share"></i> Share</a>
                        <a href="#"><i class="fa-solid fa-envelope"></i> Message seller</a>
                        <a href="#"><i class="fa-solid fa-heart"></i> Save this Customer</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- <div class="tabs_container">
        <div class="row">
            <div class="col-md-7">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">All</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="about-ongoing" data-bs-toggle="tab" data-bs-target="#ongoing" type="button" role="tab" aria-controls="ongoing" aria-selected="false">Ongoing</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab" aria-controls="completed" aria-selected="false">Completed</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="feedrefundback-tab" data-bs-toggle="tab" data-bs-target="#refund" type="button" role="tab" aria-controls="refund" aria-selected="false">Refund</button>
                    </li>
                </ul>
            </div>
            <div class="col-md-5">
                <div class="p-1 bg-white  rounded-pill shadow-sm mb-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button id="button-addon2" type="submit" class="btn btn-link text-dark"><i class="fa fa-search"></i></button>
                        </div>
                        <input type="search" placeholder="Search All 2,656 items" aria-describedby="button-addon2" class="form-control border-0 bg-white">
                    </div>
                </div>
            </div>
        </div>
    </div> -->
    <form action="{{ route('vendor.dashboard',['id' => $id]) }}" method="GET">
            <div class="date-selector">
                
               
                <input class="flatpickr-input flatpickr-mobile" name='date' tabindex="1" type="date" placeholder="">
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
    <div class="row">
        <div class="col-md-12">
            @foreach($vendorDashboard[0]['vendorOrder'] as $orders )
            <div class="seller_order_card d-flex align-items-center p-3 shadow-sm">
                <div class="order-image">
                    <img src="{{$orders->orderDetails[0]->product->media[0]['name']}}" alt="" class="img-fluid rounded">
                </div>
                <div class="order-details ms-3">
                    <p class="order-number mb-1">{{$orders->orderid}}</p>
                    <p class="order-title mb-1">{{$orders->address}}</p>
                    <p class="refund-requested text-primary mb-0">
                        <?php
                            if($orders->orderDetails[0]['status'] == "COMPLETED" && $orders->orderDetails[0]['refunded'] == 0){
                                echo 'Completed';
                            } elseif($orders->orderDetails[0]['status'] == "COMPLETED" && $orders->orderDetails[0]['refunded'] == 1){
                                echo 'Refunded';
                            }elseif($orders->orderDetails[0]['status'] == "pending"){
                                echo 'Pending';
                            }elseif($orders->orderDetails[0]['status'] == "accepted"){
                                echo 'Accepted';
                            }elseif($orders->orderDetails[0]['status'] == "rejected"){
                                echo 'Rejected';
                            }
                        ?>
                    </p>
                </div>
                <div class="ms-auto">
                    <form action="{{route('order.detail' ,$orders->id) }}" method="GET">
                        <button type="submit" class="btn btn-primary btn-gradient">View Details</button>
                    </form>
                </div>
            </div>
            @endforeach
            {{ $vendorDashboard[0]['vendorOrder']->links('vendor.pagination.default') }}

          
        </div>
    </div>


</div>
@endsection

@push('js')



    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
    	
new DataTable('#example');
</script>


@php
    $labels = [];
    $data = [];
@endphp

@foreach($vendorDashboard[0]['chart'] as $chart)
    @php
        $labels[] = $chart['month_name'];
        $data[] = $chart['order_total'];
    @endphp
@endforeach


<script>
    var ctx = document.getElementById('linechart');
    
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($labels) !!}, // Dynamic labels
            datasets: [
                {
                    label: "",
                    backgroundColor: 'transparent',
                    borderColor: 'rgb(82, 136, 255)',
                    data: {!! json_encode($data) !!}, // Dynamic data
                    lineTension: 0.3,
                    pointRadius: 5,
                    pointBackgroundColor: 'rgba(255,255,255,1)',
                    pointHoverBackgroundColor: 'rgba(255,255,255,0.6)',
                    pointHoverRadius: 10,
                    pointHitRadius: 30,
                    pointBorderWidth: 2,
                    pointStyle: 'rectRounded'
                }
            ]
        },
        options: {
            legend: {
                display: false
            },
            scales: {
                xAxes: [{
                    gridLines: {
                        display: false
                    }
                }],
                yAxes: [{
                    gridLines: {
                        display: true
                    },
                    ticks: {
                        callback: function(value) {
                            var ranges = [
                                { divider: 1e6, suffix: 'M' },
                                { divider: 1e3, suffix: 'k' }
                            ];
                            function formatNumber(n) {
                                for (var i = 0; i < ranges.length; i++) {
                                    if (n >= ranges[i].divider) {
                                        return (n / ranges[i].divider).toString() + ranges[i].suffix;
                                    }
                                }
                                return n;
                            }
                            return '$' + formatNumber(value);
                        }
                    },
                }]
            },
            tooltips: {
                callbacks: {
                    title: function(tooltipItem, data) {
                        return data['labels'][tooltipItem[0]['index']];
                    },
                    label: function(tooltipItem, data) {
                        return '$' + data['datasets'][0]['data'][tooltipItem['index']];
                    },
                },
                backgroundColor: '#606060',
                titleFontSize: 14,
                titleFontColor: '#ffffff',
                bodyFontColor: '#ffffff',
                bodyFontSize: 18,
                displayColors: false
            }
        }
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
                y: {{$vendorDashboard[0]['refundPercentage']}},
                color: '#775dd0'
            },
            {
                name: 'In Process',
                y: {{$vendorDashboard[0]['ongoingPercentage']}},
                color: '#008ffb'
            },
            {
                name: 'Complete',
                y: {{$vendorDashboard[0]['completedPercentage']}},
                color: '#00e396'
            }
        ]
        }]
    });
    
    </script>
@endpush