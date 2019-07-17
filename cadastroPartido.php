<?php 

	include "dbConfig.php";

	$msg = "";       

	$title = "Partido";
	
	$siglaPartido = "";
	$nomePartido = "";
	$numeroPartido = "";
		
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
					$sqlEditarPartido = "SELECT numero, nome, sigla FROM partido WHERE numero = $idEditar";
					$resultEditarPartido = mysql_query($sqlEditarPartido);
					if ($resultEditarPartido === false) {
						echo "Erro na query ($sqlEditarPartido), do BD: " . mysql_error();
						exit;
					}
					if (mysql_num_rows($resultEditarPartido) > 0) { 
						$row = mysql_fetch_row($resultEditarPartido);					
						$numeroPartido = $row[0];
						$nomePartido = $row[1];	
						$siglaPartido = $row[2];							
					}
				}
			}
			if($_GET["action"] == "excluir") {
				if (isset($_GET['id'])) {
					$idExcluir = $_GET['id'];
					$sqlExcluirPartido = "DELETE FROM partido WHERE numero = $idExcluir";
					$resultExcluirPartido = mysql_query($sqlExcluirPartido);
					if ($resultExcluirPartido === false) {
						echo "Erro na query ($sqlExcluirPartido), do BD: " . mysql_error();
						exit;
					}						
				}	
			}
		}
	}	
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if (isset($_POST['salvarPartido'])) {
			$nomePartido = $_POST["nomePartido"];
			$siglaPartido = $_POST["siglaPartido"];
			$numeroPartido = $_POST["numeroPartido"];	
			
			//verificar se o numero e sigla já existem no banco
			$sqlSelectPartido = "SELECT * FROM partido WHERE numero = $numeroPartido";
			$resultSelectPartido = mysql_query($sqlSelectPartido);
			if ($resultSelectPartido === false) {
				echo "Erro na query ($sqlSelectPartido), do BD: " . mysql_error();
				exit;
			}
			if ((mysql_num_rows($resultSelectPartido) > 0) || ($numeroPartido < 1)){  
				$sqlInsert = "UPDATE partido set nome='$nomePartido',sigla='$siglaPartido' WHERE numero = $numeroPartido";
				$resultInsert = mysql_query($sqlInsert);
				if ($resultInsert === false) {
					echo "Erro na query ($sqlInsert), do BD: " . mysql_error();
					exit;
				}			
			}		
			else {
				if(strlen($numeroPartido) != 2) {
					echo "O número do partido deve ter 2 dígitos.";
					exit;
				}
				//insere os dados do partido no sistema
				$sqlInsert = "INSERT INTO partido (numero,nome,sigla) VALUES ($numeroPartido, '$nomePartido','$siglaPartido')";
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
	  <li role="presentation" class="active"><a href="cadastroPartido.php" > Partido </a></li>
	  <li role="presentation"><a href="cadastroColigacao.php"> Cadastro Coligação </a></li>
  	  <li role="presentation"><a href="cadastroUrna.php"> Cadastro Urna </a></li>
	  <li role="presentation"><a href="login.php" > Logout</a></li>		  
	</ul>

	<form class="form-partido" action="<?= $_SERVER['PHP_SELF'] ?>" method="post" >
		<table class="table table-striped">
			<th> 	<h3> Cadastro Partido </h3> </th>
			<tr>
			<td></td>
				<td style="color:red;">
				<?php echo $msg; ?></td>
			</tr> 
			
			<tr>
				<th><label for="numeroPartido"><strong>Número do partido:</strong></label></th>
				<td><input class="form-control" name="numeroPartido" id="numeroPartido" type="text" size="30" value="<?php echo $numeroPartido ?>" required /></td>
			</tr>		
			<tr>
				<th><label for="nomePartido"><strong>Nome do partido:</strong></label></th>
				<td><input class="form-control" name="nomePartido" id="nomePartido" type="text" size="30" value="<?php echo $nomePartido ?>" required /></td>
			</tr>
			<tr>
				<th><label for="siglaPartido"><strong>Sigla do partido:</strong></label></th>
				<td><input class="form-control" name="siglaPartido" id="siglaPartido" type="text" size="30" value="<?php echo $siglaPartido ?>" required /></td>
			</tr>		
			<tr>
				<th><label for="coligacaoCandidato"><strong>Coligação:</strong></label></th>
				<td><select id="coligacaoCandidato" name="coligacaoCandidato" class="selectpicker" >
				<?php
					$sqlDDLColigacao = "SELECT id, nome FROM coligacao";
					$resultDDLColigacao = mysql_query($sqlDDLColigacao);
					if ($resultDDLColigacao === false) {
						echo "Could not successfully run query ($sqlDDLColigacao) from DB: " . mysql_error();
						exit;
					}
					if (mysql_num_rows($resultDDLColigacao) > 0) {         				
						while ($row = mysql_fetch_array($resultDDLColigacao, MYSQL_NUM)) {
						echo "<option value=".$row[0].">".$row[1]."</option>";
						}
					}
				?>
				</select>
				</td>
			</tr>			
			<td></td>
				<td>
				<button class="btn btn-lg btn-primary btn-block" type="submit" id="salvarPartido" name="salvarPartido">Salvar</button>							
			</tr>
		</table>
	</form>

	<form class="form-eleitor-cadastro" action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" >
		<table class="table table-striped" >	
			<th>
			Pesquisa de partido
			</th>
			<tr>
				<th><label for="numPartidoPesquisa"><strong>Número:</strong></label></th>
				<td><input class="form-control" name="numPartidoPesquisa" id="numPartidoPesquisa" type="text" size="30" /></td>
			</tr>					
			<tr>
				<th><label for="siglaPartidoPesq"><strong>Sigla:</strong></label></th>
				<td><input class="form-control" name="siglaPartidoPesq" id="siglaPartidoPesq" type="text" size="30" /></td>
			</tr>			
			<td></td>
				<td>
				<button class="btn btn-lg btn-primary btn-block" type="submit" id="pesquisarPartido" name="pesquisarPartido">Pesquisar</button>			
				</td>
			</tr>			
		</table>
		<table class="table table-striped" >
			<th>Número do partido</th>
			<th>Nome</th>
			<th>Sigla</th>			
			<th>Ações</th>
			<tr>
			<td></td>
				<td style="color:red;">
				<?php echo $msg; ?></td>
			</tr>				
			<?php
			if(isset($_POST['pesquisarPartido'])) {
				$numPartidoPesquisa = $_POST["numPartidoPesquisa"];
				$siglaPartidoPesq = $_POST["siglaPartidoPesq"];	
			}			
			$sqlPesquisaPartido = "SELECT numero,nome,sigla FROM partido";//numero ='' AND nome ='' AND sigla ='';
			$resultPesquisaPartido = mysql_query($sqlPesquisaPartido);
			if ($resultPesquisaPartido === false) {
				echo "Erro na query ($sqlPesquisaPartido), do BD: " . mysql_error();
				exit;
			}
			if (mysql_num_rows($resultPesquisaPartido) > 0) {         				
				while ($row = mysql_fetch_array($resultPesquisaPartido, MYSQL_NUM)) {
					echo "
					<tr>
					<td>".$row[0]."</td>
					<td>".$row[1]."</td>
					<td>".$row[2]."</td>
					<td>
						<a class=\"btn btn-link\" href=\"cadastroPartido.php?action=editar&id=".$row[0]."\">Editar</a> / 
						<a class=\"btn btn-link\" onclick=\"return confirmarRemover();\" href=\"cadastroPartido.php?action=excluir&id=".$row[0]."\">Excluir</a>
					</td>
					</tr>";
				}
			}
			?>
		</table>
	</form>	
</body>
</html>
