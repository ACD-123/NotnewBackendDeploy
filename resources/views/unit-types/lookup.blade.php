<select id="unitType" name="unit_type_id" class="form-control">
    <option value="" selected>Please select...</option>
    @foreach( \App\Models\UnitType::getAll()->get() as $unitType)
        <option value="{{$unitType->id}}">{{$unitType->name}}</option>
    @endforeach
</select>