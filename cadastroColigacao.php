<?php 

	include "dbConfig.php";

	$msg = "";       

	$title = "Coligação";
	
	$nomeColigacao = "";
		
	if(isset($_COOKIE['dadosUsr'])) {
		$dadosUsrSplit = explode(",",$_COOKIE['dadosUsr']);
		$loginUsr = $dadosUsrSplit[0];
		$tipoUsr = $dadosUsrSplit[2];		
	}	
	//Verifica se usuário tem permissão de acesso a tela
	if($tipoUsr != "admin") {	
		header("Location: http://localhost/UrnaIAC/login.php");
	}
	//Verifica se foi chamado um GET
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		if (isset($_GET['action'])) {
			if($_GET["action"] == "editar") {
				if (isset($_GET['id'])) {
					$idEditar = $_GET['id'];
					$sqlEditarColigacao = "SELECT id, nome FROM coligacao WHERE id = $idEditar";
					$resultEditarColigacao = mysql_query($sqlEditarColigacao);
					if ($resultEditarColigacao === false) {
						echo "Erro na query ($sqlEditarColigacao), do BD: " . mysql_error();
						exit;
					}
					if (mysql_num_rows($resultEditarColigacao) > 0) { 
						$row = mysql_fetch_row($resultEditarColigacao);					
						$idColigacao= $row[0];
						$nomeColigacao = $row[1];	
					}
				}
			}
			if($_GET["action"] == "excluir") {
				if (isset($_GET['id'])) {
					$idExcluir = $_GET['id'];
					$sqlExcluirColigacao = "DELETE FROM coligacao WHERE id = $idExcluir";
					$resultExcluirColigacao = mysql_query($sqlExcluirColigacao);
					if ($resultExcluirColigacao === false) {
						echo "Erro na query ($sqlExcluirColigacao), do BD: " . mysql_error();
						exit;
					}						
				}	
			}
		}
	}	
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if (isset($_POST['salvarColigacao'])) {
			$nomeColigacao = $_POST["nomeColigacao"];
			
			//verificar se o nome já existe no banco
			$sqlSelectColigacao = "SELECT nome FROM coligacao WHERE nome like '%$nomeColigacao%'";
			$resultSelectColigacao = mysql_query($sqlSelectColigacao);
			if ($resultSelectColigacao === false) {
				echo "Erro na query ($sqlSelectColigacao), do BD: " . mysql_error();
				exit;
			}
			if (mysql_num_rows($resultSelectColigacao) > 0) {  
				//insere os dados da coligacao no sistema
				$sqlInsert = "UPDATE coligacao set nome = '$nomeColigacao' WHERE id = $idColigacao";
				$resultInsert = mysql_query($sqlInsert);
				if ($resultInsert === false) {
					echo "Erro na query ($sqlInsert), do BD: " . mysql_error();
					exit;
				}			}		
			else {
				//insere os dados da coligacao no sistema
				$sqlInsert = "INSERT INTO coligacao (nome) VALUES ('$nomeColigacao')";
				$resultInsert = mysql_query($sqlInsert);
				if ($resultInsert === false) {
					echo "Erro na query ($sqlInsert), do BD: " . mysql_error();
					exit;
				}
			}
		}
	}

	?>

	<?php include "header.php"; ?>

	<ul class="nav nav-pills">
	  <li role="presentation" class="active"><a href="cadastroColigacao.php" > Coligação </a></li>
	  <li role="presentation"><a href="cadastroPartido.php"> Cadastro Partido </a></li>
  	  <li role="presentation"><a href="cadastroUrna.php"> Cadastro Urna </a></li>
	  <li role="presentation"><a href="login.php" > Logout</a></li>		  
	</ul>

	<form class="form-partido" action="<?= $_SERVER['PHP_SELF'] ?>" method="post" >
		<table class="table table-striped">
			<th> 	<h3> Cadastro Coligação </h3> </th>
			<tr>
			<td></td>
				<td style="color:red;">
				<?php echo $msg; ?></td>
			</tr> 		
			<tr>
				<th><label for="nomeColigacao"><strong>Nome da Coligação:</strong></label></th>
				<td><input class="form-control" name="nomeColigacao" id="nomeColigacao" type="text" size="30" value="<?php echo $nomeColigacao ?>" required /></td>
			</tr>			
			<td></td>
				<td>
				<button class="btn btn-lg btn-primary btn-block" type="submit" id="salvarColigacao" name="salvarColigacao">Salvar</button>							
			</tr>
		</table>
	</form>
		<table class="table table-striped" >
			<th>Nome</th>
			<tr>
			<td></td>
				<td style="color:red;">
				<?php echo $msg; ?></td>
			</tr>				
			<?php		
				$sqlPesquisaColigacao = "SELECT id, nome FROM coligacao";
				$resultPesquisaColigacao = mysql_query($sqlPesquisaColigacao);
				if ($resultPesquisaColigacao === false) {
					echo "Erro na query ($sqlPesquisaColigacao), do BD: " . mysql_error();
					exit;
				}
				if (mysql_num_rows($resultPesquisaColigacao) > 0) {         				
					while ($row = mysql_fetch_array($resultPesquisaColigacao, MYSQL_NUM)) {
						echo "
						<tr>
						<td>".$row[1]."</td>
						<td>
							<a class=\"btn btn-link\" href=\"cadastroColigacao.php?action=editar&id=".$row[0]."\">Editar</a> / 
							<a class=\"btn btn-link\" onclick=\"return confirmarRemover();\" href=\"cadastroColigacao.php?action=excluir&id=".$row[0]."\">Excluir</a>
						</td>
						</tr>";
					}
				}
			?>
		</table>
	</form>	
</body>
</html>
