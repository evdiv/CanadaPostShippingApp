
<div class="modal fade" id="pickupConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="pickupConfirmationModal" aria-hidden="true" style="z-index: 1400;">
  	<div class="modal-dialog" role="document">
	    <div class="modal-content">

		    <div class="modal-header">
		        <h6 class="modal-title">Request Confirmation</h6>

		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"> 
		          <span aria-hidden="true">&times;</span>
		        </button>
		    </div>


		    <div class="modal-body">
			    <div v-if="errors.length > 0" class='alert alert-danger'>
			        <div v-for="(err, index) in errors">
			            <span>{{err}}</span>
			        </div>
			    </div>

		    	<div v-else class="alert alert-success">
		    		Pickup request has been transmitted to Canada Post
		    	</div>
		    </div>


	    </div>
  	</div>
</div>