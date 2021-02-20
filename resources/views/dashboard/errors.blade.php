@if (count($errors) > 0)
    <div class="alert alert-danger">
        <strong>{{__('Whoops!')}}</strong> {{__('Something went wrong!')}}
        <br><br>

        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if($success = session()->get('success'))
    <div class="alert alert-success">
        <p style="margin-bottom: 0;">{{ $success }}</p>
    </div>
@endif
