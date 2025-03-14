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
@endpush