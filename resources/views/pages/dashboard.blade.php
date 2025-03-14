@extends('../layout')



@section('title','Dashboard')
@push('css')
<style>
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
        top: 35% !important;
        left: 30% !important;
        right: auto;
    }

    .form-control {
        padding: 12px 36px;
        border-radius: 21px;
    }

    .form-box input.form-control {
        margin-bottom: 0;
    }

    label {
        color: #495057;
    }

    .chevron-icon {
        font-size: 16px;
        position: absolute;
        top: 18px;
        left: 125px;
    }

    .dropdown {
        position: relative;
    }

    .form_btn {
        padding: 11px 20px;
        width: unset !important;
    }

    .upload-box {
        margin-top: 40px;
        height: 300px !important;
        width: unset !important;
    }

    .upload-icon {
        width: 60px;
    }


    .upload-box {
        position: relative;
    }

    .file-input {
        position: absolute;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .upload-content {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;

        cursor: pointer;
    }

    .upload-icon {
        width: 50px;
        height: 50px;
    }

    .upload-text {
        margin-left: 10px;
    }

    .image-preview {
        margin-top: 20px;
        position: relative;
        display: inline-block;
    }

    .image-preview img {
        width: 100%;
        height: 350px;
    }

    .image-preview a {
        display: block;
        margin-top: 10px;
        color: blue;
        text-decoration: underline;
    }

    .remove-icon {
        position: absolute;
        top: 3px;
        right: 0;
        background-color: red;
        color: white;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        text-align: center;
        line-height: 30px;
        cursor: pointer;
        font-weight: bold;
    }

    .link-container {
        margin-top: 10px;
        display: flex;
        align-items: baseline;
        justify-content: center;
        /* Center the link and button */
    }

    .image-link {
        color: blue;
        text-decoration: underline;
        border: 2px dashed #ccc;
        padding: 5px;
        margin-right: 10px;
        width: 100%;
    }

    .copy-button {
        margin-top: 4px;
        background: linear-gradient(to top, #00C3C9, #8B2CA0);
        color: white;
        border: none;
        padding: 3px 10px;
        border-radius: 10px;
        font-size: 22.51px;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    .product-image{
        width: 60px !important;
    height: 60px !important;
    }

    .copy-button:hover {
        background: linear-gradient(to top, #00C3C9, #8B2CA0);
        color: white;
    }

    .but.sec button {
        width: 100% !important;
    }
</style>

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
    <h3>Admin Dashboard</h3>
</div>
<div class="boxes_div">
    <div class="box">
        <img src="public/images/tabler_coin.png" alt="">
        <p>Total Revenue</p>
        <h1>${{$totalPrice}}</h1>
    </div>
    <div class="box">
        <img src="public/images/Vector (2).png" alt="">
        <p>Ongoing Orders</p>
        <h1>{{$pending}}</h1>
    </div>
    <div class="box">
        <img src="public/images/trolly.png" alt="">
        <p>Complete Orders</p>
        <h1>{{$complete}}</h1>
    </div>
    <div class="box">
        <img src="public/images/tabler_coin (1).png" alt="">
        <p>Refund Orders</p>
        <h1>{{$refund}}</h1>
    </div>

</div>
<div class="cart_box">
    <div class="row">
        <div class="col-md-6">


            <div class="chartjs-wrapper">
                <canvas id="linechart" style="width: 334px; height: 335px; display: block; box-sizing: border-box;" class="chartjs" width="668" height="670"></canvas>
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="header-title">Recent Orders</h3>

            </div>
            <form action="{{ route('home') }}" method="GET">
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
            <div class="table-responsive">
                <table class="table table-borderless custom-table">
                    <thead>
                        <tr>
                            <th>Orders</th>
                            <th>Date &amp; Time</th>
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
                        @foreach($order as $orders)
                        <tr class="table-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{$orders['orderDetails'][0]['product']['media'][0]['name'] ?? 'https://notnewbackendv2.testingwebsitelink.com/image/category/1734476352.jpg' }} " alt="Nike Dunk Low" class="rounded-circle product-image me-2">
                                    <div>
                                        <p class="mb-0">{{$orders['orderDetails'][0]['product']['name']}}</p>
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
                            <td><span class="badge bg-success">{{$orders['orderDetails'][0]['status']}}</span></td>
                            <td>{{$orders['orderDetails'][0]['price']}}</td>
                            <td>{{$orders->payment_type}}</td>
                            <!-- <td><img src="./images/Group 4261.png" alt="MasterCard" class="payment-icon"></td> -->
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
              data: @json($result),
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
        document.addEventListener('DOMContentLoaded', function() {
    // Initialize Flatpickr
    const calendar = flatpickr("#calendar", {
        dateFormat: 'Y j F',  // Display format
        onChange: function(selectedDates, dateStr, instance) {
            const formattedDate = instance.formatDate(selectedDates[0], "Y-m-d");  // Format as YYYY-MM-DD

            // Update the hidden input with the formatted date
            document.getElementById('date').value = formattedDate;

            // Update the display span with the selected date
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
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('fileInput');
        const imagePreview = document.getElementById('imagePreview');
        const linkContainer = document.getElementById('linkContainer');

        // Check if an image is stored in local storage
        const storedImage = localStorage.getItem('uploadedImage');
        if (storedImage) {
            displayImage(storedImage);
            displayLink(storedImage);
        }

        

        // Function to display the image with a remove button
        function displayImage(imageDataUrl) {
            imagePreview.innerHTML = ''; // Clear previous content

            const img = document.createElement('img');
            img.src = imageDataUrl;
            img.alt = 'Uploaded Image';

            const removeIcon = document.createElement('div');
            removeIcon.className = 'remove-icon';
            removeIcon.innerHTML = '&times;'; // Cross icon
            removeIcon.addEventListener('click', function() {
                // Remove the image and clear local storage
                localStorage.removeItem('uploadedImage');
                imagePreview.innerHTML = '';
                linkContainer.innerHTML = ''; // Clear link container
            });

            imagePreview.appendChild(img);
            imagePreview.appendChild(removeIcon);
        }

        // Function to display the link and copy button below the image
        function displayLink(imageDataUrl) {
            linkContainer.innerHTML = ''; // Clear previous content

            const link = document.createElement('a');
            link.href = imageDataUrl;
            link.className = 'image-link';
            link.textContent = 'View Image';
            link.target = '_blank'; // Open link in a new tab

            const copyButton = document.createElement('button');
            copyButton.className = 'copy-button';
            copyButton.textContent = 'Copy';
            copyButton.addEventListener('click', function() {
                navigator.clipboard.writeText(imageDataUrl).then(() => {
                    alert('Image link copied to clipboard!');
                });
            });

            linkContainer.appendChild(link);
            linkContainer.appendChild(copyButton);
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
@endpush