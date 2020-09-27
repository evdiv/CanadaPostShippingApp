

	<div class="modal fade" id="createReturnShipmentModal" tabindex="-1" role="dialog" aria-labelledby="createReturnShipmentModal" aria-hidden="true">
	  	<div class="modal-dialog" role="document">
		    <div class="modal-content">

			    <div class="modal-header">
			        <h5 class="modal-title">Return Shipment <small>(check details).</small></h5>

			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          <span aria-hidden="true">&times;</span>
			        </button>
			    </div>

			    <div class="modal-body">

					<div class="alert alert-danger alert-narrow" v-for="error in errors">
						{{ error }}<br/>
					</div>

					<div v-for="(pin, index) in pins">
						<div class="shipment-pin">
							<h6>Shipment PIN: {{ pin }}</h6> 
						</div>
					</div>


					<div v-if="pins.length > 0">

						<button type="button" class="btn btn-outline-info btn-sm btn-show-pdf" 
									onclick="$('#return-shipment-labels').toggle();">Show / Hide label</button>

						<div id="return-shipment-labels" class="pdf-label">
							<center><div class="fa fa-spinner fa-spin fa-3x"></div></center>
						</div>
					</div>


					<!-- Email to customer with Shipment Label -->
					<div v-if="pins.length > 0" style="margin-top: 50px;">

						<div v-if="emailToCustomerSent === 0" role="alert" class="alert alert-primary">

						  	<!-- Default panel contents -->
						  	<h6>Send Shipping Labels to Customer</h6>
							  
							<div class="form-group row" style="margin-bottom: 8px;">
								<div class="col-sm-12">
									<div class="input-group input-group-sm">
										<input type="text" placeholder="Customer Email" v-model="receiverEmail" class="form-control form-control-sm" />
									</div>
								</div>
							</div>


							<div class="form-group row" style="margin-bottom: 12px;">
								<div class="col-sm-12">
									<div class="input-group input-group-sm">
										<textarea class="form-control" v-model="receiverEmailBody" rows="7"></textarea>
									</div> 
								</div>
							</div>
							
						
							<small><i class="fa fa-file"></i> &nbsp;&nbsp;Shipment Label is attached</small>

							<div class="text-right">
								<button 
									type="button" 
									class="btn btn-success btn-sm"
									v-on:click="sendEmailToCustomer">
									<i aria-hidden="true" class="fa fa-envelope"></i> Send Label to Customer
								</button>
							</div>

						</div>


						<div v-else-if="emailToCustomerSent === 1" role="alert" class="alert alert-success">
							<h6>Email to Customer has been sent successfully.</h6>
						</div>

						<div v-else role="alert" class="alert alert-danger">
							<h6>Error: We are not able to send Email.</h6> 
						</div>

					</div>


					<!-- Return Shipment Details--> 
					<div v-if="pins.length === 0">

						<h6>FROM: </h6> 

						<small>
							<div v-bind:class="{'text-danger': isEmpty(receiverName)}">Sender Name: <b>{{ receiverName }}</b></div>
							<div v-bind:class="{'text-danger': isEmpty(receiverStreetName)}">Sender Address: <b>{{ receiverStreetNumber }} {{ receiverStreetName }}</b></div>

							<div v-if="receiverAddress2">Sender Address 2: <b>{{ receiverAddress2 }} </b></div>
							<div v-if="receiverAddress3">Sender Address 3: <b>{{ receiverAddress3 }} </b></div>

							<div v-bind:class="{'text-danger': isEmpty(receiverCity)}">Sender City: <b>{{ receiverCity }}</b></div>
							<div v-bind:class="{'text-danger': isEmpty(receiverProvince)}">Sender Province Code: <b>{{ receiverProvince }}</b></div>
							<div v-bind:class="{'text-danger': isEmpty(receiverPostalCode)}">Sender Postal Code: <b>{{ receiverPostalCode }}</b></div>

							<div v-bind:class="{'text-danger': isEmpty(receiverPhone)}">
								Sender Phone <b>{{ receiverPhoneAreaCode }}-{{ receiverPhone }}
								<span v-if="receiverPhoneExtension">ext.{{ receiverPhoneExtension }}</span></b>
							</div>

							<div v-if="receiverFaxNumber">Sender Fax <b>{{ receiverFaxNumber }}</b></div>
							<div v-if="receiverEmail">Email <b>{{ receiverEmail }}</b></div>
						</small>


						<h6 style="margin-top: 18px;">TO Store: </h6>


						<div class="row" v-if="locations.length > 0" style="margin-bottom: 14px;">
						   	<div class="col-sm-12">
							    <select class="form-control form-control-sm" v-model="senderId" @change="getLocationsForReturnLabel">
							      	<option v-for="location in locations" :value="location.Id">{{ location.city }}</option>
							    </select>
							</div>
					  	</div>


						<small>
							<div >Receiver Name: <b>Long and McQuade</b></div>
							<div v-bind:class="{'text-danger': isEmpty(senderStreetNumber)}">Receiver Address: <b>{{ senderStreetNumber }} {{ senderStreetName }}</b></div>

							<div v-bind:class="{'text-danger': isEmpty(senderCity)}">Receiver City: <b>{{ senderCity }}</b></div>
							<div v-bind:class="{'text-danger': isEmpty(senderProvince)}">Receiver Province Code: <b>{{ senderProvince }}</b></div>
							<div v-bind:class="{'text-danger': isEmpty(senderPostalCode)}">Receiver Postal Code: <b>{{ senderPostalCode }}</b></div>

							<div v-bind:class="{'text-danger': isEmpty(senderPhone)}">
								Receiver Phone <b>{{ senderPhoneAreaCode }}-{{ senderPhone }}</b>
							</div>
						</small>


						<h6 style="margin-top: 18px;">Shipment Details</h6>

						<small>
							<div v-bind:class="{'text-danger': isEmpty(orderId)}">OrderID (Reference): <b>{{ orderId }}</b></div>
							<!-- 
								<div v-bind:class="{'text-danger': isEmpty(getTotalPieces())}">Total Pieces: <b>{{ getTotalPieces() }}</b></div>
								<div v-bind:class="{'text-danger': isEmpty(getTotalWeight())}">Total Weight: <b>{{ getTotalWeight() }}kg.</b></div>
								<div v-bind:class="{'text-danger': isEmpty(selectedService)}">Service: <b>{{ selectedService }}</b></div>
							-->
						</small>

					</div>
					<!--/ Return Shipment Details --> 

					<center>
						<div v-if="displayLoadShipmentSpinner == 1" class="fa fa-spinner fa-spin fa-3x"></div>
	    			</center>


			    </div>

			    <div class="modal-footer">
			    	<button type="button" class="btn btn-success btn-lg" v-on:click="createReturnShipment" v-if="pins.length === 0">Create Return Shipment</button>
			    </div>

		    </div>
	  	</div>
	</div>