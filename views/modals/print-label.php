

	<div class="modal fade" id="printLabelModal" tabindex="-1" role="dialog" aria-labelledby="printLabelModal" aria-hidden="true" style="z-index: 1800;">
	  	<div class="modal-dialog" role="document">
		    <div class="modal-content">

			    <div class="modal-header">
			        <h6 class="modal-title">Print Label</h6>
			        <button type="button" class="close" 
			        		@click="{ errors = []; }"
			        		data-dismiss="modal"
			        		aria-label="Close"> 
			          <span aria-hidden="true">&times;</span>
			        </button>
			    </div>

			    <div class="modal-body">
					
					<h5 v-if="shipmentPin != ''">Shipment PIN: {{ shipmentPin }}</h5>

			    	<div class="alert alert-danger alert-narrow" v-for="error in errors">
						{{ error }}<br/>
					</div>

		        	<div id="reprint-label"></div>

			    </div>

			    <div class="modal-footer">
			        <button type="button" 
			        		@click="{ errors = []; }" 
			        		class="btn btn-secondary" data-dismiss="modal">Close</button>
			    </div>

		    </div>
	  	</div>
	</div>