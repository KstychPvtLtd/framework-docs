 <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AwesomeBlogs</title>

    <!-- Here we will link css file-->
    <link rel="stylesheet" href="{{ url('custom/blogpost/css/main.css') }}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

	  <style>
	   body{font-size:25px;}
	   h3{font-size:40px;}
	  </style>
  </head>
  <body>
      <div id="header">
      <div class="container" style="max-width:90%">
        <a href="/blogpost" style="font-size:35px;padding:0px;float:left;">Awesome Blogs</a>
        <ul id="header-nav">
         <li><a href="/blogpost/create">CreatePost</a></li>
        </ul>
      </div>
    </div>

    @yield('content')

<!-- Footer -->
<footer class="page-footer font-small" style="background-color:#1ABC9C;">

  <!-- Copyright -->
  <div class="text-center py-3">
    <a href="/localhost/blogpost" style="color:white"> AwesomeBlogs - Kstych</a>
  </div>
  <!-- Copyright -->

</footer>
<!-- Footer -->


</body>
</html>

