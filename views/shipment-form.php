<div class="tab-pane fade show active" id="shipment" role="tabpanel" aria-labelledby="shipment-tab">

	<div class="alert alert-danger" role="alert">

		<div class="form-group row">
		    <label for="carrier" class="col-sm-4 col-form-label"> 
		    	Carrier
		    </label> 

		    <div class="col-sm-8">
		    	<div class="input-group input-group-sm">
		      		<input type="text" class="form-control form-control-sm" id="carrier" value="Canada Post" readonly>
				</div>
		    </div>
		</div>


		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Available Services  	
		    	<div v-if="displayLoadServicesSpinner == 1" class="fa fa-spinner fa-spin"></div>
		    	<span v-if="displayLoadServicesSpinner == 2">
		    		<span @click="displayRates" 
							data-toggle="modal" 
							data-target="#ratesModal" class="fa fa-exclamation-circle"></span>
		    	</span>
		    </label>

		    <div class="col-sm-8">

		    	<div class="input-group input-group-sm" v-cloak>

			    	<select class="form-control form-control-sm" v-model="selectedService">
				      	<option v-for="service in availableServiceNames">{{ service.service_name }}</option>
				    </select>

			      	<div class="input-group-append">

			      		<button class="btn btn-success" type="button" @click="getAvailableServices">
			      			<i class="fa fa-refresh" aria-hidden="true"></i> Update
			      		</button>

				  	</div>
				</div>

		    </div>

		    <div class="col-sm-4" v-if="courierSelected" v-cloak>
		    	<small> Customer selected:</small>
		    </div>

		    <div class="col-sm-8" v-if="courierSelected" v-cloak>
		    	<small><b>{{ courierSelected }}</b> - <b>{{ courierService }}</b></small>
		    </div>

		</div>	

	</div>


	<div class="alert alert-primary" role="alert">

		<div class="form-group row">
		    <label class="col-sm-4 col-form-label">Available Boxes</label>

		    <div class="col-sm-8">

		    	<div class="input-group input-group-sm" v-cloak>

			    	<select class="form-control form-control-sm" v-model="selectedBoxId" @change="setPackageSizesBySelectedBox">
				      	<option :value="box.id" v-for="box in boxes">{{ box.description }} - weight limit {{ box.weightLimit }}kg.</option>
				    </select>

				</div>

		    </div>
		</div>	

		<hr/>

		<div class="form-group row" style="margin: 0 0 6px;">
			<span>Metric </span>
			<label class="switch">
				<input type="checkbox" class="form-control form-control-sm" value="false" v-model="isImperial">
				<span class="slider round"></span>	
			</label>
			<span> Imperial</span>
		</div>

	  	<div class="form-group row" style="margin-bottom: 6px;">

	  		<div class="col-sm-4" style="font-size: 0.8rem;" v-cloak>{{ lengthInches }} inches</div>
	  		<div class="col-sm-4" style="font-size: 0.8rem;" v-cloak>{{ widthInches }} inches</div>
	  		<div class="col-sm-4" style="font-size: 0.8rem;" v-cloak>{{ heightInches }} inches</div>


		    <div class="col-sm-4">
		    	<div class="input-group input-group-sm">
		      		<input type="number" class="form-control form-control-sm" placeholder="Length" value="" v-model="length" min="0">
			      	<div class="input-group-append">
	    				<span class="input-group-text">cm</span>
				  	</div>
				</div>
		    </div>


		    <div class="col-sm-4">
		    	<div class="input-group input-group-sm">
		      		<input type="number" class="form-control form-control-sm" placeholder="Width" value="" v-model="width"  min="0">
			      	<div class="input-group-append">
	    				<span class="input-group-text">cm</span>
				  	</div>
				</div>
		    </div>


		    <div class="col-sm-4">
		    	<div class="input-group input-group-sm">
		      		<input type="number" class="form-control form-control-sm" placeholder="Height" value="" v-model="height"  min="0">
			      	<div class="input-group-append">
	    				<span class="input-group-text">cm</span>
				  	</div>
				</div>
		    </div>
 
	  	</div>



		<div class="form-group row" style="margin-bottom: 6px;">

			<div class="col-sm-12" style="font-size: 0.8rem;" v-cloak>{{ weightLbs }} lbs</div>


		    <div class="col-sm-12">
		    	<div class="input-group input-group-sm">
		      		<input type="number" class="form-control form-control-sm" id="weight" placeholder="Weight" value="" v-model="weight"  min="0">
			      	<div class="input-group-append">
	    				<span class="input-group-text">kg</span>
				  	</div>
				</div>
		    </div>
		    
		</div>



	  	<div class="form-group row">
		    <label for="packageReference" class="col-sm-4 col-form-label">
		    	Package Reference
		    </label>

		    <div class="col-sm-8">
		    	<div class="input-group input-group-sm">
		      		<input type="text" class="form-control form-control-sm" id="packageReference" placeholder="Package Reference" value="" v-model="reference">
				</div>
		    </div>
	  	</div>



		<div class="form-group row" style="margin-bottom: 12px;">

		    <label for="packageNote" class="col-sm-4 col-form-label">
		    	Package Note
		    </label>

		    <div class="col-sm-8">
		    	<div class="input-group input-group-sm">
					<textarea class="form-control" v-model="note"></textarea>
				</div>
		    </div>

		</div>


		<div class="text-right">
			<button type="button" class="btn btn-success btn-sm" @click="addPackage"> <i class="fa fa-plus" aria-hidden="true"></i> Add Package </button>
		</div>

	</div>

	
	<table class="table table-hover table-sm" v-cloak>
		<thead>
		    <tr>
			    <th scope="col">ID</th>
			    <th scope="col">Weight</th>
			    <th scope="col">Reference</th>
			    <th scope="col">Special Handling</th>
			    <th scope="col">Delete</th>	
			</tr>
	  	</thead>
	  	<tbody v-for="(package, index) in packages">
		    
		    <tr>
			    <td>{{ index + 1 }}</td>
			    <td>{{ package.weight }}<small class="text-muted"> kg.</small></td>
			    <td><small class="text-secondary">{{ package.reference }}</small></td>
			    <td><small class="text-secondary">{{ package.note}}</small></td>	
			    <td><span class="active-link" @click="removePackage(index)"><i class="fa fa-trash text-danger" aria-hidden="true"></i></span></td>		    
		    </tr>
		</tbody>
	</table>

	<div class="text-right" style="margin-top: 24px;" v-cloak>


		<? if($returnShipment): ?>

			<button type="button" class="btn btn-success btn-lg" 
						data-toggle="modal" 
						data-target="#createReturnShipmentModal" 
						@click="resetShipmentDetails" 
		          		href="#"> Create Return Shipment</button>	



		<? else: ?>

			<!-- Uncomment if want to show the Confirmation Modal Window  --> 
 			<!-- 
 			<button type="button" class="btn btn-info btn-sm" 
					data-toggle="modal" 
					data-target="#createShipmentModal"  
					@click="resetShipmentDetails"> Create Shipment </button> 
			-->

			<div v-if='returnShipmentState'>
				<button type="button" class="btn btn-success btn-lg" 
						data-toggle="modal" 
						data-target="#createReturnShipmentModal" 
						@click="resetShipmentDetails" 
		          		href="#"> Create Return Shipment</button>	
			</div>

			<div v-else>
				<button type="button" class="btn btn-success btn-lg" 
					data-toggle="modal" 
					data-target="#createShipmentModal"  
			    	@click="createShipment">Create Shipment</button>
			</div>

		<? endif;?>

	</div>

</div> 