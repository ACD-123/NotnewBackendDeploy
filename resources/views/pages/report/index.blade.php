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
<div class="dash-set">

</div>


<div class="row">
    <div class="col-md-12">
        <div class="tables" style="padding-bottom:40px;">

            <table class="table table-borderless custom-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User </th>
                        <th>Seller </th>
                        <th>Reason </th>
                        <th>Message </th>
                    </tr>
                </thead>
                <tbody>

                @foreach($report as $reports)
                    <tr>
                       
                        <td>{{ (request()->get('page', 1) - 1) * 16 + $loop->iteration }}</td>
                        <td>
                        <img src="{{ $reports->user->profile_image}}" style="height: 100px; width: 100px; " alt="" class="rounded-circle">

                            {{$reports->user->name}}
                        </td>
                        <td>
                        <img src="{{ $reports->seller->cover_image}}" style="height: 100px; width: 100px;" alt="" class="rounded-circle">

                            {{$reports->seller->fullname}}
                        </td>
                        <td>{{$reports->reason}}</td>
                        <td>{{$reports->message}}</td>
                        

                    </tr>
                    @endforeach

                    <!-- Add more rows here -->
                </tbody>





            </table>
            {{ $report->links('vendor.pagination.default') }}

        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('#example').DataTable({
            "pagingType": "full_numbers",
            "lengthMenu": [10, 25, 50, 75, 100],
            "pageLength": 10
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
    

        <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
        <script src="https://cdn.datatables.net/2.1.4/js/dataTables.js"></script>
    
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