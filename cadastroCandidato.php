<?php 
	include "dbConfig.php";

	$msg = "";           

	$title = "Candidato";
	$nomeCandidato = "";
	$numeroCandidato = "";
	$fotoCandidato = "fotosCandidatos/blank.jpg";
	$cargoCandidato = "";
	$partidoCandidato = "";
	
	if(isset($_COOKIE['dadosUsr'])) {
		$dadosUsrSplit = explode(",",$_COOKIE['dadosUsr']);
		$loginUsr = $dadosUsrSplit[0];
		$tipoUsr = $dadosUsrSplit[2];		
	}	
	//Verifica se usuário tem permissão de acesso a tela
	if($tipoUsr != "admin") {	
		header("Location: http://localhost/UrnaIAC/login.php");
	}
	?>

	<?php include "header.php"; ?>
	
	<?php	
	//Verifica se foi chamado um GET
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		if (isset($_GET['action'])) {
			if($_GET["action"] == "editar") {
				if (isset($_GET['id'])) {
					$idEditar = $_GET['id'];
					$sqlEditarPartido = "SELECT numero, nome, cargo, foto FROM candidato WHERE id = $idEditar";
					$resultEditarPartido = mysql_query($sqlEditarPartido);
					if ($resultEditarPartido === false) {
						echo "Erro na query ($sqlEditarPartido), do BD: " . mysql_error();
						exit;
					}
					if (mysql_num_rows($resultEditarPartido) > 0) { 
						$row = mysql_fetch_row($resultEditarPartido);					
						$numeroCandidato = $row[0];
						$nomeCandidato = $row[1];
						$cargoCandidato = $row[2];
						$fotoCandidato = $row[3];						
						$partidoCandidato = "";						
					}
				}
			}
			if($_GET["action"] == "excluir") {
				if (isset($_GET['id'])) {
					$idExcluir = $_GET['id'];
					$sqlExcluirCandidato = "DELETE FROM candidato WHERE id = $idExcluir";
					$resultExcluirCandidato = mysql_query($sqlExcluirCandidato);
					if ($resultExcluirCandidato === false) {
						echo "Erro na query ($sqlExcluirCandidato), do BD: " . mysql_error();
						exit;
					}							
				}	
			}
		}
	}		
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if (isset($_POST['salvarCandidato'])) {
			$target_dir = "fotosCandidatos/";
			if($_FILES["fileToUpload"]) {
				$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
			}
			$uploadOk = 1;
			$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
			if(isset($_POST["submit"])) {
				$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
				if($check !== false) {
					$uploadOk = 1;
				} else {
					$uploadOk = 0;
				}
			}
			//if (file_exists($target_file)) {
			//	echo "Desculpe, este arquivo já existe.";
			//	$uploadOk = 0;
			//}
			//if ($_FILES["fileToUpload"]["size"] > 5000000) {
			//	echo "Desculpe, seu arquivo é muito grande.";
			//	$uploadOk = 0;
			//}
			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
			&& $imageFileType != "gif" && $imageFileType != "JPG" && $imageFileType != "JPEG" && $imageFileType != "GIF" && $imageFileType != "PNG" ) {
				echo "Desculpe, somente arquivos JPG, JPEG, PNG & GIF são permitidos.";
				echo "Tipo ".$imageFileType." não permitido.";
				$uploadOk = 0;
			}
			if ($uploadOk == 0) {
				echo "Houve um erro no upload da sua imagem.";
			} else {
				if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
					//echo "O arquivo". basename( $_FILES["fileToUpload"]["name"]). " foi uploaded.";
				} else {
					echo "Desculpe, houve um erro ao fazer upload do ser arquivo.";
				}
			}
			$numPartido = $_POST["partido"];
			$cargoCandidato = $_POST["cargoCandidato"];
			$numeroCandidato = $_POST["numeroCandidato"];
			$nomeCandidato = $_POST["nomeCandidato"];	
			$fotoCandidato = "fotosCandidatos/".$_FILES["fileToUpload"]["name"];
			
			//verificar se o numero do candidato já existe no banco
			$sqlSelectPartido = "SELECT numPartido FROM candidato WHERE numero = $numeroCandidato AND cargo = '$cargoCandidato'";
			$resultSelectPartido = mysql_query($sqlSelectPartido);
			if ($resultSelectPartido === false) {
				echo "Erro na query ($sqlSelectPartido), do BD: " . mysql_error();
				exit;
			}
			if (mysql_num_rows($resultSelectPartido) > 0) {  
				//Verificar partido
				//$row = mysql_fetch_row($resultSelectPartido);					
				//$numPartido = $row[0];					
				if(substr($numeroCandidato,0,2) != $numPartido) {
					echo "O Número do candidato deve se iniciar com o número do partido <".$numPartido.">";
					exit;
				}				
				
				//Verificar numeracao				
				if(($cargoCandidato == "Deputado Estadual") || ($cargoCandidato == "Vereador"))
					if(strlen($numeroCandidato) != 5) {
						echo "O Número do candidato deve conter 5 dígitos";
						exit;						
					}
				if(($cargoCandidato == "Prefeito") || ($cargoCandidato == "Governador") || ($cargoCandidato == "Presidente"))
					if(strlen($numeroCandidato) != 2) {
						echo "O Número do candidato deve conter 2 dígitos";
						exit;						
					}
				if($cargoCandidato == "Senador")
					if(strlen($numeroCandidato) != 3) {
						echo "O Número do candidato deve conter 3 dígitos";
						exit;						
					}
				if($cargoCandidato == "Deputado Federal")
					if(strlen($numeroCandidato) != 4) {
						echo "O Número do candidato deve conter 4 dígitos";
						exit;						
					}				
				//insere os dados do candidato no sistema
				$sqlUpdate = "UPDATE candidato set nome='$nomeCandidato', numero=$numeroCandidato, numPartido=$numPartido, cargo='$cargoCandidato' ,foto='$fotoCandidato'";
				$resultUpdate = mysql_query($sqlUpdate);
				if ($resultUpdate === false) {
					echo "Erro na query ($sqlUpdate), do BD: " . mysql_error();
					exit;
				}
			}		
			else {				
				//Verificar partido
				if(substr($numeroCandidato,0,2) != $numPartido) {
					echo "O Número do candidato deve se iniciar com o número do partido <".$numPartido.">";
					exit;
				}				
				
				//Verificar numeracao
				if(($cargoCandidato == "Deputado Estadual") || ($cargoCandidato == "Vereador"))
					if(strlen($numeroCandidato) != 5) {
						echo "O Número do candidato deve conter 5 dígitos";
						exit;						
					}
				if(($cargoCandidato == "Prefeito") || ($cargoCandidato == "Governador") || ($cargoCandidato == "Presidente"))
					if(strlen($numeroCandidato) != 2) {
						echo "O Número do candidato deve conter 2 dígitos";
						exit;						
					}
				if($cargoCandidato == "Senador")
					if(strlen($numeroCandidato) != 3) {
						echo "O Número do candidato deve conter 3 dígitos";
						exit;						
					}
				if($cargoCandidato == "Deputado Federal")
					if(strlen($numeroCandidato) != 4) {
						echo "O Número do candidato deve conter 4 dígitos";
						exit;						
					}					
				//insere os dados do candidato no sistema
				$sqlInsert = "INSERT INTO candidato (nome,numero,numPartido, cargo,foto) VALUES ('$nomeCandidato',$numeroCandidato,$numPartido,'$cargoCandidato','$fotoCandidato')";
				$resultInsert = mysql_query($sqlInsert);
				if ($resultInsert === false) {
					echo "Erro na query ($sqlInsert), do BD: " . mysql_error();
					exit;
				}
			}
		}
	}	
	?>	
	
	
	<ul class="nav nav-pills">
	  <li role="presentation" class="active"><a href="cadastroCandidato.php" > Candidato </a></li>
	  <li role="presentation"><a href="cadastroUrna.php"> Cadastro Urna </a></li>
	  <li role="presentation"><a href="login.php" > Logout</a></li>		  
	</ul>
	
	<form class="form-candidato" action="<?= $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
		<table class="table table-striped">
			<th> 	<h3> Cadastro Candidato </h3> </th>
			<tr>
			<td></td>
				<td style="color:red;">
				<?php echo $msg; ?></td>
			</tr> 
			<tr>
				<th><label for="name"><strong>Partido:</strong></label></th>
				<td><select id="partido" name="partido" class="selectpicker" >
				<?php
					$sqlDDLPartido = "SELECT numero, sigla FROM partido";
					$resultDDLPartido = mysql_query($sqlDDLPartido);
					if ($resultDDLPartido === false) {
						echo "Erro na query ($sqlDDLPartido) do BD: " . mysql_error();
						exit;
					}
					if (mysql_num_rows($resultDDLPartido) > 0) {         				
						while ($row = mysql_fetch_array($resultDDLPartido, MYSQL_NUM)) {
						echo "<option value=".$row[0].">".$row[1]."</option>";
						}
					}
				?>
				</select>
				</td>
			</tr>
			<tr>
				<th><label for="cargoCandidato"><strong>Cargo:</strong></label></th>
				<td><select id="cargoCandidato" name="cargoCandidato" class="selectpicker" >
				  <option value="Deputado Estadual">Deputado Estadual</option>
				  <option value="Deputado Federal">Deputado Federal</option>
				  <option value="Senador">Senador</option>
				  <option value="Governador">Governador</option>
				  <option value="Presidente">Presidente</option>
				  <option value="Vereador">Vereador</option>	
				  <option value="Prefeito">Prefeito</option>				  
				</select></td>
			</tr>			
			<tr>
				<th><label for="numeroCandidato"><strong>Número do candidato:</strong></label></th>
				<td><input class="form-control" name="numeroCandidato" id="numeroCandidato" type="text" size="30" value="<?php echo $numeroCandidato ?>" required /></td>
			</tr>	
			<tr>
				<th><label for="nomeCandidato"><strong>Nome do candidato:</strong></label></th>
				<td><input class="form-control" name="nomeCandidato" id="nomeCandidato" type="text" size="30" value="<?php echo $nomeCandidato ?>" required /></td>
			</tr>
			<tr>
			<td>
				  <a href="#" class="thumbnail">
					  <img src="<?php if(isset($_FILES["fileToUpload"]["name"])) echo "fotosCandidatos/".$_FILES["fileToUpload"]["name"]; else echo $fotoCandidato; ?>" alt="...">
				  </a>
			</td>
			<td><input class="btn btn-primary btn-block" type="file" name="fileToUpload" id="fileToUpload" accept="image/*" value="<?php echo $fotoCandidato ?>" required ></td>
			</tr>
			<tr>
			<td></td>
				<td>
				<input class="btn btn-lg btn-primary btn-block" type="submit" value="Salvar" alt="Salvar" title="Salvar" id="salvarCandidato" name="salvarCandidato" />	
				</td>
			</tr>
		</table>
	</form>
	
	<form class="form-eleitor-cadastro" action="<?= $_SERVER['PHP_SELF'] ?>" method="post" >
		<table class="table table-striped" >	
			<th>
			Pesquisa de candidato
			</th>
			<tr>
				<th><label for="nomeCandidatoPesquisa"><strong>Nome:</strong></label></th>
				<td><input class="form-control" name="nomeCandidatoPesquisa" id="nomeCandidatoPesquisa" type="text" size="30" value="<?php $usr ?>"  /></td>
			</tr>
			<tr>
				<th><label for="partidoCandidatoPesquisa"><strong>Partido:</strong></label></th>
				<td><select id="partidoCandidatoPesquisa" name="partidoCandidatoPesquisa" class="selectpicker" >
				<?php
					$sqlDDLPartido = "SELECT numero, sigla FROM partido";
					$resultDDLPartido = mysql_query($sqlDDLPartido);
					if ($resultDDLPartido === false) {
						echo "Erro na query ($sqlDDLPartido), do BD: " . mysql_error();
						exit;
					}
					if (mysql_num_rows($resultDDLPartido) > 0) {         				
						while ($row = mysql_fetch_array($resultDDLPartido, MYSQL_NUM)) {
						echo "<option value=".$row[0].">".$row[1]."</option>";
						}
					}
				?>
				</select>
				</td>
			</tr>		
			<tr>
				<th><label for="cargoCandidatoPesquisa"><strong>Cargo:</strong></label></th>
				<td><input class="form-control" name="cargoCandidatoPesquisa" id="cargoCandidatoPesquisa" type="text" size="30" /></td>
			</tr>				
			<td></td>
				<td>
				<button class="btn btn-lg btn-primary btn-block" type="submit" id="pesquisarCandidato" name="pesquisarCandidato">Pesquisar</button>			
				</td>
			</tr>			
		</table>
	</form>
	
		<form name="frmregister"action="<?= $_SERVER['PHP_SELF'] ?>" method="post" >
		<table class="table table-striped" >
			<th>Nome</th>
			<th>Partido</th>
			<th>Cargo</th>
			<th>Número</th>
			<th>Ações</th>					
			<tr>
			<td></td>
				<td style="color:red;">
				<?php echo $msg; ?></td>
			</tr>				
			<?php
			$sqlWhere = "";
			if(isset($_POST['pesquisarCandidato'])) {
				$nomeCandidatoPesquisa = $_POST["nomeCandidatoPesquisa"];
				$partidoCandidatoPesquisa = $_POST["partidoCandidatoPesquisa"];
				$cargoCandidatoPesquisa = $_POST["cargoCandidatoPesquisa"];
				//Busca por parâmetros
				if(!empty($nomeCandidato)){ 
					$sqlWhere = " WHERE c.nome like '%$nomeCandidatoPesquisa%' ";
				}
				if(!empty($partidoCandidato)){ 
					if(empty($sqlWhere)) 
						$sqlWhere = "WHERE c.numPartido = $partidoCandidatoPesquisa ";
					else
						$sqlWhere .= "AND c.numPartido = $partidoCandidatoPesquisa ";
				}
				if(!empty($cargoCandidato)){ 
					if(empty($sqlWhere)) 
						$sqlWhere = "WHERE c.cargo = '$cargoCandidatoPesquisa' ";
					else
						$sqlWhere .= "AND c.cargo = '$cargoCandidatoPesquisa' ";
				}
			}
			$sqlPesquisaCandidato = "SELECT c.nome, p.sigla, c.cargo, c.numero, c.id FROM candidato c inner join partido p on p.numero = c.numPartido ".$sqlWhere;//WHERE...
			$resultPesquisaCandidato = mysql_query($sqlPesquisaCandidato);
			if ($resultPesquisaCandidato === false) {
				echo "Erro na query ($sqlPesquisaCandidato), do BD: " . mysql_error();
				exit;
			}
			if (mysql_num_rows($resultPesquisaCandidato) > 0) {         				
				while ($row = mysql_fetch_array($resultPesquisaCandidato, MYSQL_NUM)) {
					echo "
					<tr>
					<td>".$row[0]."</td>
					<td>".$row[1]."</td>
					<td>".$row[2]."</td>
					<td>".$row[3]."</td>				
					<td>
						<a class=\"btn btn-link\" href=\"cadastroCandidato.php?action=editar&id=".$row[4]."\">Editar</a> / 
						<a class=\"btn btn-link\" onclick=\"return confirmarRemover();\" href=\"cadastroCandidato.php?action=excluir&id=".$row[4]."\">Excluir</a>
					</td>
					</tr>";
				}
			}
			?>
		</table>
	</form>	
</body>
</html>
