
	    <div class="container" style="margin-bottom: 1px;"> 
	    	<div class="row" style="margin-top: 10px;">

				<!-- Left Tab -->
			    <div class="col">
					<ul class="nav nav-tabs" id="left-tab" role="tablist" v-cloak>
						<li class="nav-item">
						    <a class="nav-link" id="sender-tab" data-toggle="tab" href="#sender" role="tab" aria-controls="sender" 
						    aria-selected="false">{{ senderLocationName }}</a>
						</li>
						<li class="nav-item">
						    <a class="nav-link active" id="receiver-tab" data-toggle="tab" href="#receiver" role="tab" aria-controls="receiver" aria-selected="true">Customer</a>
						</li>
					</ul>

					<div class="tab-content" id="left-tab-content">

						<!-- Sender Form -->
							<?php include('sender-form.php'); ?>					  
					  	<!--/ Sender Form -->
						
					  	<!-- Receiver Form -->
							<?php include('receiver-form.php'); ?>
					  	<!--/ Receiver Form -->

					</div>
			    </div>
			    <!--/ Left Tab -->


			    <div class="col">
					<ul class="nav nav-tabs" id="right-tab" role="tablist">

						<li class="nav-item">
						    <a class="nav-link active" id="shipment-tab" data-toggle="tab" href="#shipment" role="tab" aria-controls="shipment" aria-selected="true">Shipment</a>
						</li>

						<li class="nav-item">
						    <a class="nav-link" id="history-tab" data-toggle="tab" href="#history" role="tab" aria-controls="history" aria-selected="false">History</a>
						</li>

						<li class="nav-item">
						    <a class="nav-link" id="admin-tab" data-toggle="tab" href="#admin" role="tab" aria-controls="admin" aria-selected="false">Manifests</a>
						</li>

						<li class="nav-item">
						    <a class="nav-link" id="pickup-tab" data-toggle="tab" href="#pickup" role="tab" aria-controls="pickup" aria-selected="false">Schedule PickUp</a>
						</li>

						<li class="nav-item">
						    <a class="nav-link" id="search-tab" data-toggle="tab" href="#search" role="tab" aria-controls="search" aria-selected="false">Search</a>
						</li>
					</ul>
 

					<div class="tab-content" id="right-tab-content">

						<!-- Shipment Form -->
							<?php include('shipment-form.php'); ?>
					  	<!-- End Shipment Form -->

						<!-- List of Shipments -->
							<?php include('history.php'); ?>
					  	<!-- End List of Shipments -->

					  	<!-- List of Admin functions --->
							<?php include('admin.php'); ?>
					  	<!-- End list of Admin functions -->

					  	<!-- List of Scheduled PickUps --->
							<?php include('pickup-form.php'); ?>
					  	<!-- End list of Scheduled PickUps -->					  	

					  	<!-- Search Forms -->
							<?php include('search.php'); ?>
					  	<!-- Search Forms -->	

					</div>
			    </div>
  			</div>
  		</div>