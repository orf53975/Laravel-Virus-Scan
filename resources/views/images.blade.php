<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <style>
        input.file-input {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                @if (count($errors) > 0)
                    <div class="alert alert-dismissible alert-danger">
                        <i class="fa fa-times close alert-hide" data-dismiss="alert" aria-label="Close" aria-hidden="true"></i>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if( session('message') )
                    <div class="alert alert-dismissible alert-success" role="alert">

                        <i class="fa fa-times close alert-hide" data-dismiss="alert" aria-label="Close" aria-hidden="true"></i>
                        @if (is_array(session('message')))
                            @foreach (session('message') as $message)
                                {{ $message }}
                            @endforeach
                        @else
                            {{ session('message') }}
                        @endif
                    </div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2">
                <form class="form-horizontal" action="{{ url('/images') }}" method="POST" enctype="multipart/form-data">
                    <fieldset>
                        <legend>Images</legend>
                        {{ csrf_field() }}
                        <input type="file" name="images[]" class="file-input">
                        <input type="file" name="images[]" class="file-input">
                        <button type="save" class="btn btn-primary">Save</button>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
