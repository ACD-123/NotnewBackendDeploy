@extends('../layout')



@section('title','Dashboard')
@push('css')
<style>
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
        top: 109% !important;
        left: 30% !important;
        right: auto;
    }
</style>
@endpush

@section('content')
<div class="dash-set">
    <h3 id="Notification_head">User Management</h3>
</div>
<div class="cart_box">
    <div class="row">
        <div class="col-md-12">
            <!-- <div id="container" style="width: 100%; height:335px;"></div> -->
            <div class="chartjs-wrapper">
                <canvas id="linechart" class="chartjs"></canvas>
            </div>

        </div>

    </div>



</div>

<div class="row">
    <div class="col-md-12">
        <div class="tables" style="padding-bottom:40px;">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-3 tabs">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="users-tab" data-bs-toggle="tab" type="button" role="tab" aria-controls="vendor" aria-selected="true" onclick="showTab('vendor')">Vendors</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="vendors-tab" data-bs-toggle="tab" type="button" role="tab" aria-controls="customer" aria-selected="false" onclick="showTab('customer')">Customers</button>
                    </li>
                </ul>
            </div>

    

            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="vendor" role="tabpanel" aria-labelledby="users-tab">
                    @foreach($data as $vendores)
                    <div class="card p-3 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="store-image">
                                    <img src="{{$vendores['vendor']['profile_url']??'https://notnew.testingwebsitelink.com/static/media/blankuser.5f897c251b8ea07dd61f.jpg'}}" alt="" class="rounded-circle">
                                </div>
                                <div class="ms-3 text_set">
                                   
                                    <h5 class="mb-0">{{$vendores['vendor']['name']}}</h5>
                                    <small>{{$vendores['feedbackCount']}} Positive feedback</small><br>
                                </div>
                            </div>
                            <div class="text-end">
                            <a class="btn btn-gradient" style="padding:8px 20px">View Chats</a>
                            <form action="{{ route('vendor.dashboard', $vendores['vendor']['id']) }}" method="GET" style="display:inline;">
                                    @csrf
                                    <button class="btn btn-gradient">Visit Seller Shop</button>



                                </form>

                                <div class="form-check form-switch mt-2">
                                    <form action="{{ route('activeInactiveVendor.active', $vendores['vendor']['id']) }}" method="POST" id="vendorStatusForm">
                                        @csrf
                                        <label class="form-check-label ms-2" id="inctive" for="statusSwitch">In Active</label>
                                        <input type="checkbox" class="form-check-input ms-2" id="statusSwitch" name="isTrustedSeller"
                                            {{ $vendores['vendor']['isTrustedSeller'] == 1 ? 'checked' : '' }}
                                            onchange="document.getElementById('vendorStatusForm').submit();">
                                        <label class="form-check-label ms-2" for="statusSwitch">Active</label>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="tab-pane fade" id="customer" role="tabpanel" aria-labelledby="vendors-tab">
                @foreach($customer as $customeres)
                    <div class="card p-3 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="store-image">
                                    <img src="{{$customeres->profile_image??'https://notnew.testingwebsitelink.com/static/media/blankuser.5f897c251b8ea07dd61f.jpg'}}" alt="" class="rounded-circle">
                                </div>
                                <div class="ms-3 text_set">
                                    <h5 class="mb-0">{{$customeres->name}}</h5>
                                    <h6 class="mb-0">{{$customeres->email }}</h6>
                                    <h6 class="mb-0">{{$customeres->address }}</h6>
                                </div>
                                
                            </div>
                            <div class="text-end" style="white-space:nowrap">
                            <a class="btn btn-gradient" style="padding:8px 20px">View Chats</a>
                                <div class="form-check form-switch mt-2">
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <script>
                function showTab(tabId) {
                    // Hide both vendor and customer tabs
                    document.getElementById('vendor').classList.remove('show', 'active');
                    document.getElementById('customer').classList.remove('show', 'active');

                    // Show the selected tab
                    document.getElementById(tabId).classList.add('show', 'active');

                    // Update the active class on the tabs
                    document.getElementById('users-tab').classList.remove('active');
                    document.getElementById('vendors-tab').classList.remove('active');

                    if (tabId === 'vendor') {
                        document.getElementById('users-tab').classList.add('active');
                    } else {
                        document.getElementById('vendors-tab').classList.add('active');
                    }
                }
            </script>





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
@endpush