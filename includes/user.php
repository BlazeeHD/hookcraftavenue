<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Users - Hookcraft Avenue</title>
  <link rel="stylesheet" href="asset/dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <?php include 'includes/sidebar.php'; ?>
  <?php include 'includes/header.php'; ?>

  <div class="main-content">
    <div class="card">
      <h4>User</h4>
      <div class="table-responsive mt-3">
        <table class="table table-bordered" style="background-color: #d39cab;">
          <thead style="background-color: #f8dce2; color: white;">
            <tr>
              <th>id</th>
              <th>Name</th>
              <th>email</th>
              <th>password</th>
              <th>created at</th>
              <th>action</th>
            </tr>
          </thead>
          <tbody style="background-color: #e9b9c6;">
            <tr>
              <td>1</td>
              <td>Marc</td>
              <td>Marc@gmail.com</td>
              <td>231651657413</td>
              <td>08-12-2003</td>
              <td>
                <button class="btn btn-success btn-sm me-1" style="border-radius: 20px;">✔</button>
                <button class="btn btn-danger btn-sm" style="border-radius: 20px;">✖</button>
              </td>
            </tr>
            <!-- Add more user rows here -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

</body>
</html>
