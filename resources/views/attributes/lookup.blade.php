<select id="properties" name="attribute_id" class="form-control">
    <option value="" selected>Please select...</option>
    @foreach( \App\Models\Attribute::getAll()->get() as $property)
        <option value="{{$property->id}}">{{$property->name}}</option>
    @endforeach
</select>
