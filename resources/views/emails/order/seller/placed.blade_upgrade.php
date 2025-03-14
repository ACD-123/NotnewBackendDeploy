@component('mail::message')
<div>
<div class="container">
            <h1>Sold Item Details</h1>
            <div class="product">
                <div class="product-image">
                    <img src="img/cars.jpeg" width="100" height="100" />
                </div>
                <div class="product-details">
                    <div class="product-details-heading">
                        Nike grippers
                    </div>
                    <div class="product-details-description">
                        this is description
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <hr />
            <div class="invoice-td">
                <div class="invoice-td-left">
                    Invoice Number
                </div>
                <div class="invoice-td-right">
                    #fafaf
                </div>
                <div class="clear"></div>
            </div>
             <div class="invoice-td">
                <div class="invoice-td-left">
                    Invoice Date
                </div>
                <div class="invoice-td-right">
                    01/01/2022
                </div>
                <div class="clear"></div>
            </div>
             <div class="invoice-td">
                <div class="invoice-td-left">
                    Shipped from 
                </div>
                <div class="invoice-td-right">
                    state m
                </div>
                <div class="clear"></div>
            </div>
            <div class="invoice-td">
                <div class="invoice-td-left">
                    Shipped to 
                </div>
                <div class="invoice-td-right">
                    state m
                </div>
                <div class="clear"></div>
            </div>
            <hr />
            <div class="invoice-td">
                <div class="invoice-td-left">
                    <b>Buyer Name</b> 
                </div>
                <div class="invoice-td-right">
                    Chris
                </div>
                <div class="clear"></div>
            </div>
            <div class="invoice-td">
                <div class="invoice-td-left">
                    <b>Payment Recieve Via</b>
                </div>
                <div class="invoice-td-right">
                    Online
                </div>
                <div class="clear"></div>
            </div>
            <hr />
            <div class="invoice-td">
                <div class="invoice-td-left">
                    Item Price
                </div>
                <div class="invoice-td-right">
                    $200.00
                </div>
                <div class="clear"></div>
            </div>
            <div class="invoice-td">
                <div class="invoice-td-left">
                    Shipping
                </div>
                <div class="invoice-td-right">
                    $7.99.00
                </div>
                <div class="clear"></div>
            </div>
            <div class="invoice-td">
                <div class="invoice-td-left">
                    Sales Tax (estimated)
                </div>
                <div class="invoice-td-right">
                    $7.99.00
                </div>
                <div class="clear"></div>
            </div>
            <div class="invoice-td">
                <div class="invoice-td-left">
                    <b>You Recieved</b>
                </div>
                <div class="invoice-td-right">
                    $207.99
                </div>
                <div class="clear"></div>
            </div>
            <hr />
            <div class="invoice-td">
                <div class="invoice-td-left">
                    <b>Status</b>
                </div>
                <div class="invoice-td-right">
                    Pending Shipment
                    <br />
                    Track our Item?
                </div>
                <div class="clear"></div>
            </div>
            <img src="img/sold_image.png" class="sold-image" width="300" height="300" />
            <div class="clear"></div>
        </div>
</div>
<style>
            .container{
                width:100%;
                height: auto;
                padding:10px;
            }
            .product{
                width:100%;
                height: auto;         
            }
            .product-image{
                width:19%;
                height: auto;
                float: left;
            }
            .product-details{
                width:80%;
                height: auto;
                float: right;
            }
            .product-details-heading{
                width:100%;
                height: auto;
            }
            .product-details-description{
                width:100%;
                height: auto;
            }
            .clear{
                clear: both;
            }
            .sold-image{
                display: block;
                left: 38%;
                right: 50%;
                position: absolute;
                z-index: 10000;
                top: 45%;
            }
            .invoice-td{
                width:100%;
                height: auto;
                margin-top: 15px;
                margin-bottom: 15px;
            }
            .invoice-td-left{
                width:49%;
                height: auto;
                float:left;
                text-align: left;
            }
            .invoice-td-right{
               width:50%;
               height: auto;
               float:right;
               text-align: right;
            }
        </style>
@endcomponent