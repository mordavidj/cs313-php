<?php
if (!isset($_SESSION)) {
   session_start();
}
if(!isset($_SESSION['user_id'])){ //if login in session is not set
   $_SESSION['return'] = "add_table.php";
   header("Location: login.php");
}

require "../dbConnect.php";
$db = get_db();
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <!-- Required meta tags -->
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

   <!-- Bootstrap CSS -->
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

   <!-- jQuery first, then Popper.js, then Bootstrap JS -->
   <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

   <title>Add Vehicle - Oil Determinator</title>
</head>

<body>
   <div class="container">
      <div class="page-header">
         <h1>Which Oil is Right for You?</h1>
      </div>
   </div>
   <div class="container">
      <nav class="navbar navbar-expand-sm navbar-light bg-light">
         <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto">
               <li class="nav-item">
                  <a class="nav-link" href="05prove.php">Access</a>
               </li>
               <li class="nav-item active">
                  <a class="nav-link disabled" href="add_table.php">Add</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="edit_table.php">Edit</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" href="remove_table.php">Remove</a>
               </li>
            </ul>
            <?php
            echo '<span id="user">' . $_SESSION['username'] . '</span><a href="logout.php"><button type="button" class="btn btn-outline-danger">Logout</button></a>';
            ?>
         </div>
      </nav>
      <?php if (isset($_POST['submit'])) {
         // initialize variables
         $year = $_POST['year'];
         $make = ucwords($_POST['make']);
         $model = ucwords($_POST['model']);
         $motor = $_POST['motor'];
         $grade1 = $_POST['grade1'];
         $grade2 = $_POST['grade2'];
         $cap = $_POST['cap'];
         // insert make if doesn't exist 
         try {
            $makeIn = $db->prepare('INSERT INTO make_tbl (make, created_by, creation_date) VALUES (:make, ' . $_SESSION['user_id'] . ', CURRENT_TIMESTAMP) ON CONFLICT (make) DO NOTHING');
            $makeIn->execute(array(':make' => $make));
         } catch (PDOException $e) {
            $message = 'Make insertion failed: ' . $e;
            echo "<script type='text/javascript'>alert('$message');</script>";
            echo $message;
         }
         // insert model if doesn't exist 
         try {
            $modelIn = $db->prepare('INSERT INTO model_tbl (model, make_id, created_by, creation_date) VALUES (:model, (SELECT make_id FROM make_tbl WHERE make = :make), ' . $_SESSION['user_id'] . ', CURRENT_TIMESTAMP) ON CONFLICT (model) DO NOTHING');
            $modelIn->execute(array(':model' => $model, ':make' => $make));
         } catch (PDOException $e) {
            $message = 'Model insertion failed: ' . $e;
            echo "<script type='text/javascript'>alert('$message');</script>";
            echo $message;
         }
         // insert motor into database
         try {
            $motorSt = $db->prepare('INSERT INTO motor_tbl (motor, year, model_id, make_id, grade1_id, grade2_id, oil_cap, created_by, creation_date, last_updated_by, last_update_date) 
            VALUES 
            ( :motor
            , :year
            ,(SELECT model_id FROM model_tbl WHERE model = :model)
            ,(SELECT make_id FROM make_tbl WHERE make = :make)
            , :grade1
            , :grade2
            , :cap
            , ' . $_SESSION['user_id'] . '
            , CURRENT_TIMESTAMP
            , ' . $_SESSION['user_id'] . '
            , CURRENT_TIMESTAMP)');
            $motorSt->execute(array(':motor' => $motor, ':year' => (int) $year, ':model' => $model, ':make' => $make, ':grade1' => (int) $grade1, ':grade2' => (int) $grade2, ':cap' => (float) $cap));
         } catch (PDOException $e) {
            $message = 'Vehicle insertion failed: ' . $e;
            echo "<script type='text/javascript'>alert('$message');</script>";
            echo $message;
         }

         unset($_POST['submit']);
         echo "<script type='text/javascript'>
            alert('Vehicle was successfully added');
            window.location.href='https://sleepy-citadel-12320.herokuapp.com/week05/05prove.php';
            </script>";
      } ?>
      <div class="jumbotron">

         <label class="h3" for="car">Add Vehicle to Database</label>
         <form id="car" method="post">
            <div class="form-row">
               <div class="col-md-2 mb-1">
                  <label class="h5" for="year">Year</label>
                  <select class="form-control" name="year" id="year" tabindex="1" autofocus required>
                     <option value="" disabled selected>Year</option>
                     <?php for ($i = date("Y") + 1; $i >= 1900; $i--) {
                        echo '<option value="' . $i . '">' . $i . '</option>';
                     } ?>
                  </select>
               </div>
               <div class="col-md-4 mb-3">
                  <label class="h5" for="make">Make</label>
                  <input class="form-control auto-cap" id="make" type="text" name="make" tabindex="2" required>
               </div>
               <div class="col-md-4 mb-3">
                  <label class="h5" for="model">Model</label>
                  <input class="form-control auto-cap" id="model" type="text" name="model" tabindex="3" required>
               </div>
            </div>
            <div class="form-row">
               <div class="col-md-4 mb-3">
                  <label class="h5" for="motor">Engine</label>
                  <input class="form-control" id="motor" type="text" name="motor" tabindex="4" required>
               </div>
               <div class="col-md-2 mb-1">
                  <label class="h5" for="oil1">Oil Grade 1</label>
                  <select id="oil1" class="form-control" name="grade1" tabindex="5" required>
                     <option value="" disabled selected>Grade 1</option>
                     <?php foreach ($db->query('SELECT grade1_id, grade1 FROM grade1_tbl') as $row) {
                        echo '<option value="' . $row["grade1_id"] . '">' . $row["grade1"] . '</option>';
                     } ?>
                  </select>
               </div>
               <div class="col-md-2 mb-1">
                  <label class="h5" for="oil2">Oil Grade 2</label>
                  <select id="oil2" class="form-control" name="grade2" tabindex="6" required>
                     <option value="" disabled selected>Grade 2</option>
                     <?php foreach ($db->query('SELECT grade2_id, grade2 FROM grade2_tbl') as $row) {
                        echo '<option value="' . $row["grade2_id"] . '">' . $row["grade2"] . '</option>';
                     } ?>
                  </select>
               </div>
               <div class="col-md-2 mb-1">
                  <label class="h5" for="cap">Oil Capacity</label>
                  <input class="form-control" id="cap" type="number" name="cap" min="0" max="15" step="0.1" tabindex="7" required>
               </div>
            </div>
            <input class="btn btn-success btn-lg" type="submit" value="Add Vehicle" name="submit" tabindex="8" />
         </form>
      </div>
   </div>

   <!-- Using PHP to include the same footer -->
   <?php include("../week02/footer.html"); ?>

   <!-- Optional JavaScript -->
   <script>
   </script>
</body>

</html>