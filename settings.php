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

	$company_name = 'فروشگاه RT';

	if ($_REQUEST['action'] == "logout")
	{
		session_destroy($_SESSION['sg_session_id']);
		unset($_SESSION['sg_session_id']);
		header('location: login.php');
		die();
	}
	
		if(sizeof($_POST))
        {
            // INVOICE SETTINGs
            if($_POST['cashiername'] != '')
            {
              Save_Config('Invoice','cashiername',$_POST['cashiername']);
            }
            if($_POST['lastFetchedPage'] != '')
            {
              Save_Config('Product','lastFetchedPage',$_POST['lastFetchedPage']);
            }
            // SHIPPING SETTINGS
            if($_POST['freeFee'] != '')
            {
              Save_Config('Shipping','freeFee',$_POST['freeFee']);
            }
            if($_POST['freeProductID'] != '')
            {
              Save_Config('Shipping','freeProductID',$_POST['freeProductID']);
            }
            if($_POST['peykTehranFee'] != '')
            {
              Save_Config('Shipping','peykTehranFee',$_POST['peykTehranFee']);
            }
            if($_POST['peykTehranProductID'] != '')
            {
              Save_Config('Shipping','peykTehranProductID',$_POST['peykTehranProductID']);
            }
            if($_POST['postTehranFee'] != '')
            {
              Save_Config('Shipping','postTehranFee',$_POST['postTehranFee']);
            }
            if($_POST['postTehranProductID'] != '')
            {
              Save_Config('Shipping','postTehranProductID',$_POST['postTehranProductID']);
            }
            if($_POST['postOtherFee'] != '')
            {
              Save_Config('Shipping','postOtherFee',$_POST['postOtherFee']);
            }
            if($_POST['postOtherProductID'] != '')
            {
              Save_Config('Shipping','postOtherProductID',$_POST['postOtherProductID']);
            }
        }
    
    // LOAD CONFIG	
	$Config = Config();

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
                <a class="nav-link active" href="settings.php">
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
            <h1 class="h2">تنظیمات</h1>
          </div>
          <form method="POST" action="settings.php">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="cashiename">نام صندوق فروشگاه</label>
                  <input type="text" class="form-control" name="cashiername" id="cashiername" placeholder="نام صندوق" value="<?php echo $Config['Invoice']['cashiername'] ; ?>">
                  <small id="emailHelp" class="form-text text-muted">نام صندوقی که فاکتور های سایت با آن ثبت می شود.</small>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="cashiename">محصولات سینک شده</label>
                  <input type="text" class="form-control" name="lastFetchedPage" id="lastFetchedPage" placeholder="محصولات سینک شده" value="<?php echo $Config['Product']['lastFetchedPage'] ; ?>">
                  <small id="emailHelp" class="form-text text-muted">محصولات تا این تعداد سینک شده اند</small>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
              <div class="form-group">
                  <label for="cashiename">اعلامیه قیمت حمل</label>
                  <table class="table">
                    <thead class="thead-dark">
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">عنوان</th>
                        <th scope="col">شناسه محصول</th>
                        <th scope="col">هزینه ارسال</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <th scope="row">1</th>
                        <td>ارسال رایگان</td>
                        <td><input type="number" class="form-control" name="freeProductID" value="<?php echo $Config['Shipping']['freeProductID'] ; ?>"></td>
                        <td><input type="number" class="form-control" name="freeFee" value="<?php echo $Config['Shipping']['freeFee'] ; ?>"></td>
                      </tr>
                      <tr>
                        <th scope="row">2</th>
                        <td>ارسال با پیک تهران</td>
                        <td><input type="number" class="form-control" name="peykTehranProductID" value="<?php echo $Config['Shipping']['peykTehranProductID'] ; ?>"></td>
                        <td><input type="number" class="form-control" name="peykTehranFee" value="<?php echo $Config['Shipping']['peykTehranFee'] ; ?>"></td>
                      </tr>
                      <tr>
                        <th scope="row">3</th>
                        <td>ارسال با پست تهران</td>
                        <td><input type="number" class="form-control" name="postTehranProductID" value="<?php echo $Config['Shipping']['postTehranProductID'] ; ?>"></td>
                        <td><input type="number" class="form-control" name="postTehranFee" value="<?php echo $Config['Shipping']['postTehranFee'] ; ?>"></td>
                      </tr>
                      <tr>
                        <th scope="row">4</th>
                        <td>ارسال با پست شهرستان</td>
                        <td><input type="number" class="form-control" name="postOtherProductID" value="<?php echo $Config['Shipping']['postOtherProductID'] ; ?>"></td>
                        <td><input type="number" class="form-control" name="postOtherFee" value="<?php echo $Config['Shipping']['postOtherFee'] ; ?>"></td>
                      </tr>
                    </tbody>
                  </table>
                  <small id="emailHelp" class="form-text text-muted">شناسه حمل و قیمت ها(تومان) را در جدول فوق وارد نمایید</small>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <button type="submit" class="btn btn-primary">ذخیره</button>
              </div>
            </div>
          </form>
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
    </script>
  </body>
</html>