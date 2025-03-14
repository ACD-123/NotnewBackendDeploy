@component('mail::message')
    <div>
    <table class="table">
            <br>
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Address</th>
                <th scope="col">Number</th>
                <th scope="col">Store</th>
                <th scope="col">Facebook Profile</th>
                <th scope="col">Instagram Profile</th>
                <th scope="col">EIN</th>
                <th scope="col">SSN</th>
                <th scope="col">Business Type</th>
                <th scope="col">Website</th>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{$user->name}}</td>
                    <td>{{$user->email}}</td>
                    <td>{{$user->number}}</td>
                    <td>{{$user->store}}</td>
                    <td>{{$user->facebook}}</td>
                    <td>{{$user->instagram}}</td>
                    <td>{{$user->ein}}</td>
                    <td>{{$user->ssn}}</td>
                    <td>{{$user->businessType}}</td>
                    <td>{{$user->website}}</td>
                </tr>   
            </tbody>
    </table>
    </div>
@endcomponent
