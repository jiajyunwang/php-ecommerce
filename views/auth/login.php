<!DOCTYPE html>
<html lang="en">

<head>
  <title>E-SHOP || Login Page</title>
  <?php
  include_once 'head.php';
  ?>

</head>

<body class="bg-gradient-primary">

  <div class="container">
    <div class="mt-5">
      <div class="card mt-5">
        <div class="p-5">
          <div class="text-center">
            <h1 class="h4 text-gray-900 mb-4">後台管理系統</h1>
          </div>
          <form class="user" method="POST" action="/admin/login">
            <div class="form-group">
              <?php if (isset($error['email'])): ?>
                <input type="email" class="form-control form-control-user is-invalid" name="email" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Enter Email Address..."  required autocomplete="email" autofocus>
                <span class="invalid-feedback" role="alert">
                  <strong><?= $error['email'] ?></strong>
                </span>
              <?php else: ?>
                <input type="email" class="form-control form-control-user" name="email" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Enter Email Address..."  required autocomplete="email" autofocus>
              <?php endif; ?>

              <?php if (isset($error['login'])): ?>
                <div class="alert alert-danger">
                  <?= $error['login'] ?>
                </div>
              <?php endif; ?>
            </div>
            
            <div class="form-group">
              <?php if (isset($error['password'])): ?>
                <input type="password" class="form-control form-control-user is-invalid" id="exampleInputPassword" placeholder="Password"  name="password" required autocomplete="current-password">
                <span class="invalid-feedback" role="alert">
                  <strong><?= $error['password'] ?></strong>
                </span>
              <?php else: ?>
                <input type="password" class="form-control form-control-user" id="exampleInputPassword" placeholder="Password"  name="password" required autocomplete="current-password">
              <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary btn-user btn-block">
              Login
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>

</html>