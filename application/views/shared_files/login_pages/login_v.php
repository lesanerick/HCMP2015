<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<title>HCMP | Login</title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<link rel="icon" href="<?php echo base_url().'assets/img/coat_of_arms.png'?>" type="image/x-icon" />
    <link href="<?php echo base_url().'assets/css/style.css'?>" type="text/css" rel="stylesheet"/> 
    <link href="<?php echo base_url().'assets/css/select2.css'?>" type="text/css" rel="stylesheet"/> 
  <link href="<?php echo base_url().'assets/css/jquery-ui-1.10.4.custom.min.css'?>" type="text/css" rel="stylesheet"/>
  <link href="<?php echo base_url().'assets/boot-strap3/css/bootstrap.min.css'?>" type="text/css" rel="stylesheet"/>
  <link href="<?php echo base_url().'assets/boot-strap3/css/bootstrap-responsive.css'?>" type="text/css" rel="stylesheet"/>
  <link href="<?php echo base_url().'assets/css/font-awesome.min.css'?>" type="text/css" rel="stylesheet"/>
  <link rel="stylesheet" href="<?php echo base_url().'assets/css/pace-theme-flash.css'?>" />
  <link rel="stylesheet" href="<?php echo base_url().'assets/bower_components/sweetalert/lib/sweet-alert.css'?>" />
  <link rel="stylesheet" href="<?php echo base_url().'assets/bower_components/alertifyjs/dist/css/alertify_bootstrap_3.css'?>" />
    <script src="<?php echo base_url().'assets/scripts/jquery.js'?>" type="text/javascript"></script>
  <script src="<?php echo base_url().'assets/scripts/pace.js'?>" type="text/javascript"></script>
     <script src="<?php echo base_url().'assets/scripts/select2.js'?>" type="text/javascript"></script>
  <script src="<?php echo base_url().'assets/bower_components/sweetalert/lib/sweet-alert.js'?>" type="text/javascript"></script>
  <script src="<?php echo base_url().'assets/bower_components/alertifyjs/dist/js/alertify.js'?>" type="text/javascript"></script>
		<!--<script src="<?php echo base_url().'assets/scripts/alert.js'?>" type="text/javascript"></script>
		<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		<style>
			h2 {
				font-size: 1.6em;
			}
			button {
				border-radius: 0 !important;
			}
			.green{
			    color:green;
			  }
		  .no-margin{
		    margin:0;
		  }
		  .small-margin{
		    margin:2px;
		  }
		  .online-status{
		    font-size: 15px;
		  }
		  .red{
		    color:#F22613;
		  }
		</style>

		<!-- STYLING FOR NEW LOGIN -->
			<style>
			.login {
			  width: 100%;
			  margin: 16px auto;
			  font-size: 16px;
			}

			/* Reset top and bottom margins from certain elements */
			.login-header,
			.login p {
			  margin-top: 0;
			  margin-bottom: 0;
			}

			/* The triangle form is achieved by a CSS hack */
			.login-triangle {
			  width: 0;
			  margin-right: auto;
			  margin-left: auto;
			  border: 12px solid transparent;
			  border-bottom-color: #144d6e;
			}

			.login-header {
			  background: #144d6e;
			  padding: 20px;
			  font-size: 1.4em;
			  font-weight: normal;
			  text-align: center;
			  text-transform: uppercase;
			  color: #fff;
			}

			.login-container {
			  background: #ebebeb;
			  padding: 12px;
			}

			/* Every row inside .login-container is defined with p tags */
			.login p {
			  padding: 12px;
			}

			.login input {
			  box-sizing: border-box;
			  display: block;
			  width: 100%;
			  border-width: 1px;
			  border-style: solid;
			  padding: 16px;
			  outline: 0;
			  font-family: inherit;
			  font-size: 0.95em;
			}

			.login input[type="email"],
			.login input[type="password"] {
			  background: #fff;
			  border-color: #bbb;
			  color: #555;
			}

			/* Text fields' focus effect */
			.login input[type="email"]:focus,
			.login input[type="password"]:focus {
			  border-color: #888;
			}

			.login input[type="submit"] {
			  background: #144d6e;
			  border-color: transparent;
			  color: #fff;
			  cursor: pointer;
			}

			.login input[type="submit"]:hover {
			  background: #144d6e;
			}

			/* Buttons' focus effect */
			.login input[type="submit"]:focus {
			  border-color: #144d6e;
			}
			</style>
						<!-- END OF STYLING FOR NEW LOGIN -->
		<?php 
		$myurl=$this->uri->segment(2);
					      
							if ($myurl == 'change_default') { ?>
								
								<script>
									$(document).ready(function() {
											alertify.set({ delay: 10000 });
											alertify.success("Your account is now secure.Please login to proceed.", null);
		
										});
										
								</script>
							
						 
							
						<?php	}
 						?>
		<script>
			paceOptions = {
				ajax : false, // disabled
				document : true, //
				eventLag : true //

			};
		</script>
		<script type="text/javascript">
			function changeHashOnLoad() {
				window.location.href += "#";
				setTimeout("changeHashAgain()", "50");
			}

			function changeHashAgain() {
				window.location.href += "1";
			}

			var storedHash = window.location.hash;
			window.setInterval(function() {
				if (window.location.hash != storedHash) {
					window.location.hash = storedHash;
				}
			}, 50);
		</script>
	</head>
	<body data-spy="scroll" data-target=".subnav" data-offset="50" screen_capture_injected="true" style="padding: 0;">
		<div class="navbar  navbar-static-top col-md-12" id="top-panel">
			<div class="col-md-12">
			<a href="<?php echo base_url(); ?>"> 
				<img style="max-width: 9em;margin:auto;" src="<?php echo base_url(); ?>assets/img/coat_of_arms.png" class="img-responsive " alt="Responsive image"></a>
			</div>
			<div class="col-md-12">
				
			<div class="col-md-12" id="logo_text">
			<center>
				<span style="font-size: 1.7em;font-weight: bold">Ministry of Health</span>
				<br />
				<span style="font-size: 1.3em">Health Commodities Management Platform</span>
    <!-- <div class="online-status"><span class="red"><b><span class="glyphicon glyphicon-off small-margin"></span>Off</span>line</b></div> -->
    			<div class="online-status"><span class="green"><b><span class="glyphicon glyphicon-off small-margin"></span>On</span>line</b></div>

			</center>
			</div>

			

			</div>
		</div>
		
		<div class="container-fluid col-md-10">

			<div class="row">
				<div class="col-md-4" style="">

				</div>
				<div class="col-md-4" style="">

					<?php
					if ($popup == "errorpopup") {

						echo '<div class="alert alert-danger alert-dismissable" style="text-align:center;"> Error! Wrong Credentials! Try Again.
								<button type="button" class=" close" data-dismiss="alert" aria-hidden="true">×</button>
								', '</div>';
					} elseif ($popup == "passwordchange") {
						echo '<div class="alert alert-success alert-dismissable" style="text-align:center;">Your password ' . $user_email . ' has been changed. Please login.
							<button type="button" class=" close" data-dismiss="alert" aria-hidden="true">×</button>
							', '</div>';
												} elseif ($popup == "activation") {
													echo '<div class="alert alert-success alert-dismissable" style="text-align:center;">Your account has been Activated. Please login.
							<button type="button" class=" close" data-dismiss="alert" aria-hidden="true">×</button>
							', '</div>';
					}
					unset($popup);
					?>
				</div>
				<div class="col-md-4" style="">

				</div>
			</div>

		</div>
		<div class="col-md-2 pull-right">
				<a href="<?php echo base_url('kenya') ?>">
				<button class="btn btn-success col-md-12">
					<span class="glyphicon glyphicon-dashboard"></span>National Dashboard
				</button></a>
		</div>

		<div class="container" style="" id="containerlogin">

			<div class="row">
			<div class="col-md-3">
				 <span>
				 	<h5>Experiencing any challenges?<br />
						Send an email to: hcmphelpdesk@googlegroups.com</h5> 
						<!-- Random comment to allow for commit -->
						</span> 
						<img src="<?php echo base_url('assets/img/healpdesk.jpg')?>" height="140" width="200" />
						

			</div>

			<div class="col-md-7">
					<div class="row col-md-12">
						<!--<div class="col-md-1"></div>-->
						<div class="col-md-12">
						
						<!-- NEW LOGIN -->
						<div class="login">
						  <!-- <div class="login-triangle"></div> -->
						  
						  <h2 class="login-header">Log in</h2>

						  <!-- <form class="login-container"> -->
							<div class="login-container col-md-12">
							<?php echo form_open('user/login_submit'); ?>
							<p><input type="email" name="username" id="username" placeholder="Enter email" required="required"></p>
						    <p><input type="password" name="password" id="password" placeholder="Password" required="required"></p>
						    <p><input type="submit" name="register" id="register" value="Log in"></p>

						    <a class="" style="margin-left: 2%;" href="<?php echo base_url().'user/forgot_password'?>" id="modalbox">Can't access your account ?</a>
							<a class="" style="margin-left: 2%;" href="<?php echo base_url().'user/sms_activate'?>" id="modalbox">Activate my Account?</a>
							<?php echo form_close(); ?>
							</div>

						  <!-- </form> -->
						</div>
						<!-- LIVE A LITTLE -->

							<!-- <div id="contain_login" class="">
								<h2><span style="margin-right: 0.5em;" class="glyphicon glyphicon-lock"></span>Login</h2>
								<?php echo form_open('user/login_submit'); ?>
								<div id="login" >

									<div class="form-group" style="margin-top: 2.3em;">
										<label for="exampleInputEmail1">Email address</label>
										<input type="text" class="form-control input-lg" name="username" id="username" placeholder="Enter email" required="required">
									</div>
									<div class="form-group" style="margin-bottom: 2em;">
										<label for="exampleInputPassword1">Password</label>
										<input type="password" class="form-control input-lg" name="password" id="password" placeholder="Password" required="required">
									</div>

									<input type="submit" class="btn btn-primary " name="register" id="register" value="Log in" style="margin-bottom: 3%;">

									<a class="" style="margin-left: 2%;" href="<?php echo base_url().'user/forgot_password'?>" id="modalbox">Can't access your account ?</a>
									<a class="" style="margin-left: 2%;" href="<?php echo base_url().'user/sms_activate'?>" id="modalbox">Activate my Account?</a>

								</div>

								<?php echo form_close(); ?>
							</div> -->


							<!-- #contain_login -->

						</div>
						<!--<div class="col-md-1"></div>-->

					</div><!-- .row #contain_login -->
				</div>

			</div><!-- .row -->
		</div><!-- .container -->
		<div id="footer">
			<div class="container">
				<p class="text-muted">
					Government of Kenya &copy <?php echo date('Y'); ?>. All Rights Reserved
				</p>
			</div>
		</div>
		<!-- JS and analytics only. -->
		<!-- Bootstrap core JavaScript

		================================================== -->
		<script>
			$(document).ready(function() {

			});

		</script>
