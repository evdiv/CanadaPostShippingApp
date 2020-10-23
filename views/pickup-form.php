<div class="tab-pane fade" id="pickup" role="tabpanel" aria-labelledby="pickup-tab">

    <!--
        <div class='alert alert-danger'>
            <h3>--- Development Mode ---</h3>
        </div>
    -->



    <div v-if="errors.length > 0" class='alert alert-danger'>
        <div v-for="(err, index) in errors">
            <span>{{err}}</span>
        </div>
    </div>


    <!-- Scheduled PickUp -->
    <table v-if="pickUps.length > 0" class="table table-hover table-sm" v-cloak>
        
        <thead>
            <tr>
                <th scope="col">PickUp Date / Time</th>
                <th scope="col"></th>               
            </tr>
        </thead>

        <tbody v-for="(pickUp, index) in pickUps" style="line-height: 1.1; font-size: 0.8rem;">
            <tr>
                <td><b>{{ pickUp.PickUpDate }}</b>  &nbsp;&nbsp;({{ pickUp.AnyTimeAfter }} - {{pickUp.UntilTime}})</td>
                <td style='text-align: right;'><button type="button"
                        class="btn btn-danger btn-sm" 
                        @click="cancelSchedulePickUp(pickUp.ConfirmationNumber)" 
                        style="padding: .2rem .5rem; line-height: 1.2; font-size: 0.6rem;">Cancel</button>
                </td>
            </tr>
        </tbody>
    </table>

    <!--/ Scheduled PickUp -->

    <div class="alert alert-primary" role="alert">
	<!-- Pickup Form -->

         <div class="form-group row">
            <label for="pickUpDate" class="col-sm-4 col-form-label">
                Date for PickUp 
            </label>

            <div class="col-sm-8">
                <div class="input-group">
                    <input type="date" 
                        class="form-control form-control-sm"
                        placeholder="Enter Date"
                        v-model="pickUpDate"/>
                </div>
                <small>The date cannot be more than <b>5 days in the future</b></small>
            </div>

        </div>

        <br />

        <div class="form-group row">
            <label for="anyTimeAfter" class="col-sm-4 col-form-label">
                Preferred Time 
            </label>

            <div class="col-sm-8">
                <div class="input-group input-group-sm">
                    <input type="time" 
                            @change="preferredTimeOnChangeHandler" 
                            class="form-control form-control-sm" 
                            step="1800" 
                            id="anyTimeAfter" 
                            placeholder="Any Time After" 
                            value="" 
                            min="12:00"
                            max="16:00"
                            v-model="anyTimeAfter">
       
                </div>
                <small>Must be between noon <b>(12:00)</b> and <b>4 p.m. (16:00)</b></small>  
            </div>
        </div>

        <br/>

        <div class="form-group row">
            <label for="untilTime" class="col-sm-4 col-form-label">
                Until Time
            </label>

            <div class="col-sm-8">
                <div class="input-group input-group-sm">
                    <input type="time" 
                            @change="untilTimeOnChangeHandler" 
                            class="form-control form-control-sm"  
                            step="1800" 
                            id="untilTime" 
                            placeholder="Until Time" 
                            value="" 
                            min="13:00"
                            max="17:00" 
                            v-model="untilTime">
                </div>
                <small>Must be a minimum of one hour after preferred-time</small>  
            </div>
        </div>

        <br/>


        <div class="form-group row">   
            <label for="pickUpTotalPieces" class="col-sm-4 col-form-label">
                Total Pieces
            </label>
            <div class="col-sm-8">
                <div class="input-group input-group-sm">
                    <input type="number" 
                            @change="pickTotalPiecesOnChangeHandler" 
                            class="form-control form-control-sm" 
                            id="pickUpTotalPieces" 
                            placeholder="Total Pieces" 
                            value=""
                            v-model="pickUpTotalPieces"  
                            :min="1"
                            :max="999">
                </div>
                <small>The expected number of items to be picked up.</small>
            </div>
        </div>

        <br/>

        <div class="form-group row">   
            <label for="pickUpLocation" class="col-sm-4 col-form-label">
                PickUp Location
            </label>
            <div class="col-sm-8">
                <div class="input-group input-group-sm">
                    <input type="text" 
                            class="form-control form-control-sm" 
                            id="pickUpLocation" 
                            placeholder="PickUp Location" 
                            value=""
                            v-model="pickUpLocation"  
                            min="0"> 
                </div>

                <small>Instructions for the driver (e.g., use back door, use side entrance, bring a dolly)</small>
            </div>
        </div>

        <br/>

        <div class="text-right" style="margin-top: 24px;">
            <button type="button" class="btn btn-success btn-lg"  v-on:click="schedulePickUp"> Schedule PickUp </button>
        </div>

    </div>


	<!--/ Pickup Form -->

</div>