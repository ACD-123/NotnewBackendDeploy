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
        top: 75% !important;
        left: 30% !important;
        right: auto;
    }
</style>
@endpush

@section('content')
<div class="dash-set">
    <h3 id="Notification_head">Revenue Management</h3>
</div>
<div class="cart_box">
    <div class="row">
        <div class="col-md-7">
            <div id="container" style="width: 100%; height:335px;"></div>

        </div>
        <div class="col-md-5">
            <div class="chart-container">
                <div class="chart-title">Total Orders</div>
                <div class="chart-subtitle">38,500</div>
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

            <!-- Header -->

            <div class="date-selector">
                <div class="icon-container">
                    <img src="images/Calendar.png" alt="Calendar Icon">
                </div>
                <span id="displaydate">6 June, 2024</span>
                <div class="dropdown-icon" id="dropdown">
                    &#x25BC;
                </div>
                <!-- The datepicker will be attached to this hidden input field -->
                <input type="text" id="calendar">
            </div>
            <input type="hidden" id="date">



            <!-- Orders Table -->
            <div class="table-responsive">
                <table class="table table-borderless custom-table">
                    <thead>
                        <tr>
                            <th>Orders</th>
                            <th>Date & Time</th>
                            <th>Customer</th>
                            <th>Seller</th>
                            <th>Order Status</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Example Row -->
                        <tr class="table-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/Rectangle 17717.png" alt="Nike Dunk Low" class="rounded-circle product-image me-2">
                                    <div>
                                        <p class="mb-0">Nike Dunk Low</p>
                                        <small>#AV3408</small>
                                    </div>
                                </div>
                            </td>
                            <td>Today 8:00 PM</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/🤖 AI Generated Avatars_ Amir Fakhri.png" alt="Customer" class="rounded-circle profile-image me-2">
                                    <span>Zaire Herwitz</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/🤖 AI Generated Avatars_ Amir Fakhri.png" alt="Seller" class="rounded-circle profile-image me-2">
                                    <span>Zaire Herwitz</span>
                                </div>
                            </td>
                            <td><span class="badge bg-success">Delivered</span></td>
                            <td>$45.00</td>
                            <td><img src="./images/Group 4261.png" alt="MasterCard" class="payment-icon"></td>
                        </tr>
                        <!-- Repeat rows as needed -->
                        <tr class="table-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/Rectangle 17717.png" alt="Nike Dunk Low" class="rounded-circle product-image me-2">
                                    <div>
                                        <p class="mb-0">Nike Dunk Low</p>
                                        <small>#AV3408</small>
                                    </div>
                                </div>
                            </td>
                            <td>Today 8:00 PM</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/🤖 AI Generated Avatars_ Amir Fakhri.png" alt="Customer" class="rounded-circle profile-image me-2">
                                    <span>Zaire Herwitz</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/🤖 AI Generated Avatars_ Amir Fakhri.png" alt="Seller" class="rounded-circle profile-image me-2">
                                    <span>Zaire Herwitz</span>
                                </div>
                            </td>
                            <td><span class="badge bg-success">Delivered</span></td>
                            <td>$45.00</td>
                            <td><img src="./images/Group.png" alt="MasterCard" class="payment-icon"></td>
                        </tr>
                        <tr class="table-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/Rectangle 17717.png" alt="Nike Dunk Low" class="rounded-circle product-image me-2">
                                    <div>
                                        <p class="mb-0">Nike Dunk Low</p>
                                        <small>#AV3408</small>
                                    </div>
                                </div>
                            </td>
                            <td>Today 8:00 PM</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/🤖 AI Generated Avatars_ Amir Fakhri.png" alt="Customer" class="rounded-circle profile-image me-2">
                                    <span>Zaire Herwitz</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/🤖 AI Generated Avatars_ Amir Fakhri.png" alt="Seller" class="rounded-circle profile-image me-2">
                                    <span>Zaire Herwitz</span>
                                </div>
                            </td>
                            <td><span class="badge bg-success">Delivered</span></td>
                            <td>$45.00</td>
                            <td><img src="./images/Cash_App-Logo.wine 1.png" alt="MasterCard" class="payment-icon"></td>
                        </tr>
                        <tr class="table-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/Rectangle 17717.png" alt="Nike Dunk Low" class="rounded-circle product-image me-2">
                                    <div>
                                        <p class="mb-0">Nike Dunk Low</p>
                                        <small>#AV3408</small>
                                    </div>
                                </div>
                            </td>
                            <td>Today 8:00 PM</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/🤖 AI Generated Avatars_ Amir Fakhri.png" alt="Customer" class="rounded-circle profile-image me-2">
                                    <span>Zaire Herwitz</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/🤖 AI Generated Avatars_ Amir Fakhri.png" alt="Seller" class="rounded-circle profile-image me-2">
                                    <span>Zaire Herwitz</span>
                                </div>
                            </td>
                            <td><span class="badge bg-success">Delivered</span></td>
                            <td>$45.00</td>
                            <td><img src="./images/Vector.png" width="30px" style="width: 30px !important;" alt="MasterCard" class="payment-icon"></td>
                        </tr>
                        <tr class="table-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/Rectangle 17717.png" alt="Nike Dunk Low" class="rounded-circle product-image me-2">
                                    <div>
                                        <p class="mb-0">Nike Dunk Low</p>
                                        <small>#AV3408</small>
                                    </div>
                                </div>
                            </td>
                            <td>Today 8:00 PM</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/🤖 AI Generated Avatars_ Amir Fakhri.png" alt="Customer" class="rounded-circle profile-image me-2">
                                    <span>Zaire Herwitz</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/🤖 AI Generated Avatars_ Amir Fakhri.png" alt="Seller" class="rounded-circle profile-image me-2">
                                    <span>Zaire Herwitz</span>
                                </div>
                            </td>
                            <td><span class="badge bg-success">Delivered</span></td>
                            <td>$45.00</td>
                            <td><img src="./images/Group 4261.png" alt="MasterCard" class="payment-icon"></td>
                        </tr>
                        <tr class="table-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/Rectangle 17717.png" alt="Nike Dunk Low" class="rounded-circle product-image me-2">
                                    <div>
                                        <p class="mb-0">Nike Dunk Low</p>
                                        <small>#AV3408</small>
                                    </div>
                                </div>
                            </td>
                            <td>Today 8:00 PM</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/🤖 AI Generated Avatars_ Amir Fakhri.png" alt="Customer" class="rounded-circle profile-image me-2">
                                    <span>Zaire Herwitz</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/🤖 AI Generated Avatars_ Amir Fakhri.png" alt="Seller" class="rounded-circle profile-image me-2">
                                    <span>Zaire Herwitz</span>
                                </div>
                            </td>
                            <td><span class="badge bg-success">Delivered</span></td>
                            <td>$45.00</td>
                            <td><img src="./images/Group 4261.png" alt="MasterCard" class="payment-icon"></td>
                        </tr>
                        <tr class="table-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/Rectangle 17717.png" alt="Nike Dunk Low" class="rounded-circle product-image me-2">
                                    <div>
                                        <p class="mb-0">Nike Dunk Low</p>
                                        <small>#AV3408</small>
                                    </div>
                                </div>
                            </td>
                            <td>Today 8:00 PM</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/🤖 AI Generated Avatars_ Amir Fakhri.png" alt="Customer" class="rounded-circle profile-image me-2">
                                    <span>Zaire Herwitz</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="./images/🤖 AI Generated Avatars_ Amir Fakhri.png" alt="Seller" class="rounded-circle profile-image me-2">
                                    <span>Zaire Herwitz</span>
                                </div>
                            </td>
                            <td><span class="badge bg-success">Delivered</span></td>
                            <td>$45.00</td>
                            <td><img src="./images/Group.png" alt="MasterCard" class="payment-icon"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
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
                y: 10,
                color: '#775dd0'
            },
            {
                name: 'In Process',
                y: 40,
                color: '#008ffb'
            },
            {
                name: 'Complete',
                y: 50,
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
@endpush