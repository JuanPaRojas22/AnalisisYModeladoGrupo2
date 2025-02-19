<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


    <title>HOME</title>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="assets/css/zabuto_calendar.css">
    <link rel="stylesheet" type="text/css" href="assets/js/gritter/css/jquery.gritter.css" />
    <link rel="stylesheet" type="text/css" href="assets/lineicons/style.css">    
    
    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">

    <script src="assets/js/chart-master/Chart.js"></script>
    
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

<body>
    <section id="container">
    <header class="header black-bg">
            <div class="sidebar-toggle-box">
                <div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
            </div>
            <a href="index.php" class="logo"><b>Acces Perssonel</b></a>
            <div class="nav notify-row" id="top_menu">
                <!-- Notifications -->
                <ul class="nav top-menu">
                    <!-- Tasks Dropdown -->
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="fa fa-tasks"></i>
                            <span class="badge bg-theme">4</span>
                        </a>
                        <ul class="dropdown-menu extended tasks-bar">
                            <div class="notify-arrow notify-arrow-green"></div>
                            <li>
                                <p class="green">You have 4 pending tasks</p>
                            </li>
                            <!-- Example Task -->
                            <li>
                                <a href="#">
                                    <div class="task-info">
                                        <div class="desc">DashGum Admin Panel</div>
                                        <div class="percent">40%</div>
                                    </div>
                                    <div class="progress progress-striped">
                                        <div class="progress-bar progress-bar-success" style="width: 40%"></div>
                                    </div>
                                </a>
                            </li>
                            <!-- More tasks -->
                        </ul>
                    </li>
                    <!-- Messages Dropdown -->
                    <li id="header_inbox_bar" class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="fa fa-envelope-o"></i>
                            <span class="badge bg-theme">5</span>
                        </a>
                        <ul class="dropdown-menu extended inbox">
                            <div class="notify-arrow notify-arrow-green"></div>
                            <li>
                                <p class="green">You have 5 new messages</p>
                            </li>
                            <!-- Example Message -->
                            <li>
                                <a href="#">
                                    <span class="photo"><img alt="avatar" src="assets/img/ui-zac.jpg"></span>
                                    <span class="subject">
                                        <span class="from">Zac Snider</span>
                                        <span class="time">Just now</span>
                                    </span>
                                    <span class="message">Hi mate, how is everything?</span>
                                </a>
                            </li>
                            <!-- More messages -->
                        </ul>
                    </li>

                    <li id="header_profile_bar" class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="fa fa-user"></i>
                            <span class="badge bg-theme">1</span>
                        </a>
                        <ul class="dropdown-menu extended inbox">
                            <div class="notify-arrow notify-arrow-green"></div>
                            <li>
                                <p class="green">User Profile</p>
                            </li>
                            <li><a href="profile.php"><i class="fa fa-cogs"></i> Edit Profile</a></li>
                            <li><a href="settings.php"><i class="fa fa-cogs"></i> Settings</a></li>
                            <li><a href="login.php"><i class="fa fa-sign-out"></i> Logout</a></li>
                        </ul>
                    </li>

                </ul>
                <div class="top-menu">
                    <?php if (isset($_GET['login']) && $_GET['login'] == 'success' && isset($_GET['username'])): ?>
                        <ul class="nav pull-right top-menu">
                            <li>
                                <h3>Bienvenido, <?php echo htmlspecialchars($username); ?>!</h3>
                            </li>
                        </ul>
                    <?php elseif (isset($_GET['login']) && $_GET['login'] == 'error'): ?>
                        <ul class="nav pull-right top-menu">
                            <li>
                                <h3>Error en el inicio de sesión. Por favor, inténtelo de nuevo.</h3>
                            </li>
                        </ul>
                    <?php endif; ?>
                </div>


        </header>
        <!--header end-->

        <!-- **********************************************************************************************************************************************************
      MAIN SIDEBAR MENU
      *********************************************************************************************************************************************************** -->
        <!--sidebar start-->
        <aside>
            <div id="sidebar" class="nav-collapse ">
                <!-- sidebar menu start-->
                <ul class="sidebar-menu" id="nav-accordion">

                    <div id="sidebar" class="nav-collapse ">
                        <!-- sidebar menu start-->
                        <ul class="sidebar-menu" id="nav-accordion">

                            <p class="centered"><a href="profile.php"><img src="assets/img/ui-sam.jpg"
                                        class="img-circle" width="60"></a></p>
                            <h5 class="centered">Bienvenido, <?php echo $_SESSION['username']; ?>!</h5>

                            <li class="mt">
                                <a class="active" href="index.php">
                                    <i class="fa fa-dashboard"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>

                            <li class="sub-menu">
                                <a href="javascript:;">
                                    <i class="fa fa-desktop"></i>
                                    <span>UI Elements</span>
                                </a>
                                <ul class="sub">
                                    <li><a href="general.html">General</a></li>
                                    <li><a href="buttons.html">Buttons</a></li>
                                    <li><a href="panels.html">Panels</a></li>
                                </ul>
                            </li>


                            <li class="sub-menu">
                                <a href="javascript:;">
                                    <i class="fa fa-cogs"></i>
                                    <span>Components</span>
                                </a>
                                <ul class="sub">
                                    <li><a href="calendar.html">Calendar</a></li>
                                    <li><a href="gallery.html">Gallery</a></li>
                                    <li><a href="todo_list.html">Todo List</a></li>
                                </ul>
                            </li>
                            <li class="sub-menu">
                                <a href="javascript:;">
                                    <i class="bi bi-person-fill-gear"></i>
                                    <span>Administracion</span>
                                </a>
                                <ul class="sub">
                                    <li><a href="VerPlanilla.php"><i
                                                class="bi bi-journal-bookmark"></i><span>Planilla</span></a></li>
                                    <li><a href="MostrarUsuarios.php"><i
                                                class="bi bi-person-lines-fill"></i><span>Usuarios</span></a></li>
                                    <li><a href="vacaciones.php"><i
                                                class="bi bi-sun"></i><span>Vacaciones</span></a></li>
                                </ul>
                            </li>

                            </li>
                            <li class="sub-menu">
                                <a href="javascript:;">
                                    <i class="fa fa-tasks"></i>
                                    <span>Forms</span>
                                </a>
                                <ul class="sub">
                                    <li><a href="form_component.html">Form Components</a></li>
                                </ul>
                            </li>
                            <li class="sub-menu">
                                <a href="javascript:;">
                                    <i class="fa fa-th"></i>
                                    <span>Data Tables</span>
                                </a>
                                <ul class="sub">
                                    <li><a href="basic_table.html">Basic Table</a></li>
                                    <li><a href="responsive_table.html">Responsive Table</a></li>
                                </ul>
                            </li>
                            <li class="sub-menu">
                                <a href="javascript:;">
                                    <i class=" fa fa-bar-chart-o"></i>
                                    <span>Charts</span>
                                </a>
                                <ul class="sub">
                                    <li><a href="morris.html">Morris</a></li>
                                    <li><a href="chartjs.html">Chartjs</a></li>
                                </ul>
                            </li>

                        </ul>
                        <!-- sidebar menu end-->
                    </div>
        </aside>
       
        <!-- Footer -->
        <footer class="site-footer">
            <div class="text-center">2024 - Your Company</div>
        </footer>
    </section>
    <script>
                    function abrirModal() {
                        document.getElementById("opcionesModal").style.display = "flex";
                    }

                    function cerrarModal() {
                        document.getElementById("opcionesModal").style.display = "none";
                    }
                </script>
                <script src="assets/js/jquery.js"></script>
                <script src="assets/js/jquery-1.8.3.min.js"></script>
                <script src="assets/js/bootstrap.min.js"></script>
                <script class="include" type="text/javascript" src="assets/js/jquery.dcjqaccordion.2.7.js"></script>
                <script src="assets/js/jquery.scrollTo.min.js"></script>
                <script src="assets/js/jquery.nicescroll.js" type="text/javascript"></script>
                <script src="assets/js/jquery.sparkline.js"></script>


                <!--common script for all pages-->
                <script src="assets/js/common-scripts.js"></script>

                <script type="text/javascript" src="assets/js/gritter/js/jquery.gritter.js"></script>
                <script type="text/javascript" src="assets/js/gritter-conf.js"></script>

                <!--script for this page-->
                <script src="assets/js/sparkline-chart.js"></script>
                <script src="assets/js/zabuto_calendar.js"></script>

                <script type="text/javascript">
                    $(document).ready(function () {
                        var unique_id = $.gritter.add({
                            // (string | mandatory) the heading of the notification
                            title: 'Welcome to Dashgum!',
                            // (string | mandatory) the text inside the notification
                            text: 'Hover me to enable the Close Button. You can hide the left sidebar clicking on the button next to the logo. Free version for <a href="http://blacktie.co" target="_blank" style="color:#ffd777">BlackTie.co</a>.',
                            // (string | optional) the image to display on the left
                            image: 'assets/img/ui-sam.jpg',
                            // (bool | optional) if you want it to fade out on its own or just sit there
                            sticky: true,
                            // (int | optional) the time you want it to be alive for before fading out
                            time: '',
                            // (string | optional) the class name you want to apply to that specific message
                            class_name: 'my-sticky-class'
                        });

                        return false;
                    });
                </script>

                <script type="application/javascript">
                    $(document).ready(function () {
                        $("#date-popover").popover({ html: true, trigger: "manual" });
                        $("#date-popover").hide();
                        $("#date-popover").click(function (e) {
                            $(this).hide();
                        });

                        $("#my-calendar").zabuto_calendar({
                            action: function () {
                                return myDateFunction(this.id, false);
                            },
                            action_nav: function () {
                                return myNavFunction(this.id);
                            },
                            ajax: {
                                url: "show_data.php?action=1",
                                modal: true
                            },
                            legend: [
                                { type: "text", label: "Special event", badge: "00" },
                                { type: "block", label: "Regular event", }
                            ]
                        });
                    });


                    function myNavFunction(id) {
                        $("#date-popover").hide();
                        var nav = $("#" + id).data("navigation");
                        var to = $("#" + id).data("to");
                        console.log('nav ' + nav + ' to: ' + to.month + '/' + to.year);
                    }
                </script>
</body>
</html>