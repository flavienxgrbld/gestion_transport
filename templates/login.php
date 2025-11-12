<?php
ob_start();
?>

<h2>Connexion</h2>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
  <?php
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($email, $password)) {
      header('Location: /dashboard');
      exit;
    } else {
      echo '<div class="error">Email ou mot de passe incorrect</div>';
    }
  ?>
<?php endif; ?>

<div style="max-width:400px">
  <form method="post" action="/login">
    <div>
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required autofocus>
    </div>
    
    <div>
      <label for="password">Mot de passe</label>
      <input type="password" id="password" name="password" required>
    </div>
    
    <div style="margin-top:20px">
      <button class="btn" type="submit">Se connecter</button>
    </div>
  </form>
  
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
