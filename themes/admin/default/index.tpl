<!DOCTYPE html>
<html>
	<head>
	  	<meta name="keywords" content="home page" lang="cs">
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	    <meta http-equiv="Content-Script-Type" content="text/javascript">
	    <meta http-equiv="Content-Language" content="cz">
	    <meta name="description" content="Admin">
	    <meta name="robots" content="noindex, nofollow">
	    <meta name="author" content="<% author %>">

	    <meta name="revisit-after" content="7 days">
	    <meta name="copyright" content="(c)2013 design & code by Karel Juřička">
	   
      <script src="<% web_path %>plugin/ckeditor/ckeditor.js"></script>
      <script src="<% web_path %>themes/globals/libs/jquery-ui/js/jquery-1.9.1.js"></script>
      <script src="<% web_path %>themes/globals/libs/jquery-ui/js/jquery-ui-1.10.3.custom.min.js"></script>
  
      <script src="<% absolute_path %>js/myscripts.js"/></script>
      <link rel="stylesheet" href="<% web_path %>themes/globals/libs/jquery-ui/css/ui-lightness/jquery-ui-1.10.3.custom.min.css" type="text/css" media="screen"/>
      <link rel="stylesheet" href="<% web_path %>themes/globals/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" type="text/css" media="screen"/>
	    <link rel="stylesheet" href="<% absolute_path %>styles/styles.css" type="text/css" media="screen"/>
			<link rel="stylesheet" href="<% absolute_path %>styles/print.css" type="text/css" media="print"/> 


	    <title>Admin</title>
  	</head>
  	<body>
  		<header>
  			<nav class="main-nav">
  				<% menu %>
  			</nav>
  			<div class="title">
  				<a href="<% admin_url %>" title="admin">
          <h1>Administrace <span>LoveCMS</span></h1>
  				<span class="version">v 1.2</span>
          </a>
  			</div>
  			<div class="def-footer"></div>
  		</header>
  		<div class="horizontal-line"></div>
      <div class="admin-status">
        <% admin_user_status %>
      </div>
  		<div class="main">
  			<% content %>
  		</div>
       <script src="<% web_path %>themes/globals/libs/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

  	</body>
</html>
