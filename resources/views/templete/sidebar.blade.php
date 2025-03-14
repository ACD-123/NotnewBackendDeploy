
            <!-- side bar -->
            <div class="side-bar">
                <h2>Hello Admin!</h2>
                
                <nav>
                    <ul>
                        <li><a href="/home">Dashboard</a></li>
                        <li><a href="/order-management">Order Management</a></li>
                        <li><a href="/user-management">User Management</a></li>
                        <li><a href="/bids-offer">Bids &amp; Offers</a></li>
                        <!-- <li><a href="/order-detail">Chats</a></li> -->
                        <li><a href="/category-table">Category</a></li>
                        <li><a href="/arrtibute-table">Attribute</a></li>
                        <li><a href="/product-table">Product</a></li>
                        <li><a href="/brand-table">Brand</a></li>
                        <li><a href="/banner-table">Banners</a></li>
                        <li><a href="/featured-banner-table">Featured Banners</a></li>
                        <!-- <li><a href="/discount-promotion">Discount &amp; Promotions</a></li> -->
                        <li><a href="/report-anaylytic">Report and Analytic</a></li>
                        <li><a href="/push-notification">Push Notifications</a></li>
                        <li><a href="/report-admin">Report</a></li>
                        <li><a href="/help-support">Help & Support</a></li>
                        <li><a href="/seller-withdraw">Seller Withdraw</a></li>
                        <li><a href="/setting-page">Settings</a></li>
                    </ul>
                    <hr>
                    <form action="{{route('logout')}}" method="POST">
                        @csrf
                    <div class="icon-log">
                        <img src="https://notnewbackendv2.testingwebsitelink.com/public/images/Logout.png" alt="">
                        <button  type="submit" style="border: none; background: none; cursor: pointer; color: black;">Logout</button>
                    </div>
                    </form>

                </nav>
            </div>
    
<!-- side bar end -->