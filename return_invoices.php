<?php
	/************************************
		Rahkaran AND HamoonKP Integration
		Start Date 2020-07-24
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
  	require_once ('lib/invoices.php');
	include_once ('lib/PersianCalendar.php');

	$company_name = 'فروشگاه RT';

	if ($_REQUEST['action'] == "logout")
	{
		session_destroy($_SESSION['sg_session_id']);
		unset($_SESSION['sg_session_id']);
		header('location: login.php');
		die();
	}

if(!empty($_REQUEST['duration']) && is_numeric($_REQUEST['duration']))
{
  $reqLimit = $_REQUEST['duration'];
  if($reqLimit < 0 || $_REQUEST['duration'] > 5)
  {
    $reqLimit = 0;
  }
}
else
{
  $reqLimit = 0;
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
                <a class="nav-link" href="index.php">
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
                <a class="nav-link active" href="return_invoices.php">
                  <span data-feather="file"></span>
                  فاکتور های برگشتی
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
            <h1 class="h2">مدیریت فاکتور های برگشتی</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
              <div class="btn-group mr-2">
              	<a href="insert_invoice_manual.php" class="btn btn-sm btn-info btn-sync">انتقال به راهکاران</a>
              </div>
              <button class="btn btn-sm btn-outline-secondary dropdown-toggle">
                <span data-feather="calendar"></span>
                هفته جاری
              </button>
            </div>
          </div>

          <!-- 	<canvas class="my-4" id="myChart" width="900" height="380"></canvas> -->
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="duration">بازه زمانی : </label>
                <select class="form-control" id="duration" onchange="Duration();">
                  <option value="0" <?php echo ($reqLimit == 0) ? 'selected' : '' ; ?>>یک هفته گذشته</option>
                  <option value="1" <?php echo ($reqLimit == 1) ? 'selected' : '' ; ?>>دو هفته گذشته</option>
                  <option value="2" <?php echo ($reqLimit == 2) ? 'selected' : '' ; ?>>یک ماه گذشته</option>
                  <option value="3" <?php echo ($reqLimit == 3) ? 'selected' : '' ; ?>>سه ماه گذشته</option>
                  <option value="4" <?php echo ($reqLimit == 4) ? 'selected' : '' ; ?>>شش ماه گذشته</option>
                  <option value="5" <?php echo ($reqLimit == 5) ? 'selected' : '' ; ?>>یک سال گذشته</option>
                  <option value="6" <?php echo ($reqLimit == 6) ? 'selected' : '' ; ?>>دو سال گذشته</option>
                </select>
              </div>
            </div>
            <div class="col-md-12">
              <h4>وضعیت فاکتور های برگشتی</h4>
                <div class="table-responsive">
                  <table class="table table-striped table-sm" id="invoices_status_table" style="display: none;">
                    <thead>
                      <tr>
                        <th style='direction:rtl;text-align:center;'>شناسه سایت</th>
                        <th style='direction:rtl;text-align:center;'>توضیحات</th>
                        <th style='direction:rtl;text-align:center;'>شناسه راهکاران</th>
                        <th style='direction:rtl;text-align:center;'>شناسه مشتری سایت</th>
                        <th style='direction:rtl;text-align:center;'>آخرین تغییر</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        $Invoice_Status = Return_Invoice_Status($reqLimit);
                        if (sizeof($Invoice_Status))
                        {
                          foreach ($Invoice_Status as $key => $value)
                          {
                            $date = date('Y-m-d H:i', strtotime('+3 hour +30 minutes', strtotime($value['date'])));
                            $date = explode(' ',$date);
                            $time = $date[1];
                            $date = explode('-',$date[0]);
                            $DATE_J = gregorian_to_mds($date[0],$date[1],$date[2]);
										        $shamsi = $DATE_J[0].'/'.$DATE_J[1].'/'.$DATE_J[2].' '.$time;
                            echo "<tr>
                                  <td style='text-align:center;'>".$value['id']."</td>
                                  <td style='direction:rtl;text-align:center;'>".$value['description']."</td>
                                  <td style='text-align:center;'>".$value['sg_id']."</td>
                                  <td style='text-align:center;'>".$value['customer_id']."</td>
                                  <td style='direction:ltr;text-align:center;'>".$shamsi."</td>
                                </tr>";
                          }
                        }
                      ?>
                    </tbody>
                  </table>
                </div>
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
    <script type="text/javascript">
      function Duration()
      {
        location.replace("invoices.php?duration="+$('#duration').val());
      }
      $(document).ready(function(){
        $('#invoices_status_table').show();
        var table = $('#invoices_status_table').DataTable({
            "info": false,
            "lengthMenu": [ 10, 20, 40],
            "pagingType": "full_numbers",
            "scroller":    true,
            "aaSorting": [[0,'desc'],],
            "language": {
                "emptyTable":     "هیچ فاکتوری برای نمایش وجود ندارد",
                "loadingRecords": "در حال خواند اطلاعات",
                "processing":     "در حال پردازش",
                "search":         "جستجو ",
                "zeroRecords":    "موردی یافت نشد !",
                "lengthMenu":     "نمایش _MENU_ سطر در هر صفحه",
                "paginate": {
                    "first":      "اولین",
                    "last":       "آخرین",
                    "next":       "بعدی",
                    "previous":   "قبلی"
                },
            }
        });
      });
    </script>
  </body>
</html>