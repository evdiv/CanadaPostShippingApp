<div class="tab-pane fade" id="search" role="tabpanel" aria-labelledby="search-tab">

    <div v-if="errors.length > 0" class='alert alert-danger'>
        <div v-for="(err, index) in errors">
            <span>Error: {{err}}</span>
        </div>
    </div>


	<!-- Shipments Table -->
	<table class="table table-hover table-sm" v-cloak v-if="displaySearchResults">
		
		<thead>
		    <tr>
			    <th scope="col">Order ID</th>
			    <th scope="col">Shipped by</th>
			    <th scope="col">Tracking #</th>
			    <th scope="col">Date/Time</th>		    
			</tr>
	  	</thead>

	  	<tbody v-for="(order, index) in orders" style="line-height: 1.1; font-size: 0.8rem;">
		    <tr>
			    <td><a href="#" 
			    	data-toggle="modal" 
			    	data-target="#showSearchShipmentModal" 
			    	@click="searchOrder = Object.assign({}, order)">{{ order.orderId }}</a></td>

			    <td><b>{{order.carrier}}</b></td>
			    <td><a :href="order.trackingUrl" target="_blank">{{order.pin}}</a></td>
				<td>{{order.date}}</td>
			</tr>

		</tbody>
	</table>
	<!-- Shipments Table -->

	<div v-if="displayLoadShipmentSpinner == 1" style="text-align: center; margin: 12px 0;">
		<div class="fa fa-spinner fa-spin fa-2x"></div>
	</div>


	<!-- Search by Order # to find the tracking number. -->
	<div class="row" style="padding: 24px 0 10px 0">
        <div class="col-sm-6">
            <div class="form-group">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Enter Order ID" v-model="orderIDSearch"/>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
			<button type="button" class="btn btn-success btn-md btn-block" @click="searchByOrderID()"><i class="fa fa-search" aria-hidden="true"></i> Search by Order ID </button>
        </div>
    </div>
	<!--/ Search by Order # to find the tracking number -->


	<!-- Search by Tracking Number to find the web order number -->
	<div class="row" style="padding: 10px 0">
        <div class="col-sm-6">
            <div class="form-group">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Enter Tracking Number" v-model="trackingNumberSearch"/>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
			<button type="button" class="btn btn-success btn-md btn-block" @click="searchByTrackingNumber()"><i class="fa fa-search" aria-hidden="true"></i> Search by Tracking Number </button>
        </div>
    </div>
	<!--/ Search by Tracking Number to find the web order number -->


	<!-- Search by Package Reference to find the tracking -->
	<div class="row" style="padding: 10px 0">
        <div class="col-sm-6">
            <div class="form-group">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Enter Package Reference" v-model="packageReferenceSearch"/>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
			<button type="button" class="btn btn-success btn-md btn-block" @click="searchByPackageReference()"><i class="fa fa-search" aria-hidden="true"></i> Search by Package Reference </button>
        </div>
    </div>
	<!--/ Search by Package Reference to find the tracking -->


	<!-- Search by Phone Number to find the tracking -->
	<div class="row" style="padding: 10px 0">
        <div class="col-sm-6">
            <div class="form-group">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Enter Phone Number" v-model="phoneNumberSearch"/>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
			<button type="button" class="btn btn-success btn-md btn-block " @click="searchByPhoneNumber()"><i class="fa fa-search" aria-hidden="true"></i> Search by Phone Number </button>
        </div>
    </div>
	<!--/ Search by Phone Number to find the tracking -->


	<!-- Search by Customer Name to find the tracking -->
	<div class="row" style="padding: 10px 0 24px 0">
        <div class="col-sm-6">
            <div class="form-group">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Enter Customer Name" v-model="customerNameSearch"/>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
			<button type="button" class="btn btn-success btn-md btn-block" @click="searchByCustomerName()"><i class="fa fa-search" aria-hidden="true"></i> Search by Customer Name </button> 
        </div>
    </div>
	<!--/ Search by Customer Name to find the tracking -->
	
</div>