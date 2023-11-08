# Laravel CDN with AWS S3 static site 
https://www.scratchcode.io/laravel-upload-file-to-aws-s3-bucket-tutorial-example/
https://laravel.com/docs/5.8/filesystem


- Create public S3 bucket + static website + record in Route 53 as alias
- Create IAM user "laravel" with AWS key and secret and without console access
- Create iam policy to access new s3 bucket. Asign the policy to new group and add user "laravel" to the new group

## Creating a new laravel app
composer create-project laravel/laravel aws-laravel

## Install AWS S3 Filesystem Package
composer require --with-all-dependencies league/flysystem-aws-s3-v3 "^1.0"

## Configure AWS S3 Credentials in .env File
- Add ¨laravel" iam user credencials to .env, as required by 

`config\filesystems.php`

    
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false,
        'scheme'  => 'http'
    ],
    

Step (Important): Set http Scheme In S3 Filesystem

If you are running your web app without https protocol then you have to follow this else you will get the aws http error: curl error 60: ssl certificate problem: unable to get local issuer certificate error.
Open the config\filesystems.php and add the 'scheme'  => 'http' in ‘s3’ array like below:


`.env`

    AWS_ACCESS_KEY_ID=AKIA5CK4UB3W...
    AWS_SECRET_ACCESS_KEY=CrRDMHkzwq2RMb....
    AWS_DEFAULT_REGION=us-east-2
    AWS_BUCKET=scratchcode-io-demo-bucket
    AWS_USE_PATH_STYLE_ENDPOINT=false
    AWS_URL=http://url-registered-in-route-53


Notes: After adding credentials in the .env file, don’t forget to run the 
php artisan config:clear


## Create Routes
We need to add two routes in the routes/web.php file to perform the file upload on the AWS S3 bucket using Laravel. First GET route to show file upload form and another route for post method.

`routes/web.php`

    <?php
    
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\FileUploadController;
    
    Route::get('file-upload', [ FileUploadController::class, 'getFileUploadForm' ])->name('get.fileupload');
    Route::post('file-upload', [ FileUploadController::class, 'store' ])->name('store.file');


## Create FileUploadController
Let’s create a FileUploadController and let’s add those two methods that we have added in the routes file getFileUploadForm() and store().
php artisan make:controller FileUploadController

`app/Http/Controllers/FileUploadController.php`

    <?php
    
    namespace App\Http\Controllers;
    
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Storage;
    
    class FileUploadController extends Controller
    {
        public function getFileUploadForm()
        {
            return view('file-upload');
        }
    
        public function store(Request $request)
        {
            $request->validate([
                'file' => 'required|file|mimes:pdf,jpeg,png,jpg,gif,svg|max:2048',
            ]);
    
            $fileName = $request->file->getClientOriginalName();
            $filePath = 'uploads/' . $fileName;
    
            $path = Storage::disk('s3')->put($filePath, file_get_contents($request->file));
            $path = Storage::disk('s3')->url($path);
    
            // Perform the database operation here
    
            return back()
                ->with('success','File has been successfully uploaded.');
        }
    }

Notes: To validate doc, docx you need to create mimes.php in config directory and add the following content.

`config/mimes.php`

    <?php
    
    return [
        'pdf' => array('application/pdf', 'application/zip'),
    ];


## Create Blade/HTML File
At last, we need to create a file-upload.blade.php file in the views folder and in this file, we will add the file upload Form HTML.

`resources/views/file-upload.blade.php`

    <!DOCTYPE html>
    <html>
    <head>
        <title>Laravel Upload File With Amazon S3 Bucket - ScratchCode.io</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container">
            <div class="panel panel-primary">
                <div class="panel-heading">
                <h2>Laravel Upload File With Amazon S3 Bucket- ScratchCode.io</h2>
                </div>
                <div class="panel-body">
                @if ($message = Session::get('success'))
                    <div class="alert alert-success alert-block">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <strong>{{ $message }}</strong>
                    </div>
                @endif
    
                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
    
                <form action="{{ route('store.file') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <input type="file" name="file" class="form-control"/>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-success">Upload File...</button>
                        </div>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </body>
    </html>


## Run the server & upload files
```bash
php artisan serve
```

After running the above command, open your browser and visit the site below URL:

http://localhost:8000/file-upload
