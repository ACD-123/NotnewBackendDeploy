@extends('../layout')



@section('title', 'Dashboard')
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
        <h3 id="Notification_head">Order Details <br><span style="font-size: 17.59px;color: rgba(13, 18, 23, 1);">Order
                ID : {{$orderDetail->orderid}}</span></h3>
    </div>
    <div class="order_details">
        <div class="row">
            <div class="col-md-12">
                <!-- image -->


            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="delivery-status">
                <div class="order_head_txt">
                    <h4>Delivery Status</h4>
                    <p>{{$orderDetail->address}}: Date : {{$orderDetail->created_at}}</b></p>
                </div>

                <div class="status-dropdown">
                    <button class="status-btn">
                        {{$orderDetail['orderDetails'][0]['status']}}
                    </button>
                </div>

            </div>
            <!-- <span class="end-date" style="text-align: end;">Ended at : 24-Jun-2024</span> -->
            <!-- he -->
            <div class="order-items">
                <h4>Order Items</h4>
                @foreach($orderDetail['orderDetails'] as $product)
                                @php
                                    $description = Str::words($product->product->description, 20, '...');
                                @endphp
                                <div class="item">
                                    <img src="{{$product->product->media[0]['name']}}" alt="Product Image" class="product-image">
                                    <div class="item-details">
                                        <p class="item-name">{{ $product->product->name }}</p>

                                        <p class="item-description">
                                            <span class="short-description">{{ $description }}</span>
                                            <span class="full-description"
                                                style="display: none;">{{ $product->product->description }}</span>
                                            <a href="javascript:void(0);" class="read-more">Read More</a>
                                        </p>

                                        <p class="item-price">${{ $product->product->price }}</p>
                                        <p class="item-size">Size: <span>9.5</span> Color: <span>●</span></p>

                                    </div>
                                    <div class="quantity">
                                        <label for="quantity">Quantity : {{$product->quantity}}</label>
                                    </div>
                                </div>
                @endforeach
                
                <div class="price-details">
                    <p class="total-sub">Subtotal (1 item) <span>$ {{$subtotal}}</span></p>
                    <p class="total-sub">Shipping <span>${{$shippingcost}}</span></p>
                    <p class="total-sub">Discount <span>${{$voucherDiscount}}</span></p>
                    <p class="total">Order Total <span>${{$ordertotal}}</span></p>
                </div>
            </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const readMoreButtons = document.querySelectorAll('.read-more');

                    readMoreButtons.forEach(button => {
                        button.addEventListener('click', function () {
                            const parent = button.parentElement;
                            const shortDescription = parent.querySelector('.short-description');
                            const fullDescription = parent.querySelector('.full-description');

                            if (fullDescription.style.display === 'none') {
                                fullDescription.style.display = 'inline';
                                shortDescription.style.display = 'none';
                                button.innerText = 'Read Less';
                            } else {
                                fullDescription.style.display = 'none';
                                shortDescription.style.display = 'inline';
                                button.innerText = 'Read More';
                            }
                        });
                    });
                });
            </script>



            <div class="details-section">
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-header">Customer Detail</div>
                        <div class="detail-card">

                            <div class="detail-body">
                                <img src="{{env('APP_URL') . $orderDetail['buyer']['profile_image'] ?? 'https://notnewbackendv2.testingwebsitelink.com/public/images/logo.png'}} "
                                    alt="">
                                <div>
                                    <div class="detail-item">{{$orderDetail['buyer']['name']}}</div>
                                    <!-- <div class="detail-item text-muted">#w34008</div> -->
                                    <div class="detail-item"><i class="bi bi-envelope"></i>
                                        {{$orderDetail['buyer']['email']}}</div>
                                    <div class="detail-item"><i class="bi bi-geo-alt"></i>
                                        {{$orderDetail['buyer']['address']}}</div>
                                    <!-- <a href="#" class="chat-link"><i class="bi bi-chat-dots"></i> Chat with Mathew</a> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    @foreach($orderDetail['orderDetails'] as $vendor)
                        <div class="col-md-6">
                            <div class="detail-header">Seller Detail</div>
                            <div class="detail-card">

                                <div class="detail-body">
                                    <img src="{{env('APP_URL') . $vendor->store->cover_image ?? 'https://notnewbackendv2.testingwebsitelink.com/public/images/logo.png'}}"
                                        alt="Profile Picture">
                                    <div>
                                        <div class="detail-item">{{$vendor->store->fullname}}</div>
                                        <!-- <div class="detail-item text-muted">#w34008</div> -->
                                        <div class="detail-item">
                                            <i class="bi bi-envelope fs-6"></i> {{$vendor->store->email}}
                                        </div>
                                        <div class="detail-item">
                                            <i class="bi bi-geo-alt fs-6"></i> {{$vendor->store->address}}
                                        </div>
                                        <!-- <a href="#" class="chat-link">
                                            <i class="bi bi-chat-dots fs-6"></i> Chat with Mathew
                                        </a> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('js')
    <script>
        $(document).ready(function () {
            $('#example').DataTable({
                "pagingType": "full_numbers",
                "lengthMenu": [10, 25, 50, 75, 100],
                "pageLength": 10
            });
        });
    </script>
@endpush