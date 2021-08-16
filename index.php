<?php
	/************************************
		Rahkaran AND HamoonKP Integration
		Start Date 2020-07-19
	=====================================
		Author:	Abolfazl Ghaffari
		Mail  : agt2020@gmail.com
		Phone : 09128997081
	************************************/
	session_start();

	if ($_SESSION['sg_session_id'] == '')
	{
		header('location: login.php');
		die();
	}

	require_once ('lib/utils.php');
	require_once ('lib/products.php');
    require_once ('lib/customers.php');

	$company_name = 'فروشگاه RT';

	if ($_REQUEST['action'] == "logout")
	{
		session_destroy($_SESSION['sg_session_id']);
		unset($_SESSION['sg_session_id']);
		header('location: login.php');
		die();
	}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="Abolfazl Ghaffari">
    <meta name="description" content="aghaffar.ir">

    <title>تنظیمات راهکاران</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-rtl.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="css/dashboard.css" rel="stylesheet">
    <link href="css/jquery.dataTables.min.css" rel="stylesheet">
  </head>

  <body>
    <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0" >
      <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="index.php" style="text-align: right;direction: rtl;"><?php echo $company_name; ?></a>
      <input class="form-control form-control-dark w-100" type="text" placeholder="جستجو" aria-label="Search">
      <ul class="navbar-nav px-3">
        <li class="nav-item text-nowrap">
          <a class="nav-link" href="index.php?action=logout">خروج</a>
        </li>
      </ul>
    </nav>

    <div class="container-fluid">
      <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar" style="text-align: right;direction: rtl;">
          <div class="sidebar-sticky">
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link active" href="index.php">
                  <span data-feather="home"></span>
                  داشبورد <span class="sr-only">(فعلی)</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="invoices.php">
                  <span data-feather="file"></span>
                  فاکتور ها
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="products.php">
                  <span data-feather="shopping-cart"></span>
                  محصولات
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="customers.php">
                  <span data-feather="users"></span>
                  مشتریان
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#">
                  <span data-feather="bar-chart-2"></span>
                  گزارشات
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="settings.php">
                  <span data-feather="layers"></span>
                  تنظیمات
                </a>
              </li>
            </ul>

            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
              <span>گزارشات ذخیره شده</span>
              <a class="d-flex align-items-center text-muted" href="#">
                <span data-feather="plus-circle"></span>
              </a>
            </h6>
            <ul class="nav flex-column mb-2">
              <li class="nav-item">
                <a class="nav-link" href="#">
                  <span data-feather="file-text"></span>
                  ماه جاری
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#">
                  <span data-feather="file-text"></span>
                  فصل گذشته
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#">
                  <span data-feather="file-text"></span>
                  فروش آخر سال
                </a>
              </li>
            </ul>
          </div>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4" style="direction: rtl;text-align: right;float: left !important;position: absolute;left: 0">
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
            <h1 class="h2">داشبورد</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
              <div class="btn-group mr-2">
                <button class="btn btn-sm btn-outline-secondary">اشتراک</button>
                <button class="btn btn-sm btn-outline-secondary">خروجی</button>
              </div>
              <button class="btn btn-sm btn-outline-secondary dropdown-toggle">
                <span data-feather="calendar"></span>
                هفته جاری
              </button>
            </div>
          </div>

          <!-- 	<canvas class="my-4" id="myChart" width="900" height="380"></canvas> -->
          <div class="row">
            <div class="col-md-4">
              <h4>لیست انبار ها</h4>
                <div class="table-responsive">
                  <table class="table table-striped table-sm" id="inventories">
                    <thead>
                      <tr>
                        <th>شناسه</th>
                        <th>نام</th>
                        <th>کد</th>
                        <th>فعال</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $inventories = Inventories();
                        if (sizeof($inventories))
                        {
                          foreach ($inventories as $key => $value)
                          {
                            echo "<tr>
                                  <td>".$value['id']."</td>
                                  <td>".$value['name']."</td>
                                  <td>".$value['code']."</td>
                                  <td>".$value['is_default']."</td>
                                </tr>";
                          }
                        }
                      ?>
                    </tbody>
                  </table>
                </div>
            </div>
            <div class="col-md-4">
              <h4>واحد های محصول</h4>
                <div class="table-responsive">
                  <table class="table table-striped table-sm" id="products_unit">
                    <thead>
                      <tr>
                        <th>شناسه</th>
                        <th>نام</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $Products_Unit = Products_Unit();
                        if (sizeof($Products_Unit))
                        {
                          foreach ($Products_Unit as $key => $value)
                          {
                            echo "<tr>
                                  <td>".$value['id']."</td>
                                  <td>".$value['name']."</td>
                                </tr>";
                          }
                        }
                      ?>
                    </tbody>
                  </table>
                </div>
            </div>
            <div class="col-md-4">
              
            </div>
          </div>
        </main>
      </div>
    </div>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script>window.jQuery || document.write('<script src="js/jquery-slim.min.js"><\/script>')</script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <!-- Icons -->
    <script src="js/feather.min.js"></script>
    <script>
      feather.replace()
    </script>

    <!-- Graphs -->
    <script src="js/Chart.min.js"></script>
    <!-- DataTable Core JavaScript -->
    <script src="js/jquery.dataTables.min.js"></script>
    <script>
      // var ctx = document.getElementById("myChart");
      // var myChart = new Chart(ctx, {
      //   type: 'line',
      //   data: {
      //     labels: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
      //     datasets: [{
      //       data: [15339, 21345, 18483, 24003, 23489, 24092, 12034],
      //       lineTension: 0,
      //       backgroundColor: 'transparent',
      //       borderColor: '#007bff',
      //       borderWidth: 4,
      //       pointBackgroundColor: '#007bff'
      //     }]
      //   },
      //   options: {
      //     scales: {
      //       yAxes: [{
      //         ticks: {
      //           beginAtZero: false
      //         }
      //       }]
      //     },
      //     legend: {
      //       display: false,
      //     }
      //   }
      // });
    </script>
  </body>
</html>