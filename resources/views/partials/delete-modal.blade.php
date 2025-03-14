<div class="modal fade" id="products1{{$data->id}}" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <!-- <h5 class="modal-title" id="exampleModalLongTitle">Change Status
                    of <strong>{{$data->name}}</strong> </h5> -->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="text-align:center;">
                <form style="display: unset;" action="{{route("{$route}.destroy",$data->id)}}"
                        method="POST" id="form-submit{{$data->id}}">
                    {{ method_field('DELETE') }}
                    {{ csrf_field()}}
                    <!-- <input type="hidden" name="activateOne" value="activateOnlyOne"> -->
                    @csrf
                    {{ "Are you sure you want to Delete {$data->name} ??"}}
                    <br />
                    <br />
                    <input type="submit" class="btn btn-danger" value="Yes" />
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                </form>
            </div>
            <div class="modal-footer">
                <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close
                </button> -->
            </div>
        </div>
    </div>
</div>
