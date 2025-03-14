@extends('../layout')



@section('title','Dashboard')
@push('css')
<style>
   .center {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
}
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}
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
                        <th>Seller </th>
                        <th>Amount </th>
                        
                        
                        
                        <th>Bank Name</th>
                        <th>A.H.Name</th>
                        <th>Account Number</th>
                        <th>Swift Number</th>
                        <th>Date </th>
                        <th>Status</th>
                        <th>Action </th>
                    </tr>
                </thead>
                <tbody>

                @foreach($withdraw as $withdraws)
                    <tr>
                        <td>{{ (request()->get('page', 1) - 1) * 16 + $loop->iteration }}</td>
                        <td>{{$withdraws->seller->fullname ?? "Empty"}} </td>
                        <td>${{$withdraws->amount}}</td>
                     
                       

                       <td>{{$withdraws->user_bank->bank->fullname}}</td> 
                       <td>{{$withdraws->user_bank->accountName}}</td> 
                       <td>{{$withdraws->user_bank->accountNumber}}</td> 
                       <td>{{$withdraws->user_bank->bic_swift}}</td> 
                       <td>{{date("Y-m-d",strtotime($withdraws->date))}}</td>
                       <td>@if($withdraws->status=="Pending")
                        <span class="badge bg-primary">Pending</span>
                        @elseif($withdraws->status=="Approved")
                        <span class="badge bg-success">Approved</span>
                        @elseif($withdraws->status=="Rejected")
                        <span class="badge bg-danger">Rejected</span>
                        @endif
                       </td>
                       <td>@if($withdraws->status=="Pending")
                       <button type="button" class="btn btn-primary" onclick="openEditModal({{$withdraws->id}},{{$withdraws->amount}})">Update</button>
                        @endif
                       </td>
                        

                    </tr>
                    @endforeach

                    <!-- Add more rows here -->
                </tbody>





            </table>
            {{ $withdraw->links('vendor.pagination.default') }}

        </div>
    </div>
</div>
<div class="modal center" id="customEditModal">
        <div class="container">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <!-- Your Edit Service Form Goes Here -->
            <form class="form-control" method="post" action="{{route('sellerWithdrawUpdate')}}" enctype="multipart/form-data">
                @csrf
    <input type="hidden" name="service_id" id="serviceId">
				<div class="mb-3">
        <label for="status" class="form-label">Status</label>
		<select name="status" id="status-select" class="form-control" onchange="removeRequired()" required>
			<option value="">Select an Option</option>
			<option value="approved">Approved</option>
			<option value="rejected">Rejected</option>
		</select>
    </div>
  
    
    <div class="mb-3">
        <label for="heading" class="form-label">Amount</label>
        <input type="text" class="form-control numeric" name="amount_withdraw" id="amountInput" required>
		 <div class="h4 alert alert-danger" id="newdiverror" style="display:none;background: red !important; color:#fff"></div>
    </div>
				  <div class="mb-3">
        <label for="new_image" class="form-label">Note:</label>
        <textarea class="form-control" name="notes" id="contentField" rows="4" required></textarea>
    </div>
    <div class="mb-3">
        <label for="image" class="form-label">Image</label>
        <input class="form-control" type="file" name="image" id="imageField" required>
    </div>
        <button type="submit" class="btn form_btn">Update</button>
</form>


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
function removeRequired() {
    const selectElement = document.getElementById('status-select');
	console.log(selectElement.value);
    if (selectElement.value == "rejected") {
        document.getElementById('amountInput').removeAttribute('required');
        document.getElementById('imageField').removeAttribute('required');
		document.getElementById('contentField').setAttribute('required');
		
    }
			if (selectElement.value == "approved") {
				document.getElementById('contentField').removeAttribute('required');
        document.getElementById('amountInput').setAttribute('required');
        document.getElementById('imageField').setAttribute('required');
    }
}
function closeEditModal() {
        var modal = document.getElementById("customEditModal");
        modal.style.display = "none";
    }
    function openEditModal(serviceId,amount) {
        var modal = document.getElementById("customEditModal");
        modal.style.display = "block";
        document.getElementById("serviceId").value =serviceId;
		 var maxAmount=amount;
		const amountInput = document.getElementById('amountInput');
    amountInput.setAttribute('min', '1');
    amountInput.setAttribute('max',maxAmount );
        
    }
    $(document).on("input", ".numeric", function (e) {
		
        this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
        document.getElementById('newdiverror').style.display = "none";
        document.getElementById('verify-submit').disabled = false;
        const min = parseFloat($(this).attr('min'));
        const max = parseFloat($(this).attr('max'));

        if (this.value !== '') {
            let value = parseFloat(this.value);
            if (value < min) {
                this.value = min;
            } else if (value > max) {
                document.getElementById('newdiverror').style.display = "block";
                document.getElementById('newdiverror').innerText = "Withdraw amount can't be greater than $"+max;
                document.getElementById('verify-submit').disabled = true;
            }
        }
    });
</script>
@endpush