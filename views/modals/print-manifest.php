
	<div class="modal fade" id="manifestModal" tabindex="-1" role="dialog" aria-labelledby="manifestModal" aria-hidden="true" style="z-index: 2000;">
	  	<div class="modal-dialog modal-lg" role="document">
		    <div class="modal-content">

			    <div class="modal-header">
			        <h6 class="modal-title">Get Shipment Manifest</h6>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close"> 
			          <span aria-hidden="true">&times;</span>
			        </button>
			    </div>

			    <div class="modal-body">

				    <div class="alert alert-danger" v-for="error in errors">
						{{ error }}<br/>
						<a href="#" data-dismiss="modal" @click="retrieveManifestForDate">Retrieve Manifest</a>
					</div>

				    <div class="alert alert-danger" v-if="errors.length > 0">
						<b>Last Transmitted Manifest:</b> 
					</div>

			    	<center>
			    		<div v-if="displayLoadManifestSpinner == 1" class="fa fa-spinner fa-spin fa-3x"></div>
			    	</center>

					<span v-if="manifestType !== '' " style="margin-right: 20px;">
	        			<small>Type: <b>{{ manifestType }}</b> </small>
	        		</span>

		        	<span v-if="manifestDescription !== '' " style="margin-right: 20px;">
		        		<small>Description: <b>{{ manifestDescription }}</b> </small>
		        	</span>

	        		<span v-if="manifestStatus !== '' " style="margin-right: 20px;">
	        			<small>Status: <b>{{ manifestStatus }}</b> </small>
	        		</span>

	        		
					<div id="manifest-pdf"></div>

			    </div>

			    <div class="modal-footer">

			        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			    </div>

		    </div>
	  	</div>
	</div>