<div class="tab-pane fade" id="admin" role="tabpanel" aria-labelledby="admin-tab">

    <div class="row justify-content-end" style="padding: 24px 0">
		<div class="col-sm-3"><small> Retrieve manifests since a date: </small></div>

        <div class="col-sm-5">
            <div class="form-group">
                <input type="date" class="form-control" v-model="retriveManifestForDate" placeholder="Enter Date"/> 
            </div>
        </div>

        <div class="col-sm-4">
			<button type="button" class="btn btn-success" @click="getManifestForDate()"><i class="fa fa-download"></i>&nbsp;&nbsp; Get Manifest </button>
        </div>


        <div class="col-sm-12">
            <table v-if="manifests" class="table table-hover table-sm" v-cloak style="margin-top: 16px;">
                    
                    <tbody v-for="(manifest, index) in manifests" style="line-height: 1.1; font-size: 0.8rem;">
                        <tr>
                            <td></td>
                            <td>{{ manifest }}</td>
                        </tr>
                    </tbody>
            </table>
        </div>

    </div>

<!--
    <div class="row justify-content-end" style="padding: 24px 0">
        <div class="col-sm-8"><small> Get the full list of groups eligible for use in a Transmit Shipments request. </small></div>

        <div class="col-sm-4">
            <button type="button" class="btn btn-success" @click="getGroups()"><i class="fa fa-download"></i>&nbsp;&nbsp; Get Groups </button>
        </div>
    </div>
-->


    <div class="row justify-content-end" style="padding: 24px 0">
        <div class="col-sm-3"><small> Print manifest: </small></div>

        <div class="col-sm-5">
            <div class="form-group">
                <input type="text" class="form-control" v-model="printManifestId"  placeholder="Manifest Id"/>
            </div>
        </div>

        <div class="col-sm-4">
            <button type="button" class="btn btn-success" 
                data-toggle="modal" 
                data-target="#manifestModal"  
                @click="printManifest()"><i class="fa fa-print"></i>&nbsp;&nbsp; Print Manifest </button>
        </div>
    </div>    

</div>