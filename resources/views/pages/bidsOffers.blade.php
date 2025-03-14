@extends('../layout')



@section('title','Dashboard')
@push('css')
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
        top: 85% !important;
        left: 25% !important;
        right: auto;
    }
</style>
@endpush

@section('content')
<div class="dash-set">
    <h3 id="Notification_head">Bids & Offers</h3>
</div>
<div class="cart_box" style="display:none">
    <div class="row">
        <div class="col-md-6">


            <div class="chartjs-wrapper">
                <canvas id="linechart" style="width: 100% !important; height:335px !important;" class="chartjs"></canvas>
            </div>


        </div>
        <div class="col-md-6">
            <div id="container" style="width: 100%; height:335px;"></div>
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

                </ul>
            </div>
            <!-- Header -->

            <div class="date-selector">
                <div class="icon-container">
                    <img src="public/images/Calendar.png" alt="Calendar Icon">
                </div>
                <span id="displaydate">{{date('d M, Y')}}</span>
                <div class="dropdown-icon" id="dropdown">
                    &#x25BC;
                </div>
                <!-- The datepicker will be attached to this hidden input field -->
                <input type="text" id="calendar">
            </div>
            <input type="hidden" id="date">



            <!-- Orders Table -->
            <div class="table-responsive" id="ongoing">
                <table class="table table-borderless custom-table">
                    <thead>
                        <tr>
                            <th>Product Image</th>
                            <th>Product Name</th>
                            <th>Max Bid</th>
                            <th>Starting Bid</th>
                            <th>Customer</th>
                            <th>Time Remaing</th>
                        
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ongoing as $product)
                        <tr class="table-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{$product->media[0]->name}}" alt="Nike Dunk Low" class="rounded-circle product-image me-2">
                                    
                                </div>
                            </td>
                            <td><div>
                                        <p class="mb-0">{{$product->name}}</p>
                                        
                                    </div></td>
                            <td>${{$product->max_bid}}</td>
                            <td>${{$product->bids}}</td>
                            <td>{{$product->max_user!=null?$product->max_user->name:"No User Bid"}}</td>
                            <td>
                                <span class="countdown-timer" data-end-time="{{ $product->auction_End_listing }}"></span>
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
                        <th>Product Image</th>
                            <th>Product Name</th>
                            <th>Max Bid</th>
                            <th>Starting Bid</th>
                            <th>Customer</th>
                            <th>Time Remaing</th>
                        
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Example Row -->
                        @foreach($complete as $product)
                        <tr class="table-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{$product->media[0]->name}}" alt="Nike Dunk Low" class="rounded-circle product-image me-2">
                                    
                                </div>
                            </td>
                            <td><div>
                                        <p class="mb-0">{{$product->name}}</p>
                                        
                                    </div></td>
                            <td>${{$product->final_bid}}</td>
                            <td>${{$product->bids}}</td>
                            <td>{{$product->max_user!=null?$product->max_user->name:"No User Bid"}}</td>
                            <td>Sold</td>
                        </tr>
                        @endforeach
                      
                   
                    </tbody>
                </table>
            </div>
            <div class="table-responsive" id="all">
                <table class="table table-borderless custom-table">
                    <thead>
                        <tr>
                        <th>Product Image</th>
                            <th>Product Name</th>
                            <th>Max Bid</th>
                            <th>Starting Bid</th>
                            <th>Customer</th>
                            <th>Time Remaing</th>
                        
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Example Row -->
                        @foreach($all as $product)
                        <tr class="table-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{$product->media[0]->name}}" alt="Nike Dunk Low" class="rounded-circle product-image me-2">
                                    
                                </div>
                            </td>
                            <td><div>
                                        <p class="mb-0">{{$product->name}}</p>
                                        
                                    </div></td>
                            <td>${{$product->final_bid}}</td>
                            <td>${{$product->bids}}</td>
                            <td>{{$product->max_user!=null?$product->max_user->name:"No User Bid"}}</td>
                            @if($product->auction_type==0)
                            <td>Sold</td>
                            @else
                            <td>
                                <span class="countdown-timer" data-end-time="{{ $product->auction_End_listing }}"></span>
                            </td>
                            @endif
                           
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
    

        <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
        <script src="https://cdn.datatables.net/2.1.4/js/dataTables.js"></script>
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
 <script>

    var ctx = document.getElementById('linechart');
    
    var chart = new Chart(ctx, {
        // The type of chart we want to create
        type: 'line',
        
        // The data for our dataset
        data: {
            labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul","Aug","Sep","Oct","Nov","Dec"],
            datasets: [
            {
              label: "",
              backgroundColor: 'transparent',
              borderColor: 'rgb(82, 136, 255)',
              data: [2000, 11000, 10000, 14000, 11000, 17000, 14500,18000,12000,23000,17000,23000],
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
        
        // Configuration options go here
        options: {
          legend: {
             display: false
           },
          scales: {
            xAxes: [{
              gridLines: {
                display:false
              }
            }],
            yAxes: [{
              gridLines: {
                 display:true
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
              console.log(data);
              console.log(tooltipItem);
              return data['labels'][tooltipItem[0]['index']];
            },
            label: function(tooltipItem, data) {
              return  '$' + data['datasets'][0]['data'][tooltipItem['index']];
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
            categories: ['May 5', 'May 6', 'May 7', 'May 8', 'May 9', 'May 10', 'May 11']
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
            data: [2200, 2800, 2300, 1700, 2000, 1200, 1400]
        }]
    });
});
</script>
<script>
    // Select all elements with the class 'countdown-timer'
    document.querySelectorAll('.countdown-timer').forEach(function(timer) {
        // Get the end time from the data attribute
        var endTime = new Date(timer.getAttribute('data-end-time')).getTime();

        // Update the countdown every 1 second
        var interval = setInterval(function() {
            var now = new Date().getTime();
            var distance = endTime - now;

            // Calculate time remaining
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Display the result
            timer.innerHTML = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";

            // If the countdown is over, show "EXPIRED"
            if (distance < 0) {
                clearInterval(interval);
                timer.innerHTML = "EXPIRED";
            }
        }, 1000);
    });
</script>
<script>
                function showTab(tabId) {
                    // Hide all tab contents
                    document.getElementById('all').style.display = 'none';
                    document.getElementById('ongoing').style.display = 'none';
                    document.getElementById('complete').style.display = 'none';
                   
                    // Show the selected tab content
                    document.getElementById(tabId).style.display = 'block';

                    // Remove active class from all tabs
                    document.getElementById('all-tab').classList.remove('active');
                    document.getElementById('ongoing-tab').classList.remove('active');
                    document.getElementById('complete-tab').classList.remove('active');


                    // Add active class to the selected tab button
                    if (tabId === 'all') {
                        document.getElementById('all-tab').classList.add('active');
                    } else if (tabId === 'ongoing') {
                        document.getElementById('ongoing-tab').classList.add('active');
                    } else if (tabId === 'complete') {
                        document.getElementById('complete-tab').classList.add('active');
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