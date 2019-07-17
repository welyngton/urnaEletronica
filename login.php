<?php 
	
	include "dbConfig.php";

	$msg = "";
	$title = "Login Urna IAC";
	
	//Apaga todos os cookies
	if (isset($_SERVER['HTTP_COOKIE'])) {
		$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
		foreach($cookies as $cookie) {
			$parts = explode('=', $cookie);
			$nome = trim($parts[0]);
			setcookie($nome, '', time()-1000);
			setcookie($nome, '', time()-1000, '/');
			
		}
	}
	
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $loginUsr = $_POST["login"];
    $password = md5($_POST["password"]);
	 if ($loginUsr == '' || $password == '') {
        $msg = "Você deve preencher todos os campos.";
    } else {
        $sql = "SELECT * FROM usuario WHERE login = '$loginUsr' AND senha = '$password'";
        $result = mysql_query($sql);

        if ($result === false) {
            echo "Erro na query: ($sql), do BD: " . mysql_error();
            exit;
        }

		//Login com sucesso!
        if (mysql_num_rows($result) > 0) {         		
			$row = mysql_fetch_row($result);
			//Grava dados de login no cookie de usuário ("Nome,ID,TipoUsr")
			setcookie("dadosUsr", $loginUsr.",".$row[0].",".$row[3], time() + (3600), "/"); // 3600 = 1 hora			
			if($row[3] == "admin") {
				header("Location: http://localhost/UrnaIAC/cadastroUrna.php");
			}
			else
				header("Location: http://localhost/UrnaIAC/cadastroEleitor.php");
            exit;
        }
        $msg = "Usuário ou senha incorretos!";
    }
}
?>

<?php include "header.php"; ?>

	  <div class="container">

      <form class="form-signin" action="<?= $_SERVER['PHP_SELF'] ?>" method="post" >
        <h2 class="form-signin-heading">Urna IAC</h2>
        <label for="inputLogin" class="sr-only">Login</label>
        <input type="text" id="login" name="login" class="form-control" placeholder="Login" required autofocus>
        <label for="inputPassword" class="sr-only">Senha</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Senha" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Entrar</button>
		<a class="btn btn-xs btn-primary btn-block" href="cadastroEleitor.php" role="button" style="color:white;">Cadastro eleitor</a>
	    		
      </form>
    </div> <!-- /container -->
					
<?php include "footer.php"; ?>