<!DOCTYPE html>
<html>
	<head>
		<!-- Basic Page Info -->
		<meta charset="utf-8" />
		<title>Belanja.id Admin</title>

		<!-- Site favicon -->
	
		<!-- Mobile Specific Metas -->
		<meta
			name="viewport"
			content="width=device-width, initial-scale=1, maximum-scale=1"
		/>

		<!-- Google Font -->
		<link
			href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
			rel="stylesheet"
		/>
		<!-- CSS -->
		<link rel="stylesheet" type="text/css" href="vendors/styles/core.css" />
		<link
			rel="stylesheet"
			type="text/css"
			href="vendors/styles/icon-font.min.css"
		/>
		<link
			rel="stylesheet"
			type="text/css"
			href="src/plugins/datatables/css/dataTables.bootstrap4.min.css"
		/>
		<link
			rel="stylesheet"
			type="text/css"
			href="src/plugins/datatables/css/responsive.bootstrap4.min.css"
		/>
		<link rel="stylesheet" type="text/css" href="vendors/styles/style.css" />

        

        
	</head>
	<body>
		<div class="mobile-menu-overlay"></div>

		<!--<div class="main-container">-->
			<div class="pd-ltr-20">
				<div class="card-box pd-20 height-100-p mb-30">
					<div class="row align-items-center">
						<div class="col-md-4">
							<img src="vendors/images/banner-img.png" alt="" />
						</div>
						<div class="col-md-8">
							<h4 class="font-20 weight-700 mb-10 text-capitalize">
								HI ! admin <span class="weight-800 font-20 text-danger"> {{ Auth::user()->name }}.. </span>
                               
							</h4>
							<p class="font-18 max-width-600">
								"Welcome to our E-commerce Admin Dashboard, the control center for managing product inventory, customer orders and sales performance. Easily monitor your online business and make smart strategic decisions. Simplify critical tasks and enjoy advanced security features. Make it your e-commerce business is more successful with us."
							</p>
						</div>
					</div>
				</div>
			    	<div class="row">
					<div class="col-xl-3 mb-30">
						<div class="card-box height-100-p widget-style1">
							<div class="d-flex flex-wrap align-items-center">
							
								<div class="widget-data">
                                    <img src="images/user.png" alt="Logo E-commerce" width="50" height="50">
                                    <div class="weight-600 font-20 text-danger">Users</div>
                                    @php
                                    $totalUsers = App\Models\User::count();
                                    @endphp
                                    <div class="h5">{{ $totalUsers }}</div>
                                </div>                                
							</div>
						</div>
					</div>
					<div class="col-xl-3 mb-30">
						<div class="card-box height-100-p widget-style1">
							<div class="d-flex flex-wrap align-items-center">
							
								<div class="widget-data">
                                    <img src="images/product.png" alt="Logo E-commerce" width="50" height="50">
									<div class="weight-600 font-20 text-danger">Product</div>
									@php
                                    $totalProduct = App\Models\Product::count();
                                    @endphp
                                    <div class="h5">{{ $totalProduct }}</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-3 mb-30">
						<div class="card-box height-100-p widget-style1">
							<div class="d-flex flex-wrap align-items-center">
							
								<div class="widget-data">
                                    <img src="images/event.png" alt="Logo E-commerce" width="50" height="50">
									<div class="weight-600 font-20 text-danger">Event</div>
									@php
                                    $totalEvent = App\Models\Event::count();
                                    @endphp
                                    <div class="h5">{{ $totalEvent }}</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-3 mb-30">
						<div class="card-box height-100-p widget-style1">
							<div class="d-flex flex-wrap align-items-center">
							
								<div class="widget-data">
                                    <img src="images/store.png" alt="Logo E-commerce" width="50" height="50">
									<div class="weight-600 font-20 text-danger">Store</div>
									@php
                                    $totalStore = App\Models\Store::count();
                                    @endphp
                                    <div class="h5">{{ $totalStore }}</div>
								</div>
							</div>
						</div>
					</div>
				</div>
                
				<div class="footer-wrap pd-20 mb-20 card-box">
					Belanja.id - Admin
				</div>
			</div>
		<!--</div>-->
		<script src="vendors/scripts/core.js"></script>
		<script src="vendors/scripts/script.min.js"></script>
		<script src="vendors/scripts/process.js"></script>
		<script src="vendors/scripts/layout-settings.js"></script>
		<script src="src/plugins/apexcharts/apexcharts.min.js"></script>
		<script src="src/plugins/datatables/js/jquery.dataTables.min.js"></script>
		<script src="src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
		<script src="src/plugins/datatables/js/dataTables.responsive.min.js"></script>
		<script src="src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>
		<script src="vendors/scripts/dashboard.js"></script>
        
	</body>
</html>
