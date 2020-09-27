<div class="container-fluid">
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		<div class="container">

			<a class="navbar-brand" href="//<?= APP_URL ?>" target="_blank">
				<?php if(isAxeAccount()): ?>
					<img src="//<?= APP_URL ?>/images/logo_axe.png" height="40px">
				<?php else: ?>
					<img src="//<?= APP_URL ?>/images/logo.png" height="28px">
				<?php endif;?>
			</a>

			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			    <span class="navbar-toggler-icon"></span></button>

			  <div class="collapse navbar-collapse" id="navbarSupportedContent">

			    <ul class="nav navbar-nav mr-auto">
				    <li class="nav-item active">
				        <a class="nav-link" href="https://www.canadapost.ca/cpotools/apps/far/business/findARate?execution=e2s1" target="_blank">
							<img src="//<?= APP_URL ?>/images/logo-canada-post-sm.jpg" height="28px">
				        </a> 
				    </li>

				    <li class="nav-item dropdown">
				        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style='color: #fff;'>
				          Shipper Selection
				        </a>
				        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
				          <a class="dropdown-item" href="https://manager.long-mcquade.com/purolator/">Purolator</a>
				          <a class="dropdown-item" href="https://manager.long-mcquade.com/canpar/">Canpar</a>
				          <a class="dropdown-item" href="https://manager.long-mcquade.com/localtruck/"><i class="fa fa-bus"></i> &nbsp;Local Truck</a>
				          <a class="dropdown-item" href="https://manager.long-mcquade.com/post/"><i class="fa fa-envelope-o"></i> &nbsp;Post</a>
				          <a class="dropdown-item" href="https://manager.long-mcquade.com/metroland/"><i class="fa fa-bus"></i> &nbsp;Metroland</a>

				        </div>
				    </li>

			    </ul>


				<div class="my-2 my-lg-0">

					<button type="button" class="btn btn-warning btn-sm" 
						v-on:click="location.href='/canada_post/'">New</button>
					
					
					<button type="button" class="btn btn-info btn-sm" 
						v-on:click="displayRates" 
						data-toggle="modal" data-target="#ratesModal">Get Rates</button>


					<button type="button" class="btn btn-info btn-sm"  
							data-toggle="modal" 
							data-target="#createShipmentModal"  
					    	@click="createShipment">Create Shipment</button>

					<button type="button" class="btn btn-info btn-sm" 
						data-toggle="modal" 
						data-target="#createReturnShipmentModal" 
						v-on:click="setReturnShipmentState" 
				        href="#">Create Return Shipment</button>


					<button type="button" class="btn btn-secondary btn-sm" 
						data-toggle="modal" 
						data-target="#manifestModal"  
						v-on:click="getManifest"
				        href="#">End of the Day</button>


				    <a href="#" data-toggle="modal" 
								data-target="#helpModal" style="color: #dbdee0;"><i class="fa fa-question"></i></a>	    

				</div>

	  		</div>
		</div>
	</nav>
</div>