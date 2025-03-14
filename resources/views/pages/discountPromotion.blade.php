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
</div>
<div class="container" style="padding: 0;">
    <div class="d-flex justify-content-between align-items-center tabs">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="promotions-tab" data-bs-toggle="tab" data-bs-target="#promotions" type="button" role="tab" aria-controls="promotions" aria-selected="true">Promotions</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="discounts-tab" data-bs-toggle="tab" data-bs-target="#discounts" type="button" role="tab" aria-controls="discounts" aria-selected="false">Discounts</button>
            </li>

        </ul>
    </div>
</div>

<!-- Tab Content -->
<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="promotions" role="tabpanel" aria-labelledby="promotions-tab">
        <!-- Your Content -->
        <div class="row">
            <div class="col-md-12">
                <div class="tables" style="padding-bottom:40px;">
                    <!-- Header -->

                    <!-- Header -->
                    <div class="button-calen">
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
                        <a href="add_banner.html" type="button" class="btn form_btn">
                            <i class="fas fa-upload"></i> Upload new banners
                        </a>
                    </div>
                    <!-- Orders Table -->
                    <div class="table-responsive">
                        <table class="table table-borderless custom-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>End Date</th>
                                    <th>Banner Name</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Example Row -->
                                <tr class="table-row">
                                    <td>July 15, 2024</td>
                                    <td>July 15, 2024</td>
                                    <td><b>Top</b></td>
                                    <td>Active</td>
                                    <td><a href="#" class="icon btn-delete"><i class="fas fa-trash"></i></a></td>
                                </tr>
                                <!-- Repeat rows as needed -->
                                <tr class="table-row">
                                    <td>July 15, 2024</td>
                                    <td>July 15, 2024</td>
                                    <td><b>Popup</b></td>
                                    <td>Active</td>
                                    <td><a href="#" class="icon btn-delete"><i class="fas fa-trash"></i></a></td>
                                </tr>
                                <tr class="table-row">
                                    <td>July 15, 2024</td>
                                    <td>July 15, 2024</td>
                                    <td><b>Bottom</b></td>
                                    <td>Active</td>
                                    <td><a href="#" class="icon btn-delete"><i class="fas fa-trash"></i></a></td>
                                </tr>
                                <tr class="table-row">
                                    <td>July 15, 2024</td>
                                    <td>July 15, 2024</td>
                                    <td><b>Popup</b></td>
                                    <td>Active</td>
                                    <td><a href="#" class="icon btn-delete"><i class="fas fa-trash"></i></a></td>
                                </tr>
                                <tr class="table-row">
                                    <td>July 15, 2024</td>
                                    <td>July 15, 2024</td>
                                    <td><b>Bottom</b></td>
                                    <td>Active</td>
                                    <td><a href="#" class="icon btn-delete"><i class="fas fa-trash"></i></a></td>
                                </tr>
                                <tr class="table-row">
                                    <td>July 15, 2024</td>
                                    <td>July 15, 2024</td>
                                    <td><b>Popup</b></td>
                                    <td>Active</td>
                                    <td><a href="#" class="icon btn-delete"><i class="fas fa-trash"></i></a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- discount  -->
    <!-- Discounts Tab Content -->
    <div class="tab-pane fade" id="discounts" role="tabpanel" aria-labelledby="discounts-tab">
        <div class="row">
            <div class="col-md-12">
                <div class="tables" style="padding-bottom:40px;">
                    <!-- Header -->

                    <!-- Header -->
                    <div class="button-calen">
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
                        <a href="add_discount.html" type="button" class="btn form_btn">
                            <i class="fas fa-upload"></i> Upload new discounts
                        </a>
                    </div>
                    <!-- Discounts Table -->
                    <div class="table-responsive">
                        <table class="table table-borderless custom-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>End Date</th>
                                    <th>Discount Name</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Example Row -->
                                <tr class="table-row">
                                    <td>July 15, 2024</td>
                                    <td>July 15, 2024</td>
                                    <td><b>Summer Sale</b></td>
                                    <td>Active</td>
                                    <td><a href="#" class="icon btn-delete"><i class="fas fa-trash"></i></a></td>
                                </tr>
                                <!-- Repeat rows as needed -->
                                <tr class="table-row">
                                    <td>August 1, 2024</td>
                                    <td>August 31, 2024</td>
                                    <td><b>Back to School</b></td>
                                    <td>Active</td>
                                    <td><a href="#" class="icon btn-delete"><i class="fas fa-trash"></i></a></td>
                                </tr>
                                <tr class="table-row">
                                    <td>September 10, 2024</td>
                                    <td>September 30, 2024</td>
                                    <td><b>Fall Discount</b></td>
                                    <td>Active</td>
                                    <td><a href="#" class="icon btn-delete"><i class="fas fa-trash"></i></a></td>
                                </tr>
                                <tr class="table-row">
                                    <td>October 1, 2024</td>
                                    <td>October 31, 2024</td>
                                    <td><b>Halloween Special</b></td>
                                    <td>Active</td>
                                    <td><a href="#" class="icon btn-delete"><i class="fas fa-trash"></i></a></td>
                                </tr>
                                <tr class="table-row">
                                    <td>November 20, 2024</td>
                                    <td>November 30, 2024</td>
                                    <td><b>Black Friday</b></td>
                                    <td>Active</td>
                                    <td><a href="#" class="icon btn-delete"><i class="fas fa-trash"></i></a></td>
                                </tr>
                                <tr class="table-row">
                                    <td>December 1, 2024</td>
                                    <td>December 31, 2024</td>
                                    <td><b>Holiday Discount</b></td>
                                    <td>Active</td>
                                    <td><a href="#" class="icon btn-delete"><i class="fas fa-trash"></i></a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
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
@endpush